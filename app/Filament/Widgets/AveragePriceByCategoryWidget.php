<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AveragePriceByCategoryWidget extends ChartWidget
{
    protected static ?string $heading = 'Average Price by Category (Latest Prices)';
    protected static ?int $sort = 5;

    public ?string $filter = 'all';

    protected function getData(): array
    {
        $query = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
            ->select([
                'categories.name',
                'categories.id',
                DB::raw('AVG(price_histories.price) as avg_price'),
                DB::raw('COUNT(DISTINCT products.id) as product_count'),
                DB::raw('COUNT(price_histories.id) as price_count')
            ])
            ->groupBy('categories.id', 'categories.name');

        // Apply time filter
        if ($this->filter !== 'all') {
            $days = (int) $this->filter;
            $query->where('price_histories.created_at', '>=', now()->subDays($days));
        }

        $averages = $query->having('product_count', '>', 0)
            ->orderBy('avg_price', 'desc')
            ->limit(10)
            ->get();

        if ($averages->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = $averages->map(function ($item) {
            return $item->name . " ({$item->product_count} products)";
        })->toArray();

        $data = $averages->pluck('avg_price')->map(fn($price) => round((float) $price, 2))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Average Price ($)',
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
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(99, 255, 132, 0.8)',
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
                        'rgba(255, 99, 255, 1)',
                        'rgba(99, 255, 132, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => 'All Time',
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '60' => 'Last 60 days',
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Category Price Distribution',
                ],
            ],
        ];
    }
} 