<?php

namespace App\Console\Commands;

use App\Jobs\ProcessKaggleDatasetJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportKaggleDataset extends Command
{
    protected $signature = 'kaggle:import 
                           {file : Path to CSV file (relative to storage/app)}
                           {--chunk-size=1000 : Number of rows to process in each chunk}
                           {--max-rows= : Maximum number of rows to process (for testing)}
                           {--queue= : Queue name to dispatch job to}
                           {--no-dw-etl : Skip data warehouse ETL after import}
                           {--sync : Run synchronously instead of queuing}
                           {--force : Skip confirmation prompt}';

    protected $description = 'Import Kaggle product price dataset into the system';

    public function handle()
    {
        $filePath = $this->argument('file');
        $chunkSize = (int) $this->option('chunk-size');
        $maxRows = $this->option('max-rows') ? (int) $this->option('max-rows') : null;
        $queue = $this->option('queue');
        $triggerDwEtl = !$this->option('no-dw-etl');
        $runSync = $this->option('sync');

        // Validate file exists
        if (!Storage::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            $this->info("Available files in storage:");
            foreach (Storage::files('imports') as $file) {
                $this->line("  - {$file}");
            }
            return 1;
        }

        // Prepare job options
        $options = [
            'chunk_size' => $chunkSize,
            'max_rows' => $maxRows,
            'trigger_dw_etl' => $triggerDwEtl,
            'data_source_tag' => 'kaggle-import'
        ];

        $this->info('Starting Kaggle dataset import...');
        $this->table(['Option', 'Value'], [
            ['File Path', $filePath],
            ['Chunk Size', $chunkSize],
            ['Max Rows', $maxRows ?: 'unlimited'],
            ['Trigger DW ETL', $triggerDwEtl ? 'yes' : 'no'],
            ['Run Mode', $runSync ? 'synchronous' : 'queued'],
            ['Queue', $queue ?: 'default']
        ]);

        if (!$this->option('force') && !$this->confirm('Continue with import?')) {
            $this->info('Import cancelled.');
            return 0;
        }

        // Dispatch job
        $job = new ProcessKaggleDatasetJob($filePath, $options);

        if ($runSync) {
            $this->info('Running import synchronously...');
            try {
                $job->handle();
                $this->info('✅ Import completed successfully!');
            } catch (\Exception $e) {
                $this->error('❌ Import failed: ' . $e->getMessage());
                return 1;
            }
        } else {
            $queue ? $job->onQueue($queue) : null;
            dispatch($job);
            $this->info('✅ Import job queued successfully!');
            $this->info('Monitor progress with: php artisan queue:work');
        }

        return 0;
    }
} 