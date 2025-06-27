<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use Carbon\Carbon;

class ProductSeeder extends Migration
{
    public function run()
    {
        $categories = ['Electronics', 'Fashion', 'Groceries'];
        $stores = ['Jarir Bookstore', 'Noon', 'Amazon.sa', 'Extra Stores'];
        $productNames = [
            'Samsung Galaxy S24' => 2999.99,
            'Shein Summer Dress' => 149.99,
            'Lulu Dates 1kg' => 29.99,
            'Apple iPhone 15' => 3999.99,
            'Adidas Sneakers' => 299.99,
        ];

        foreach (range(1, 150) as $index) {
            $productName = array_rand($productNames);
            $basePrice = $productNames[$productName];
            $priceVariation = rand(-10, 10) / 100 * $basePrice; // ±10% variation
            $price = max(10, $basePrice + $priceVariation); // Ensure price > 0

            Product::create([
                'name' => $productName . ' #' . $index,
                'category' => $categories[array_rand($categories)],
                'store_name' => $stores[array_rand($stores)],
                'current_price' => $price,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
