<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PriceChangeTrendWidget extends ChartWidget
{
    protected static ?string $heading = 'Price Change Trend (Last 30 Days)';

    public ?string $filter = null;

    protected function getData(): array
    {
        $productId = $this->filter;
        
        if (!$productId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $priceHistory = DB::table('price_histories')
            ->where('product_id', $productId)
            ->where('effective_date', '>=', now()->subDays(30))
            ->orderBy('effective_date')
            ->select('price', 'effective_date')
            ->get();

        $product = Product::find($productId);

        return [
            'datasets' => [
                [
                    'label' => $product ? "Price for {$product->sku}" : 'Price',
                    'data' => $priceHistory->pluck('price')->map(fn($price) => (float) $price)->toArray(),
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $priceHistory->pluck('effective_date')->map(fn($date) => 
                \Carbon\Carbon::parse($date)->format('M d')
            )->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return Product::with(['category', 'brand', 'store'])
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Product $product) => [
                $product->id => "{$product->sku} - {$product->category->name} - {$product->brand->name}"
            ])->toArray();
    }
} 