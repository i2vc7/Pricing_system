<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImportProductPrices extends Command
{
    protected $signature = 'import:product-prices {file : Path to the CSV file}';
    protected $description = 'Import product prices from a CSV file';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info('Starting import...');

        DB::beginTransaction();

        try {
            $handle = fopen($file, 'r');
            $headers = fgetcsv($handle);
            $row = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $row++;
                $record = array_combine($headers, $data);

                // Validate the record
                $validator = Validator::make($record, [
                    'name' => 'required|string|max:255',
                    'sku' => 'required|string|max:100',
                    'category' => 'required|string|max:255',
                    'brand' => 'required|string|max:255',
                    'store' => 'required|string|max:255',
                    'price' => 'required|numeric|min:0',
                    'date' => 'required|date',
                ]);

                if ($validator->fails()) {
                    Log::warning("Invalid record at row {$row}", [
                        'errors' => $validator->errors()->toArray(),
                        'data' => $record
                    ]);
                    continue;
                }

                // Find or create related entities
                $category = Category::firstOrCreate(['name' => $record['category']]);
                $brand = Brand::firstOrCreate(['name' => $record['brand']]);
                $store = Store::firstOrCreate(['name' => $record['store']]);

                // Find or create product
                $product = Product::firstOrCreate(
                    ['sku' => $record['sku']],
                    [
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'store_id' => $store->id,
                        'current_price' => $record['price'],
                    ]
                );

                // Create product translation
                $product->translations()->updateOrCreate(
                    ['locale' => 'en'],
                    ['name' => $record['name']]
                );

                // Create price history
                PriceHistory::create([
                    'product_id' => $product->id,
                    'price' => $record['price'],
                    'effective_date' => Carbon::parse($record['date']),
                ]);

                // Update current price if the new price is more recent
                $latestPrice = $product->priceHistories()
                    ->orderBy('effective_date', 'desc')
                    ->first();

                if ($latestPrice && $latestPrice->price != $product->current_price) {
                    $product->update(['current_price' => $latestPrice->price]);
                }
            }

            fclose($handle);
            DB::commit();

            $this->info('Import completed successfully!');
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import failed', ['error' => $e->getMessage()]);
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}