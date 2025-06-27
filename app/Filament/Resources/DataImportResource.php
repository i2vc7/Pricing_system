<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataImportResource\Pages;
use App\Models\DataImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class DataImportResource extends Resource
{
    protected static ?string $model = DataImport::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Data Management';

    protected static ?string $navigationLabel = 'Import History';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Import Details')
                    ->schema([
                        Forms\Components\TextInput::make('import_id')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('file_path')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('data_source')
                            ->options([
                                'kaggle-import' => 'Kaggle Import',
                                'manual-upload' => 'Manual Upload',
                                'scheduled-import' => 'Scheduled Import'
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed'
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('total_rows')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('processed_rows')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('products_created')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('price_histories_created')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('errors_count')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Timing')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at'),
                        Forms\Components\DateTimePicker::make('completed_at'),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('error_message')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('options')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('stats')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('import_id')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kaggle-import' => 'info',
                        'manual-upload' => 'warning',
                        'scheduled-import' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('processed_rows')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_created')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_histories_created')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Success Rate')
                    ->formatStateUsing(fn (?float $state): string => $state ? number_format($state, 1) . '%' : 'N/A')
                    ->color(fn (?float $state): string => match (true) {
                        $state >= 95 => 'success',
                        $state >= 80 => 'warning',
                        $state < 80 => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(fn (?int $state): string => $state ? gmdate('H:i:s', $state) : 'N/A'),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed'
                    ]),
                SelectFilter::make('data_source')
                    ->options([
                        'kaggle-import' => 'Kaggle Import',
                        'manual-upload' => 'Manual Upload',
                        'scheduled-import' => 'Scheduled Import'
                    ]),
                Tables\Filters\Filter::make('recent')
                    ->query(fn ($query) => $query->where('created_at', '>=', now()->subDays(7)))
                    ->label('Last 7 Days'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (DataImport $record): bool => $record->status === 'failed')
                    ->action(function (DataImport $record) {
                        // Dispatch a new job with the same parameters
                        \App\Jobs\ProcessKaggleDatasetJob::dispatch(
                            $record->file_path,
                            $record->options ?? []
                        );
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataImports::route('/'),
            'view' => Pages\ViewDataImport::route('/{record}'),
        ];
    }
} 