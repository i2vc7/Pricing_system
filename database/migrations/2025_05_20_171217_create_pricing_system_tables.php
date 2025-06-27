<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('url')->nullable();
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('brand_id')->constrained()->onDelete('restrict');
            $table->foreignId('store_id')->constrained()->onDelete('restrict');
            $table->string('sku', 100);
            $table->decimal('current_price', 10, 2);
            $table->decimal('vat_percentage', 5, 2)->default(15.00);
            $table->timestamps();
            $table->softDeletes();
            $table->index('sku');
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('store_id');
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('locale', ['ar', 'en']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'locale']);
        });

        Schema::create('price_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->timestamp('effective_date');
            $table->timestamps();
            $table->softDeletes();
            $table->index('product_id');
            $table->index('effective_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_histories');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
?>
