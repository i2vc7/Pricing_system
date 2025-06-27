<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id', 
        'store_id',
        'sku',
        'current_price',
        'vat_percentage'
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
        'vat_percentage' => 'decimal:2'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    // Get translated name for current locale
    public function getNameAttribute()
    {
        return $this->translations()
            ->where('locale', app()->getLocale())
            ->value('name') ?: $this->sku;
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class)->withTimestamps()->withPivot([
            "id",
            'price',
            'highest_price',
            'lowest_price',
            'notify_price',
            'notify_percentage',
            'rate',
            'number_of_rates',
            'seller',
            'shipping_price',
            'updated_at',
            'add_shipping',
        ]);
    }

    public function product_stores(): HasMany
    {
        return $this->hasMany(ProductStore::class);
    }
}
