<?php

namespace App\Filament\Resources\DataImportResource\Pages;

use App\Filament\Resources\DataImportResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewDataImport extends ViewRecord
{
    protected static string $resource = DataImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('retry')
                ->label('Retry Import')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => $this->record->status === 'failed')
                ->action(function () {
                    \App\Jobs\ProcessKaggleDatasetJob::dispatch(
                        $this->record->file_path,
                        $this->record->options ?? []
                    );
                    
                    $this->notify('success', 'Import job has been queued for retry');
                })
                ->requiresConfirmation(),
        ];
    }
} 