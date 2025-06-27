<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'file_path',
        'data_source',
        'status',
        'options',
        'stats',
        'error_message',
        'total_rows',
        'processed_rows',
        'products_created',
        'price_histories_created',
        'errors_count',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'options' => 'array',
        'stats' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now()
        ]);
    }

    public function markAsCompleted(array $stats): void
    {
        $this->update([
            'status' => 'completed',
            'stats' => $stats,
            'processed_rows' => $stats['processed'] ?? 0,
            'products_created' => $stats['products_created'] ?? 0,
            'price_histories_created' => $stats['price_histories_created'] ?? 0,
            'errors_count' => $stats['errors'] ?? 0,
            'completed_at' => now()
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now()
        ]);
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->completed_at->diffInSeconds($this->started_at);
        }
        return null;
    }

    public function getSuccessRateAttribute(): ?float
    {
        if ($this->processed_rows > 0) {
            $successful = $this->processed_rows - $this->errors_count;
            return ($successful / $this->processed_rows) * 100;
        }
        return null;
    }
} 