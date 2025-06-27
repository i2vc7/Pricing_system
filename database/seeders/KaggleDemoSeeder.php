<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Faker\Factory as Faker;

class KaggleDemoSeeder extends Seeder
{
    /**
     * Generate demo CSV file with Kaggle-style product data
     */
    public function run()
    {
        $faker = Faker::create();
        
        $this->command->info('Generating demo Kaggle dataset...');

        // Product categories
        $categories = [
            'Electronics', 'Food & Beverages', 'Clothing & Apparel', 
            'Home & Garden', 'Health & Beauty', 'Sports & Outdoors',
            'Books & Media', 'Toys & Games', 'Automotive', 'Office Supplies'
        ];

        // Brands per category
        $brands = [
            'Electronics' => ['Samsung', 'Apple', 'Sony', 'LG', 'HP'],
            'Food & Beverages' => ['Nestle', 'Coca Cola', 'Pepsi', 'Kraft', 'General Mills'],
            'Clothing & Apparel' => ['Nike', 'Adidas', 'H&M', 'Zara', 'Levi\'s'],
            'Home & Garden' => ['IKEA', 'Home Depot', 'Black & Decker', 'Stanley', 'Rubbermaid'],
            'Health & Beauty' => ['L\'Oreal', 'Unilever', 'P&G', 'Johnson & Johnson', 'Nivea'],
            'Sports & Outdoors' => ['Nike', 'Adidas', 'Under Armour', 'North Face', 'Columbia'],
            'Books & Media' => ['Penguin', 'Harper Collins', 'Random House', 'Marvel', 'DC Comics'],
            'Toys & Games' => ['Mattel', 'Hasbro', 'Lego', 'Fisher Price', 'Hot Wheels'],
            'Automotive' => ['Castrol', 'Mobil', 'Michelin', 'Goodyear', 'Bosch'],
            'Office Supplies' => ['Staples', 'HP', 'Canon', 'Epson', '3M']
        ];

        // Canadian provinces
        $provinces = [
            'Ontario', 'Quebec', 'British Columbia', 'Alberta', 'Manitoba',
            'Saskatchewan', 'Nova Scotia', 'New Brunswick', 'Newfoundland and Labrador',
            'Prince Edward Island', 'Northwest Territories', 'Nunavut', 'Yukon'
        ];

        // Generate products
        $products = [];
        foreach ($categories as $category) {
            $categoryBrands = $brands[$category];
            for ($i = 0; $i < 6; $i++) { // 6 products per category = 60 total
                $brand = $faker->randomElement($categoryBrands);
                $productName = $this->generateProductName($category, $brand, $faker);
                
                $products[] = [
                    'category' => $category,
                    'brand' => $brand,
                    'product_name' => $productName,
                    'base_price' => $faker->randomFloat(2, 5, 500)
                ];
            }
        }

        // Generate CSV data
        $csvData = [];
        $csvData[] = [
            'Product_Name', 'Category', 'Brand', 'Province', 'Price', 'Tax', 'Year', 'Month'
        ];

        // Generate monthly price data for the last 3 years
        $startDate = Carbon::now()->subYears(3)->startOfYear();
        $endDate = Carbon::now();

        $rowCount = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addMonth()) {
            foreach ($products as $product) {
                foreach ($provinces as $province) {
                    // Add some randomness - not all products available in all provinces every month
                    if ($faker->boolean(70)) { // 70% chance of availability
                        
                        // Price variation (±20% from base price)
                        $priceVariation = $faker->randomFloat(2, -0.2, 0.2);
                        $price = $product['base_price'] * (1 + $priceVariation);
                        $price = max($price, 1.00); // Minimum $1
                        
                        // Tax calculation (varies by province)
                        $taxRate = $this->getTaxRate($province);
                        $tax = $price * $taxRate;

                        $csvData[] = [
                            $product['product_name'],
                            $product['category'],
                            $product['brand'],
                            $province,
                            number_format($price, 2),
                            number_format($tax, 2),
                            $date->year,
                            $date->month
                        ];
                        
                        $rowCount++;
                    }
                }
            }
        }

        // Write CSV file
        $filename = 'imports/kaggle_demo_' . now()->format('Y_m_d_H_i_s') . '.csv';
        $csv = fopen('php://temp', 'w+');
        
        foreach ($csvData as $row) {
            fputcsv($csv, $row);
        }
        
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        
        Storage::put($filename, $content);

        $this->command->info("✅ Demo dataset generated: {$filename}");
        $this->command->info("📊 Generated {$rowCount} price records for " . count($products) . " products");
        $this->command->info("🚀 Run import with: php artisan kaggle:import {$filename} --max-rows=1000");
    }

    private function generateProductName(string $category, string $brand, $faker): string
    {
        $productNames = [
            'Electronics' => ['Smartphone', 'Laptop', 'Tablet', 'Headphones', 'Speaker', 'TV', 'Camera'],
            'Food & Beverages' => ['Cereal', 'Soda', 'Coffee', 'Snack Bar', 'Juice', 'Cookies', 'Chips'],
            'Clothing & Apparel' => ['T-Shirt', 'Jeans', 'Sneakers', 'Jacket', 'Dress', 'Sweater', 'Hat'],
            'Home & Garden' => ['Tool Set', 'Garden Hose', 'Storage Box', 'Lamp', 'Cushion', 'Planter', 'Drill'],
            'Health & Beauty' => ['Shampoo', 'Face Cream', 'Toothpaste', 'Perfume', 'Soap', 'Moisturizer', 'Lipstick'],
            'Sports & Outdoors' => ['Running Shoes', 'Gym Bag', 'Water Bottle', 'Yoga Mat', 'Tennis Racket', 'Backpack'],
            'Books & Media' => ['Novel', 'Comic Book', 'Magazine', 'Textbook', 'Cookbook', 'Biography', 'DVD'],
            'Toys & Games' => ['Action Figure', 'Board Game', 'Puzzle', 'Doll', 'Building Blocks', 'Card Game'],
            'Automotive' => ['Motor Oil', 'Tire', 'Car Battery', 'Air Filter', 'Brake Pad', 'Spark Plug'],
            'Office Supplies' => ['Printer', 'Notebook', 'Pen Set', 'Stapler', 'Paper', 'Ink Cartridge', 'Calculator']
        ];

        $baseName = $faker->randomElement($productNames[$category]);
        $model = $faker->randomElement(['Pro', 'Max', 'Elite', 'Premium', 'Classic', 'Standard', 'Deluxe']);
        
        return "{$brand} {$baseName} {$model}";
    }

    private function getTaxRate(string $province): float
    {
        // Simplified Canadian tax rates
        $taxRates = [
            'Ontario' => 0.13,
            'Quebec' => 0.15,
            'British Columbia' => 0.12,
            'Alberta' => 0.05,
            'Manitoba' => 0.12,
            'Saskatchewan' => 0.11,
            'Nova Scotia' => 0.15,
            'New Brunswick' => 0.15,
            'Newfoundland and Labrador' => 0.15,
            'Prince Edward Island' => 0.15,
            'Northwest Territories' => 0.05,
            'Nunavut' => 0.05,
            'Yukon' => 0.05
        ];

        return $taxRates[$province] ?? 0.10;
    }
} 