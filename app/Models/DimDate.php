<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DimDate extends Model
{
    use HasFactory;

    protected $table = 'dim_dates';

    protected $fillable = [
        'full_date',
        'day',
        'week',
        'month',
        'year',
        'weekday',
        'month_name',
        'weekday_name',
        'quarter',
        'is_weekend'
    ];

    protected $casts = [
        'full_date' => 'date',
        'is_weekend' => 'boolean'
    ];

    public function priceChanges(): HasMany
    {
        return $this->hasMany(FactPriceChange::class, 'date_id');
    }

    public static function createFromDate(\Carbon\Carbon $date): self
    {
        return self::create([
            'full_date' => $date->toDateString(),
            'day' => $date->day,
            'week' => $date->week,
            'month' => $date->month,
            'year' => $date->year,
            'weekday' => $date->dayOfWeek,
            'month_name' => $date->monthName,
            'weekday_name' => $date->dayName,
            'quarter' => $date->quarter,
            'is_weekend' => $date->isWeekend()
        ]);
    }
} 