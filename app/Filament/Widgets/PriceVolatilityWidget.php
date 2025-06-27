<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PriceVolatilityWidget extends ChartWidget
{
    protected static ?string $heading = 'Price Volatility Analysis';
    protected static ?int $sort = 4;

    public ?string $filter = '30';

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $dateFrom = now()->subDays($days);

        // Calculate standard deviation of prices for each product
        $volatilityData = DB::table('products')
            ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
            ->select([
                'products.id',
                'products.sku',
                DB::raw('STDDEV(price_histories.price) as price_volatility'),
                DB::raw('AVG(price_histories.price) as avg_price'),
                DB::raw('COUNT(price_histories.id) as price_count')
            ])
            ->where('price_histories.created_at', '>=', $dateFrom)
            ->groupBy('products.id', 'products.sku')
            ->having('price_count', '>=', 2) // Need at least 2 price points
            ->orderBy('price_volatility', 'desc')
            ->limit(8)
            ->get();

        if ($volatilityData->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = $volatilityData->map(function ($item) {
            return strlen($item->sku) > 15 ? substr($item->sku, 0, 15) . '...' : $item->sku;
        })->toArray();

        $data = $volatilityData->map(function ($item) {
            return round((float) $item->price_volatility, 2);
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Price Volatility ($)',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Most Volatile Products (by Price Standard Deviation)',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": $" + context.parsed + " volatility";
                        }'
                    ]
                ]
            ],
        ];
    }
} 