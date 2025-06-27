<?php

namespace App\Filament\Resources\DataImportResource\Pages;

use App\Filament\Resources\DataImportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListDataImports extends ListRecords
{
    protected static string $resource = DataImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_demo')
                ->label('Generate Demo Data')
                ->icon('heroicon-o-document-plus')
                ->color('info')
                ->action(function () {
                    $this->redirect('/admin/generate-demo-data');
                }),
        ];
    }
} 