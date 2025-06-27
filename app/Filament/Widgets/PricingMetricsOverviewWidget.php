<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Category;
use App\Models\PriceHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PricingMetricsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            $this->getTotalProductsStat(),
            $this->getAveragePriceStat(),
            $this->getPriceChangesTodayStat(),
            $this->getHighestPricedProductStat(),
            $this->getMostVolatileCategoryStat(),
            $this->getDataCoverageStat(),
        ];
    }

    private function getTotalProductsStat(): Stat
    {
        $totalProducts = Product::count();
        $activeProducts = Product::whereHas('priceHistories')->count();
        
        return Stat::make('Total Products', $totalProducts)
            ->description("{$activeProducts} with price data")
            ->descriptionIcon('heroicon-m-cube')
            ->color('success')
            ->chart([7, 4, 6, 8, 6, 9, 7, 8, 6, 8]);
    }

    private function getAveragePriceStat(): Stat
    {
        $avgPrice = DB::table('price_histories')
            ->avg('price');
        
        $weekAgoAvg = DB::table('price_histories')
            ->where('created_at', '<=', now()->subWeek())
            ->avg('price');
        
        $changePercent = $weekAgoAvg ? (($avgPrice - $weekAgoAvg) / $weekAgoAvg) * 100 : 0;
        
        return Stat::make('Average Price', '$' . number_format($avgPrice, 2))
            ->description(($changePercent >= 0 ? '+' : '') . number_format($changePercent, 1) . '% from last week')
            ->descriptionIcon($changePercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($changePercent >= 0 ? 'success' : 'danger')
            ->chart([4, 6, 8, 5, 7, 6, 9, 8, 7, 8]);
    }

    private function getPriceChangesTodayStat(): Stat
    {
        $todayChanges = PriceHistory::where('created_at', '>=', now()->startOfDay())->count();
        $yesterdayChanges = PriceHistory::whereBetween('created_at', [
            now()->subDay()->startOfDay(),
            now()->subDay()->endOfDay()
        ])->count();
        
        $changePercent = $yesterdayChanges ? (($todayChanges - $yesterdayChanges) / $yesterdayChanges) * 100 : 0;
        
        return Stat::make('Price Updates Today', $todayChanges)
            ->description('Price changes recorded')
            ->descriptionIcon('heroicon-m-arrow-path')
            ->color('info')
            ->chart([3, 5, 2, 8, 6, 4, 7, 5, 9, 6]);
    }

    private function getHighestPricedProductStat(): Stat
    {
        $highestPriced = DB::table('products')
            ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
            ->select('products.sku', 'price_histories.price')
            ->orderBy('price_histories.price', 'desc')
            ->first();
        
        if (!$highestPriced) {
            return Stat::make('Highest Price', '$0.00')
                ->description('No data available')
                ->color('gray');
        }
        
        return Stat::make('Highest Price', '$' . number_format($highestPriced->price, 2))
            ->description($highestPriced->sku)
            ->descriptionIcon('heroicon-m-star')
            ->color('warning')
            ->chart([8, 9, 7, 8, 9, 8, 7, 9, 8, 9]);
    }

    private function getMostVolatileCategoryStat(): Stat
    {
        $mostVolatile = DB::table('categories')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('price_histories', 'products.id', '=', 'price_histories.product_id')
            ->select([
                'categories.name',
                DB::raw('STDDEV(price_histories.price) as volatility')
            ])
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('volatility', 'desc')
            ->first();
        
        if (!$mostVolatile) {
            return Stat::make('Most Volatile', 'No data')
                ->description('Category volatility')
                ->color('gray');
        }
        
        return Stat::make('Most Volatile', $mostVolatile->name)
            ->description('$' . number_format($mostVolatile->volatility, 2) . ' std dev')
            ->descriptionIcon('heroicon-m-bolt')
            ->color('danger')
            ->chart([2, 8, 3, 9, 1, 7, 4, 8, 2, 9]);
    }

    private function getDataCoverageStat(): Stat
    {
        $categoriesWithData = Category::whereHas('products.priceHistories')->count();
        $totalCategories = Category::count();
        
        $coveragePercent = $totalCategories ? ($categoriesWithData / $totalCategories) * 100 : 0;
        
        return Stat::make('Data Coverage', number_format($coveragePercent, 0) . '%')
            ->description("{$categoriesWithData}/{$totalCategories} categories")
            ->descriptionIcon('heroicon-m-chart-bar')
            ->color($coveragePercent >= 80 ? 'success' : ($coveragePercent >= 50 ? 'warning' : 'danger'))
            ->chart([5, 6, 7, 8, 7, 8, 9, 8, 9, 8]);
    }
} 