<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PriceHistoryOverviewWidget extends ChartWidget
{
    protected static ?string $heading = 'Price History Overview - Top Products';
    protected static ?int $sort = 2;

    public ?string $filter = '30';

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $dateFrom = now()->subDays($days);

        // Get top 5 products with most price changes
        $topProducts = DB::table('products')
            ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
            ->select('products.id', 'products.sku', DB::raw('COUNT(*) as price_count'))
            ->where('price_histories.created_at', '>=', $dateFrom)
            ->groupBy('products.id', 'products.sku')
            ->orderBy('price_count', 'desc')
            ->limit(5)
            ->get();

        if ($topProducts->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // Get date labels for the chart
        $dates = [];
        for ($i = $days; $i >= 0; $i--) {
            $dates[] = now()->subDays($i)->format('M d');
        }

        $datasets = [];
        $colors = [
            ['border' => '#FF6384', 'background' => 'rgba(255, 99, 132, 0.1)'],
            ['border' => '#36A2EB', 'background' => 'rgba(54, 162, 235, 0.1)'],
            ['border' => '#FFCE56', 'background' => 'rgba(255, 206, 86, 0.1)'],
            ['border' => '#4BC0C0', 'background' => 'rgba(75, 192, 192, 0.1)'],
            ['border' => '#9966FF', 'background' => 'rgba(153, 102, 255, 0.1)'],
        ];

        foreach ($topProducts as $index => $product) {
            $priceHistory = DB::table('price_histories')
                ->where('product_id', $product->id)
                ->where('created_at', '>=', $dateFrom)
                ->orderBy('effective_date')
                ->get();

            // Fill in missing dates with previous price
            $priceData = [];
            $lastPrice = null;
            
            for ($i = $days; $i >= 0; $i--) {
                $currentDate = now()->subDays($i)->format('Y-m-d');
                $dayPrice = $priceHistory->where('effective_date', '<=', $currentDate)->last();
                
                if ($dayPrice) {
                    $lastPrice = (float) $dayPrice->price;
                }
                
                $priceData[] = $lastPrice ?? 0;
            }

            $color = $colors[$index % count($colors)];
            $datasets[] = [
                'label' => substr($product->sku, 0, 20) . (strlen($product->sku) > 20 ? '...' : ''),
                'data' => $priceData,
                'borderColor' => $color['border'],
                'backgroundColor' => $color['background'],
                'tension' => 0.4,
                'fill' => false,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '60' => 'Last 60 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'title' => [
                        'display' => true,
                        'text' => 'Price ($)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Date',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Product Price Trends',
                ],
            ],
        ];
    }
} 