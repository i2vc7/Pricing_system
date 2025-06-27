<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DimProduct extends Model
{
    use HasFactory;

    protected $table = 'dim_products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'brand',
        'category',
        'store',
        'vat_percentage',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'vat_percentage' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function priceChanges(): HasMany
    {
        return $this->hasMany(FactPriceChange::class, 'product_id', 'product_id');
    }
} 