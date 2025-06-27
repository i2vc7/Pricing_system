<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductDataCleaningService
{
    /**
     * Clean and process product data from array
     *
     * @param array $data Raw product data
     * @param string $locale Locale for product translations (default: 'en')
     * @return array Result with status and messages
     */
    public function processProductData(array $data, string $locale = 'en'): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'processed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        if (empty($data)) {
            $result['message'] = 'No data provided';
            return $result;
        }

        $processed = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $item) {
                // Validate required fields
                $validator = Validator::make($item, [
                    'sku' => 'required|string|max:100',
                    'name' => 'required|string|max:255',
                    'category' => 'required|string|max:255',
                    'brand' => 'required|string|max:255',
                    'store' => 'required|string|max:255',
                    'price' => 'required|numeric|min:0',
                    'effective_date' => 'required|date_format:Y-m-d',
                    'description' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    $skipped++;
                    $errors[] = "Row {$index}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Clean and standardize data
                $cleanData = $this->cleanProductData($item);

                // Skip if effective date is in the future
                $effectiveDate = Carbon::parse($cleanData['effective_date']);
                if ($effectiveDate->isFuture()) {
                    $skipped++;
                    $errors[] = "Row {$index}: Effective date is in the future";
                    continue;
                }

                // Find or create related entities
                $category = $this->findOrCreateCategory($cleanData['category']);
                $brand = $this->findOrCreateBrand($cleanData['brand']);
                $store = $this->findOrCreateStore($cleanData['store'], $cleanData['store_url'] ?? null);

                // Find or create product
                $product = $this->findOrCreateProduct(
                    $cleanData['sku'],
                    $category->id,
                    $brand->id,
                    $store->id,
                    $cleanData['price']
                );

                // Create or update product translation
                $this->createOrUpdateTranslation(
                    $product->id,
                    $locale,
                    $cleanData['name'],
                    $cleanData['description'] ?? null
                );

                // Create price history if not a duplicate
                $priceHistoryCreated = $this->createPriceHistoryIfNeeded(
                    $product->id,
                    $cleanData['price'],
                    $effectiveDate
                );

                // Update product's current price if this is the latest price
                $this->updateProductCurrentPriceIfNeeded($product, $cleanData['price'], $effectiveDate);

                $processed++;
            }

            DB::commit();

            $result['success'] = true;
            $result['message'] = "Processed {$processed} products, skipped {$skipped}";
            $result['processed'] = $processed;
            $result['skipped'] = $skipped;
            $result['errors'] = $errors;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing product data: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            $result['message'] = 'Error processing data: ' . $e->getMessage();
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Clean and standardize product data
     *
     * @param array $data Raw product data
     * @return array Cleaned product data
     */
    protected function cleanProductData(array $data): array
    {
        return [
            'sku' => trim($data['sku']),
            'name' => trim($data['name']),
            'description' => isset($data['description']) ? trim($data['description']) : null,
            'category' => trim($data['category']),
            'brand' => trim($data['brand']),
            'store' => trim($data['store']),
            'store_url' => isset($data['store_url']) ? trim($data['store_url']) : null,
            'price' => round((float) $data['price'], 2),
            'effective_date' => Carbon::parse($data['effective_date'])->format('Y-m-d'),
            'vat_percentage' => isset($data['vat_percentage']) ? round((float) $data['vat_percentage'], 2) : 15.00,
        ];
    }

    /**
     * Find or create a category
     *
     * @param string $name Category name
     * @return Category
     */
    protected function findOrCreateCategory(string $name): Category
    {
        return Category::firstOrCreate(['name' => $name]);
    }

    /**
     * Find or create a brand
     *
     * @param string $name Brand name
     * @return Brand
     */
    protected function findOrCreateBrand(string $name): Brand
    {
        return Brand::firstOrCreate(['name' => $name]);
    }

    /**
     * Find or create a store
     *
     * @param string $name Store name
     * @param string|null $url Store URL
     * @return Store
     */
    protected function findOrCreateStore(string $name, ?string $url = null): Store
    {
        return Store::firstOrCreate(
            ['name' => $name],
            ['url' => $url]
        );
    }

    /**
     * Find or create a product
     *
     * @param string $sku Product SKU
     * @param int $categoryId Category ID
     * @param int $brandId Brand ID
     * @param int $storeId Store ID
     * @param float $price Current price
     * @return Product
     */
    protected function findOrCreateProduct(
        string $sku,
        int $categoryId,
        int $brandId,
        int $storeId,
        float $price
    ): Product {
        return Product::firstOrCreate(
            ['sku' => $sku, 'store_id' => $storeId],
            [
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'current_price' => $price,
            ]
        );
    }

    /**
     * Create or update product translation
     *
     * @param int $productId Product ID
     * @param string $locale Locale
     * @param string $name Product name
     * @param string|null $description Product description
     * @return ProductTranslation
     */
    protected function createOrUpdateTranslation(
        int $productId,
        string $locale,
        string $name,
        ?string $description = null
    ): ProductTranslation {
        return ProductTranslation::updateOrCreate(
            ['product_id' => $productId, 'locale' => $locale],
            [
                'name' => $name,
                'description' => $description,
            ]
        );
    }

    /**
     * Create price history if it doesn't already exist
     *
     * @param int $productId Product ID
     * @param float $price Price
     * @param Carbon $effectiveDate Effective date
     * @return bool Whether a new price history was created
     */
    protected function createPriceHistoryIfNeeded(int $productId, float $price, Carbon $effectiveDate): bool
    {
        // Check if a price history entry already exists for this product on this date with this price
        $exists = PriceHistory::where('product_id', $productId)
            ->whereDate('effective_date', $effectiveDate->format('Y-m-d'))
            ->where('price', $price)
            ->exists();

        if (!$exists) {
            PriceHistory::create([
                'product_id' => $productId,
                'price' => $price,
                'effective_date' => $effectiveDate,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Update product's current price if the effective date is the latest
     *
     * @param Product $product Product
     * @param float $price Price
     * @param Carbon $effectiveDate Effective date
     * @return bool Whether the product was updated
     */
    protected function updateProductCurrentPriceIfNeeded(Product $product, float $price, Carbon $effectiveDate): bool
    {
        // Get the latest price history entry for this product
        $latestPriceHistory = PriceHistory::where('product_id', $product->id)
            ->where('effective_date', '<=', now())
            ->orderBy('effective_date', 'desc')
            ->first();

        // If this is the latest price, update the product's current price
        if (!$latestPriceHistory || $effectiveDate->greaterThanOrEqualTo($latestPriceHistory->effective_date)) {
            $product->update(['current_price' => $price]);
            return true;
        }

        return false;
    }

    /**
     * Process data from a CSV file
     *
     * @param string $filePath Path to CSV file
     * @param string $locale Locale for product translations (default: 'en')
     * @return array Result with status and messages
     */
    public function processFromCsv(string $filePath, string $locale = 'en'): array
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'message' => 'File not found: ' . $filePath,
                'processed' => 0,
                'skipped' => 0,
                'errors' => ['File not found'],
            ];
        }

        $data = [];
        $headers = [];

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Read headers
            if (($headers = fgetcsv($handle)) !== false) {
                // Convert headers to lowercase for case-insensitive matching
                $headers = array_map('strtolower', $headers);

                // Read data rows
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($headers) === count($row)) {
                        $data[] = array_combine($headers, $row);
                    }
                }
            }
            fclose($handle);
        }

        return $this->processProductData($data, $locale);
    }

    /**
     * Process data from an API response
     *
     * @param array $apiData API response data
     * @param string $locale Locale for product translations (default: 'en')
     * @return array Result with status and messages
     */
    public function processFromApi(array $apiData, string $locale = 'en'): array
    {
        // Map API data to our expected format if needed
        $mappedData = $this->mapApiDataToExpectedFormat($apiData);
        
        return $this->processProductData($mappedData, $locale);
    }

    /**
     * Map API data to our expected format
     *
     * @param array $apiData API response data
     * @return array Mapped data
     */
    protected function mapApiDataToExpectedFormat(array $apiData): array
    {
        $mappedData = [];
        
        // This method would be customized based on the actual API response format
        // Here's a simple example assuming the API returns an array of products
        foreach ($apiData as $item) {
            $mappedData[] = [
                'sku' => $item['sku'] ?? $item['id'] ?? '',
                'name' => $item['name'] ?? $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'category' => $item['category'] ?? $item['category_name'] ?? 'Uncategorized',
                'brand' => $item['brand'] ?? $item['manufacturer'] ?? 'Unknown',
                'store' => $item['store'] ?? $item['retailer'] ?? 'Unknown',
                'store_url' => $item['store_url'] ?? $item['retailer_url'] ?? null,
                'price' => $item['price'] ?? 0,
                'effective_date' => $item['date'] ?? $item['price_date'] ?? now()->format('Y-m-d'),
                'vat_percentage' => $item['vat'] ?? $item['tax'] ?? 15.00,
            ];
        }
        
        return $mappedData;
    }
}