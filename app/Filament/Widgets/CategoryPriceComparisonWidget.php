<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoryPriceComparisonWidget extends ChartWidget
{
    protected static ?string $heading = 'Price Analysis by Category';
    protected static ?int $sort = 3;

    public ?string $filter = 'average';

    protected function getData(): array
    {
        $metric = $this->filter;
        
        $categories = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->limit(8)
            ->get();

        if ($categories->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = $categories->pluck('name')->toArray();
        $data = [];

        foreach ($categories as $category) {
            switch ($metric) {
                case 'average':
                    $value = DB::table('products')
                        ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
                        ->where('products.category_id', $category->id)
                        ->avg('price_histories.price');
                    break;
                
                case 'highest':
                    $value = DB::table('products')
                        ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
                        ->where('products.category_id', $category->id)
                        ->max('price_histories.price');
                    break;
                
                case 'lowest':
                    $value = DB::table('products')
                        ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
                        ->where('products.category_id', $category->id)
                        ->min('price_histories.price');
                    break;
                
                case 'range':
                    $max = DB::table('products')
                        ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
                        ->where('products.category_id', $category->id)
                        ->max('price_histories.price');
                    $min = DB::table('products')
                        ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
                        ->where('products.category_id', $category->id)
                        ->min('price_histories.price');
                    $value = $max - $min;
                    break;
                
                default:
                    $value = 0;
            }
            
            $data[] = round((float) $value, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => $this->getMetricLabel($metric),
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
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'average' => 'Average Price',
            'highest' => 'Highest Price',
            'lowest' => 'Lowest Price',
            'range' => 'Price Range',
        ];
    }

    private function getMetricLabel(string $metric): string
    {
        return match ($metric) {
            'average' => 'Average Price ($)',
            'highest' => 'Highest Price ($)',
            'lowest' => 'Lowest Price ($)',
            'range' => 'Price Range ($)',
            default => 'Price ($)',
        };
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Price ($)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Categories',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Category Price Analysis',
                ],
            ],
        ];
    }
} 