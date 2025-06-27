<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TopPriceMoversWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $topMovers = $this->getTopPriceMovers();
        
        $stats = [];
        
        foreach ($topMovers as $index => $mover) {
            $priceChange = number_format($mover['price_change'], 2);
            $statNumber = $index + 1;
            $stats[] = Stat::make("#$statNumber Price Mover", $mover['sku'])
                ->description("Price change: $" . $priceChange)
                ->descriptionIcon($mover['price_change'] > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($mover['price_change'] > 0 ? 'success' : 'danger')
                ->chart([7, 4, 6, 8, 6, 9, 7]);
        }
        
        return $stats;
    }
    
    private function getTopPriceMovers(): array
    {
        $weekAgo = now()->subWeek();
        
        return DB::table('products')
            ->join('price_histories as ph1', 'products.id', '=', 'ph1.product_id')
            ->join('price_histories as ph2', function($join) use ($weekAgo) {
                $join->on('products.id', '=', 'ph2.product_id')
                     ->where('ph2.effective_date', '<=', $weekAgo);
            })
            ->select([
                'products.sku',
                'products.id',
                DB::raw('ph1.price - ph2.price as price_change'),
                DB::raw('ABS(ph1.price - ph2.price) as abs_change')
            ])
            ->where('ph1.effective_date', '>=', $weekAgo)
            ->orderBy('abs_change', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'sku' => $item->sku,
                    'price_change' => (float) $item->price_change,
                    'abs_change' => (float) $item->abs_change,
                ];
            })
            ->toArray();
    }
} 