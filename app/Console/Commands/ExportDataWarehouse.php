<?php

namespace App\Console\Commands;

use App\Models\FactPriceChange;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportDataWarehouse extends Command
{
    protected $signature = 'dw:export 
                           {type : Export type (price-trends|product-summary|category-analysis)}
                           {--from= : Start date (YYYY-MM-DD)}
                           {--to= : End date (YYYY-MM-DD)}
                           {--format=csv : Export format (csv|json)}';

    protected $description = 'Export data warehouse data for analysis';

    public function handle()
    {
        $type = $this->argument('type');
        $format = $this->option('format');
        $fromDate = $this->option('from');
        $toDate = $this->option('to');

        $this->info("Exporting {$type} data in {$format} format...");

        try {
            $data = match ($type) {
                'price-trends' => $this->exportPriceTrends($fromDate, $toDate),
                'product-summary' => $this->exportProductSummary($fromDate, $toDate),
                'category-analysis' => $this->exportCategoryAnalysis($fromDate, $toDate),
                default => throw new \InvalidArgumentException("Unknown export type: {$type}")
            };

            $filename = $this->generateFilename($type, $format);
            
            if ($format === 'csv') {
                $this->exportToCsv($data, $filename);
            } else {
                $this->exportToJson($data, $filename);
            }

            $this->info("Export completed: storage/exports/{$filename}");

        } catch (\Exception $e) {
            $this->error("Export failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function exportPriceTrends(?string $fromDate, ?string $toDate): array
    {
        $query = FactPriceChange::with(['product', 'date'])
            ->join('dim_products', 'fact_price_changes.product_id', '=', 'dim_products.product_id')
            ->join('dim_dates', 'fact_price_changes.date_id', '=', 'dim_dates.id')
            ->select([
                'dim_products.sku',
                'dim_products.name',
                'dim_products.category',
                'dim_products.brand',
                'dim_products.store',
                'dim_dates.full_date',
                'fact_price_changes.price',
                'fact_price_changes.price_change',
                'fact_price_changes.price_change_percentage'
            ]);

        if ($fromDate) {
            $query->where('dim_dates.full_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('dim_dates.full_date', '<=', $toDate);
        }

        return $query->orderBy('dim_dates.full_date')
            ->orderBy('dim_products.sku')
            ->get()
            ->toArray();
    }

    private function exportProductSummary(?string $fromDate, ?string $toDate): array
    {
        $query = FactPriceChange::with(['product'])
            ->join('dim_products', 'fact_price_changes.product_id', '=', 'dim_products.product_id')
            ->join('dim_dates', 'fact_price_changes.date_id', '=', 'dim_dates.id')
            ->selectRaw('
                dim_products.sku,
                dim_products.name,
                dim_products.category,
                dim_products.brand,
                dim_products.store,
                MIN(fact_price_changes.price) as min_price,
                MAX(fact_price_changes.price) as max_price,
                AVG(fact_price_changes.price) as avg_price,
                COUNT(*) as price_changes_count,
                AVG(fact_price_changes.price_change_percentage) as avg_change_percentage
            ')
            ->groupBy([
                'dim_products.product_id',
                'dim_products.sku',
                'dim_products.name',
                'dim_products.category',
                'dim_products.brand',
                'dim_products.store'
            ]);

        if ($fromDate) {
            $query->where('dim_dates.full_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('dim_dates.full_date', '<=', $toDate);
        }

        return $query->get()->toArray();
    }

    private function exportCategoryAnalysis(?string $fromDate, ?string $toDate): array
    {
        $query = FactPriceChange::join('dim_products', 'fact_price_changes.product_id', '=', 'dim_products.product_id')
            ->join('dim_dates', 'fact_price_changes.date_id', '=', 'dim_dates.id')
            ->selectRaw('
                dim_products.category,
                COUNT(DISTINCT dim_products.product_id) as products_count,
                MIN(fact_price_changes.price) as min_category_price,
                MAX(fact_price_changes.price) as max_category_price,
                AVG(fact_price_changes.price) as avg_category_price,
                AVG(fact_price_changes.price_change_percentage) as avg_price_volatility
            ')
            ->groupBy('dim_products.category');

        if ($fromDate) {
            $query->where('dim_dates.full_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->where('dim_dates.full_date', '<=', $toDate);
        }

        return $query->get()->toArray();
    }

    private function exportToCsv(array $data, string $filename): void
    {
        if (empty($data)) {
            throw new \Exception('No data to export');
        }

        $csv = fopen('php://temp', 'w+');
        
        // Write headers
        fputcsv($csv, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($csv, $row);
        }
        
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        
        Storage::disk('local')->put("exports/{$filename}", $content);
    }

    private function exportToJson(array $data, string $filename): void
    {
        $content = json_encode($data, JSON_PRETTY_PRINT);
        Storage::disk('local')->put("exports/{$filename}", $content);
    }

    private function generateFilename(string $type, string $format): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "{$type}_{$timestamp}.{$format}";
    }
} 