<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use App\Jobs\EtlDataWarehouseJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KaggleEtlService
{
    private array $categoryMap = [];
    private array $brandMap = [];
    private array $storeMap = [];
    private array $productMap = [];
    private array $stats = [
        'processed' => 0,
        'skipped' => 0,
        'products_created' => 0,
        'price_histories_created' => 0,
        'errors' => 0
    ];

    public function __construct()
    {
        $this->loadMaps();
    }

    /**
     * Process a single row from the Kaggle dataset
     */
    public function processRow(array $row): bool
    {
        try {
            $this->stats['processed']++;

            // Extract and validate data
            $transformedData = $this->transformRow($row);
            
            if (!$transformedData) {
                $this->stats['skipped']++;
                return false;
            }

            // Get or create entities
            $categoryId = $this->getOrCreateCategory($transformedData['category']);
            $brandId = $this->getOrCreateBrand($transformedData['brand']);
            $storeId = $this->getOrCreateStore($transformedData['store']);

            // Get or create product
            $productId = $this->getOrCreateProduct(
                $transformedData['product_name'],
                $transformedData['sku'],
                $categoryId,
                $brandId,
                $storeId,
                $transformedData['price']
            );

            // Create price history
            $this->createPriceHistory(
                $productId,
                $transformedData['price'],
                $transformedData['effective_date']
            );

            return true;

        } catch (\Exception $e) {
            $this->stats['errors']++;
            Log::error('KaggleETL: Error processing row', [
                'row' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Transform raw CSV row into clean data structure
     */
    private function transformRow(array $row): ?array
    {
        // Map CSV columns (adjust these based on actual Kaggle dataset structure)
        $productName = $this->cleanString($row['Product_Name'] ?? $row['product_name'] ?? '');
        $category = $this->cleanString($row['Category'] ?? $row['category'] ?? '');
        $brand = $this->cleanString($row['Brand'] ?? $row['brand'] ?? '');
        $province = $this->cleanString($row['Province'] ?? $row['province'] ?? '');
        $price = $this->cleanPrice($row['Price'] ?? $row['price'] ?? 0);
        $year = (int) ($row['Year'] ?? $row['year'] ?? 0);
        $month = (int) ($row['Month'] ?? $row['month'] ?? 0);

        // Data validation
        if (empty($productName) || empty($category) || $price <= 0 || $year < 2000 || $month < 1 || $month > 12) {
            return null;
        }

        // Create effective date
        try {
            $effectiveDate = Carbon::create($year, $month, 1);
        } catch (\Exception $e) {
            return null;
        }

        return [
            'product_name' => $productName,
            'sku' => $this->generateSku($productName, $brand),
            'category' => $category,
            'brand' => $brand ?: 'Unknown',
            'store' => $this->mapProvinceToStore($province),
            'price' => $price,
            'effective_date' => $effectiveDate
        ];
    }

    /**
     * Clean and normalize string data
     */
    private function cleanString(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    /**
     * Clean and validate price data
     */
    private function cleanPrice($value): float
    {
        // Remove currency symbols and convert to float
        $cleaned = preg_replace('/[^\d.,]/', '', (string) $value);
        $cleaned = str_replace(',', '.', $cleaned);
        return (float) $cleaned;
    }

    /**
     * Generate SKU from product name and brand
     */
    private function generateSku(string $productName, string $brand): string
    {
        $namePart = Str::slug(Str::limit($productName, 20, ''));
        $brandPart = Str::slug(Str::limit($brand, 10, ''));
        return strtoupper($brandPart . '-' . $namePart);
    }

    /**
     * Map province to store concept
     */
    private function mapProvinceToStore(string $province): string
    {
        $provinceStoreMap = [
            'Ontario' => 'Ontario Retail',
            'Quebec' => 'Quebec Retail',
            'British Columbia' => 'BC Retail',
            'Alberta' => 'Alberta Retail',
            'Manitoba' => 'Manitoba Retail',
            'Saskatchewan' => 'Saskatchewan Retail',
            'Nova Scotia' => 'Nova Scotia Retail',
            'New Brunswick' => 'New Brunswick Retail',
            'Newfoundland and Labrador' => 'Newfoundland Retail',
            'Prince Edward Island' => 'PEI Retail',
            'Northwest Territories' => 'Northwest Territories Retail',
            'Nunavut' => 'Nunavut Retail',
            'Yukon' => 'Yukon Retail'
        ];

        return $provinceStoreMap[$province] ?? ($province . ' Store');
    }

    /**
     * Get or create category
     */
    private function getOrCreateCategory(string $name): int
    {
        if (!isset($this->categoryMap[$name])) {
            $category = Category::firstOrCreate(['name' => $name]);
            $this->categoryMap[$name] = $category->id;
        }
        return $this->categoryMap[$name];
    }

    /**
     * Get or create brand
     */
    private function getOrCreateBrand(string $name): int
    {
        if (!isset($this->brandMap[$name])) {
            $brand = Brand::firstOrCreate(['name' => $name]);
            $this->brandMap[$name] = $brand->id;
        }
        return $this->brandMap[$name];
    }

    /**
     * Get or create store
     */
    private function getOrCreateStore(string $name): int
    {
        if (!isset($this->storeMap[$name])) {
            $store = Store::firstOrCreate(['name' => $name]);
            $this->storeMap[$name] = $store->id;
        }
        return $this->storeMap[$name];
    }

    /**
     * Get or create product
     */
    private function getOrCreateProduct(string $name, string $sku, int $categoryId, int $brandId, int $storeId, float $price): int
    {
        $key = "{$sku}-{$storeId}";
        
        if (!isset($this->productMap[$key])) {
            $product = Product::firstOrCreate(
                ['sku' => $sku, 'store_id' => $storeId],
                [
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'current_price' => $price,
                    'vat_percentage' => 15.00
                ]
            );

            if ($product->wasRecentlyCreated) {
                $this->stats['products_created']++;
                
                // Create product translation
                $product->translations()->create([
                    'locale' => 'en',
                    'name' => $name
                ]);
            }

            $this->productMap[$key] = $product->id;
        }
        
        return $this->productMap[$key];
    }

    /**
     * Create price history entry
     */
    private function createPriceHistory(int $productId, float $price, Carbon $effectiveDate): void
    {
        // Check if price history already exists for this product and date
        $exists = PriceHistory::where('product_id', $productId)
            ->whereDate('effective_date', $effectiveDate->toDateString())
            ->exists();

        if (!$exists) {
            PriceHistory::create([
                'product_id' => $productId,
                'price' => $price,
                'effective_date' => $effectiveDate
            ]);
            $this->stats['price_histories_created']++;
        }
    }

    /**
     * Load existing entities into memory for performance
     */
    private function loadMaps(): void
    {
        $this->categoryMap = Category::pluck('id', 'name')->toArray();
        $this->brandMap = Brand::pluck('id', 'name')->toArray();
        $this->storeMap = Store::pluck('id', 'name')->toArray();
    }

    /**
     * Get processing statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Reset statistics
     */
    public function resetStats(): void
    {
        $this->stats = [
            'processed' => 0,
            'skipped' => 0,
            'products_created' => 0,
            'price_histories_created' => 0,
            'errors' => 0
        ];
    }

    /**
     * Trigger data warehouse ETL after processing
     */
    public function triggerDataWarehouseEtl(): void
    {
        Log::info('KaggleETL: Triggering data warehouse ETL');
        EtlDataWarehouseJob::dispatch(false, now()->subDays(1)->toDateString());
    }
} 