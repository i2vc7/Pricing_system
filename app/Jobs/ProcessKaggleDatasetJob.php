<?php

namespace App\Jobs;

use App\Models\DataImport;
use App\Services\KaggleEtlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessKaggleDatasetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $filePath;
    private array $options;
    private string $importId;

    public $timeout = 3600; // 1 hour
    public $tries = 3;

    public function __construct(string $filePath, array $options = [])
    {
        $this->filePath = $filePath;
        $this->options = array_merge([
            'chunk_size' => 1000,
            'max_rows' => null,
            'data_source_tag' => 'kaggle-import',
            'trigger_dw_etl' => true
        ], $options);
        
        $this->importId = 'kaggle_' . now()->format('YmdHis') . '_' . uniqid();
    }

    public function handle()
    {
        // Create or find existing import record (for retries)
        $importRecord = DataImport::firstOrCreate(
            ['import_id' => $this->importId],
            [
                'file_path' => $this->filePath,
                'data_source' => $this->options['data_source_tag'],
                'options' => $this->options,
                'status' => 'processing',
                'started_at' => now()
            ]
        );
        
        // Update status to processing in case of retry
        $importRecord->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null
        ]);

        Log::info('KaggleDataset: Starting import', [
            'import_id' => $this->importId,
            'file_path' => $this->filePath,
            'options' => $this->options
        ]);

        try {
            if (!Storage::exists($this->filePath)) {
                throw new \Exception("File not found: {$this->filePath}");
            }

            $etlService = new KaggleEtlService();
            $this->processCsvFile($etlService);

            $finalStats = $etlService->getStats();
            
            Log::info('KaggleDataset: Import completed', [
                'import_id' => $this->importId,
                'final_stats' => $finalStats
            ]);

            // Trigger data warehouse ETL if requested
            if ($this->options['trigger_dw_etl']) {
                $etlService->triggerDataWarehouseEtl();
            }

            // Mark import as completed
            $importRecord->markAsCompleted($finalStats);

        } catch (\Exception $e) {
            $importRecord->markAsFailed($e->getMessage());
            
            Log::error('KaggleDataset: Import failed', [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function processCsvFile(KaggleEtlService $etlService): void
    {
        $filePath = Storage::path($this->filePath);
        $handle = fopen($filePath, 'r');
        
        if (!$handle) {
            throw new \Exception("Could not open file: {$this->filePath}");
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception("Could not read CSV headers");
        }

        $processedRows = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            // Check max rows limit
            if ($this->options['max_rows'] && $processedRows >= $this->options['max_rows']) {
                break;
            }

            // Convert row to associative array
            $data = array_combine($headers, $row);
            if ($data === false) {
                Log::warning('KaggleDataset: Could not combine headers with row', [
                    'import_id' => $this->importId,
                    'row_number' => $processedRows + 1,
                    'headers_count' => count($headers),
                    'row_count' => count($row)
                ]);
                continue;
            }

            // Process the row
            $etlService->processRow($data);
            $processedRows++;

            // Log progress every 1000 rows
            if ($processedRows % 1000 === 0) {
                Log::info('KaggleDataset: Progress update', [
                    'import_id' => $this->importId,
                    'processed_rows' => $processedRows,
                    'stats' => $etlService->getStats()
                ]);
            }
        }

        fclose($handle);
        
        Log::info('KaggleDataset: CSV processing completed', [
            'import_id' => $this->importId,
            'total_processed' => $processedRows
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('KaggleDataset: Job failed permanently', [
            'import_id' => $this->importId,
            'file_path' => $this->filePath,
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['kaggle-import', 'etl', $this->importId];
    }
} 