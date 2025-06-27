<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dimension: Products
        Schema::create('dim_products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('sku', 100)->index();
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('store')->nullable();
            $table->decimal('vat_percentage', 5, 2)->default(15.00);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            
            $table->index(['brand', 'category', 'store']);
        });

        // Dimension: Dates
        Schema::create('dim_dates', function (Blueprint $table) {
            $table->id();
            $table->date('full_date')->unique();
            $table->integer('day');
            $table->integer('week');
            $table->integer('month');
            $table->integer('year');
            $table->integer('weekday'); // 1=Monday, 7=Sunday
            $table->string('month_name');
            $table->string('weekday_name');
            $table->integer('quarter');
            $table->boolean('is_weekend');
            $table->timestamps();
            
            $table->index(['year', 'month']);
            $table->index(['year', 'quarter']);
            $table->index('week');
        });

        // Fact: Price Changes
        Schema::create('fact_price_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('dim_products', 'product_id')->onDelete('cascade');
            $table->foreignId('date_id')->constrained('dim_dates')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('price_change', 10, 2)->nullable(); // Change from previous period
            $table->decimal('price_change_percentage', 8, 4)->nullable(); // Percentage change
            $table->timestamp('effective_datetime');
            $table->timestamps();
            
            $table->index(['product_id', 'date_id']);
            $table->index('effective_datetime');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fact_price_changes');
        Schema::dropIfExists('dim_dates');
        Schema::dropIfExists('dim_products');
    }
}; 