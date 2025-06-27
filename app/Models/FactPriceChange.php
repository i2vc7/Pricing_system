<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactPriceChange extends Model
{
    use HasFactory;

    protected $table = 'fact_price_changes';

    protected $fillable = [
        'product_id',
        'date_id',
        'price',
        'price_change',
        'price_change_percentage',
        'effective_datetime'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_change' => 'decimal:2',
        'price_change_percentage' => 'decimal:4',
        'effective_datetime' => 'datetime'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(DimProduct::class, 'product_id', 'product_id');
    }

    public function date(): BelongsTo
    {
        return $this->belongsTo(DimDate::class, 'date_id');
    }
} 