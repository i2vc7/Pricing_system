<?php

namespace App\Console\Commands;

use App\Models\DimDate;
use App\Models\DimProduct;
use App\Models\FactPriceChange;
use App\Models\Product;
use App\Models\PriceHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EtlDataWarehouse extends Command
{
    protected $signature = 'dw:etl 
                           {--full-refresh : Perform a full refresh of all data}
                           {--from-date= : Start date for incremental load (YYYY-MM-DD)}';

    protected $description = 'Extract, Transform, and Load data from operational database to data warehouse';

    public function handle()
    {
        $this->info('Starting ETL process for Data Warehouse...');

        try {
            DB::beginTransaction();

            // Step 1: Load Date Dimension
            $this->loadDateDimension();

            // Step 2: Load Product Dimension
            $this->loadProductDimension();

            // Step 3: Load Price Change Facts
            $this->loadPriceChangeFacts();

            DB::commit();
            
            $this->info('ETL process completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('ETL process failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function loadDateDimension()
    {
        $this->info('Loading Date Dimension...');

        // Get earliest and latest dates from price history
        $earliestDate = PriceHistory::min('effective_date');
        $latestDate = PriceHistory::max('effective_date');

        if (!$earliestDate || !$latestDate) {
            $this->warn('No price history data found. Skipping date dimension.');
            return;
        }

        $startDate = Carbon::parse($earliestDate)->startOfDay();
        $endDate = Carbon::parse($latestDate)->endOfDay();

        // Extend range to include future dates (next 30 days)
        $endDate = $endDate->addDays(30);

        $currentDate = $startDate->copy();
        $count = 0;

        while ($currentDate <= $endDate) {
            if (!DimDate::where('full_date', $currentDate->toDateString())->exists()) {
                DimDate::createFromDate($currentDate);
                $count++;
            }
            $currentDate->addDay();
        }

        $this->info("Created {$count} new date records.");
    }

    private function loadProductDimension()
    {
        $this->info('Loading Product Dimension...');

        $products = Product::with(['category', 'brand', 'store', 'translations'])
            ->withTrashed()
            ->get();

        $count = 0;

        foreach ($products as $product) {
            $productName = $product->translations()
                ->where('locale', 'en')
                ->value('name') ?: $product->sku;

            DimProduct::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'sku' => $product->sku,
                    'name' => $productName,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'store' => $product->store?->name,
                    'vat_percentage' => $product->vat_percentage,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                    'deleted_at' => $product->deleted_at,
                ]
            );
            $count++;
        }

        $this->info("Processed {$count} product records.");
    }

    private function loadPriceChangeFacts()
    {
        $this->info('Loading Price Change Facts...');

        $isFullRefresh = $this->option('full-refresh');
        $fromDate = $this->option('from-date');

        $query = PriceHistory::with('product')
            ->join('dim_dates', DB::raw('DATE(price_histories.effective_date)'), '=', 'dim_dates.full_date')
            ->select('price_histories.*', 'dim_dates.id as date_id');

        if (!$isFullRefresh && $fromDate) {
            $query->where('price_histories.effective_date', '>=', $fromDate);
        } elseif (!$isFullRefresh) {
            // Default incremental load: last 7 days
            $query->where('price_histories.effective_date', '>=', now()->subDays(7));
        }

        if (!$isFullRefresh) {
            // Delete existing facts for the date range to avoid duplicates
            $deleteQuery = FactPriceChange::query();
            if ($fromDate) {
                $deleteQuery->where('effective_datetime', '>=', $fromDate);
            } else {
                $deleteQuery->where('effective_datetime', '>=', now()->subDays(7));
            }
            $deletedCount = $deleteQuery->count();
            $deleteQuery->delete();
            $this->info("Deleted {$deletedCount} existing fact records for incremental load.");
        } else {
            // Full refresh: truncate fact table
            FactPriceChange::truncate();
            $this->info("Truncated fact table for full refresh.");
        }

        $priceHistories = $query->get();
        $count = 0;

        foreach ($priceHistories as $priceHistory) {
            // Calculate price change from previous record
            $previousPrice = PriceHistory::where('product_id', $priceHistory->product_id)
                ->where('effective_date', '<', $priceHistory->effective_date)
                ->orderBy('effective_date', 'desc')
                ->value('price');

            $priceChange = null;
            $priceChangePercentage = null;

            if ($previousPrice) {
                $priceChange = $priceHistory->price - $previousPrice;
                $priceChangePercentage = $previousPrice > 0 
                    ? ($priceChange / $previousPrice) * 100 
                    : null;
            }

            FactPriceChange::create([
                'product_id' => $priceHistory->product_id,
                'date_id' => $priceHistory->date_id,
                'price' => $priceHistory->price,
                'price_change' => $priceChange,
                'price_change_percentage' => $priceChangePercentage,
                'effective_datetime' => $priceHistory->effective_date,
            ]);

            $count++;
        }

        $this->info("Created {$count} price change fact records.");
    }
} 