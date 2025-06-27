<?php

namespace App\Jobs;

use App\Models\DimDate;
use App\Models\DimProduct;
use App\Models\FactPriceChange;
use App\Models\Product;
use App\Models\PriceHistory;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EtlDataWarehouseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private bool $fullRefresh;
    private ?string $fromDate;

    public function __construct(bool $fullRefresh = false, ?string $fromDate = null)
    {
        $this->fullRefresh = $fullRefresh;
        $this->fromDate = $fromDate;
    }

    public function handle()
    {
        Log::info('Starting ETL Data Warehouse Job', [
            'full_refresh' => $this->fullRefresh,
            'from_date' => $this->fromDate
        ]);

        try {
            DB::beginTransaction();

            $this->loadDateDimension();
            $this->loadProductDimension();
            $this->loadPriceChangeFacts();

            DB::commit();
            
            Log::info('ETL Data Warehouse Job completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ETL Data Warehouse Job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function loadDateDimension()
    {
        $earliestDate = PriceHistory::min('effective_date');
        $latestDate = PriceHistory::max('effective_date');

        if (!$earliestDate || !$latestDate) {
            return;
        }

        $startDate = Carbon::parse($earliestDate)->startOfDay();
        $endDate = Carbon::parse($latestDate)->addDays(30)->endOfDay();

        $currentDate = $startDate->copy();
        $count = 0;

        while ($currentDate <= $endDate) {
            if (!DimDate::where('full_date', $currentDate->toDateString())->exists()) {
                DimDate::createFromDate($currentDate);
                $count++;
            }
            $currentDate->addDay();
        }

        Log::info("Created {$count} new date dimension records");
    }

    private function loadProductDimension()
    {
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

        Log::info("Processed {$count} product dimension records");
    }

    private function loadPriceChangeFacts()
    {
        $query = PriceHistory::with('product')
            ->join('dim_dates', DB::raw('DATE(price_histories.effective_date)'), '=', 'dim_dates.full_date')
            ->select('price_histories.*', 'dim_dates.id as date_id');

        if (!$this->fullRefresh && $this->fromDate) {
            $query->where('price_histories.effective_date', '>=', $this->fromDate);
        } elseif (!$this->fullRefresh) {
            $query->where('price_histories.effective_date', '>=', now()->subDays(7));
        }

        if (!$this->fullRefresh) {
            $deleteQuery = FactPriceChange::query();
            if ($this->fromDate) {
                $deleteQuery->where('effective_datetime', '>=', $this->fromDate);
            } else {
                $deleteQuery->where('effective_datetime', '>=', now()->subDays(7));
            }
            $deletedCount = $deleteQuery->count();
            $deleteQuery->delete();
            Log::info("Deleted {$deletedCount} existing fact records for incremental load");
        } else {
            FactPriceChange::truncate();
            Log::info("Truncated fact table for full refresh");
        }

        $priceHistories = $query->get();
        $count = 0;

        foreach ($priceHistories as $priceHistory) {
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

        Log::info("Created {$count} price change fact records");
    }
} 