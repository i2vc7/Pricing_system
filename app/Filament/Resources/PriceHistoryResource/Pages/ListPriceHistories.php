<?php

namespace App\Filament\Resources\PriceHistoryResource\Pages;

use App\Filament\Resources\PriceHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPriceHistories extends ListRecords
{
    protected static string $resource = PriceHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
