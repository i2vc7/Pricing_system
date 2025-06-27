<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_imports', function (Blueprint $table) {
            $table->id();
            $table->string('import_id')->unique();
            $table->string('file_path');
            $table->string('data_source')->default('kaggle-import');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('options')->nullable(); // Store import options
            $table->json('stats')->nullable(); // Store processing statistics
            $table->text('error_message')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('products_created')->default(0);
            $table->integer('price_histories_created')->default(0);
            $table->integer('errors_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'data_source']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_imports');
    }
}; 