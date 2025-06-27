<?php

namespace App\Console;

use App\Jobs\EtlDataWarehouseJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily ETL job - runs at 2 AM every day (incremental load)
        $schedule->job(new EtlDataWarehouseJob(false))
            ->dailyAt('02:00')
            ->name('etl-daily-incremental')
            ->withoutOverlapping()
            ->runInBackground();

        // Weekly full refresh - runs every Sunday at 1 AM
        $schedule->job(new EtlDataWarehouseJob(true))
            ->weeklyOn(0, '01:00')
            ->name('etl-weekly-full-refresh')
            ->withoutOverlapping()
            ->runInBackground();

        // Example: Auto-import Kaggle data if new files are found
        // $schedule->call(function () {
        //     $files = Storage::files('imports/auto');
        //     foreach ($files as $file) {
        //         if (str_ends_with($file, '.csv')) {
        //             ProcessKaggleDatasetJob::dispatch($file, [
        //                 'chunk_size' => 2000,
        //                 'data_source_tag' => 'kaggle-scheduled'
        //             ]);
        //             Storage::move($file, 'imports/processed/' . basename($file));
        //         }
        //     }
        // })->hourly()->name('auto-import-kaggle');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}