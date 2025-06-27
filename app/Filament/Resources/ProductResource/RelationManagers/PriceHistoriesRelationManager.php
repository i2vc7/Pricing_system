<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PriceHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'priceHistories';

    protected static ?string $recordTitleAttribute = 'effective_date';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Price History Entry')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->placeholder('0.00'),
                        Forms\Components\DateTimePicker::make('effective_date')
                            ->label('Effective Date')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d H:i:s'),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('effective_date')
            ->columns([
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_date')
                    ->label('Effective Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('recent')
                    ->query(fn (Builder $query): Builder => $query->where('effective_date', '>=', now()->subDays(30)))
                    ->label('Last 30 Days'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Price History'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('effective_date', 'desc')
            ->emptyStateHeading('No price history yet')
            ->emptyStateDescription('Add the first price history entry for this product.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }
}
