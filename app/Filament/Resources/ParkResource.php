<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParkResource\Pages;
use App\Models\Park;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ParkResource extends Resource
{
    protected static ?string $model = Park::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Парки';

    protected static ?string $modelLabel = 'парк';

    protected static ?string $pluralModelLabel = 'Парки';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Референс')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('UP-RU-NSK-0001')
                            ->maxLength(255)
                            ->helperText('Формат: UP-RU-NSK-0001'),

                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('city')
                            ->label('Город')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('region')
                            ->label('Регион')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Координаты')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Широта')
                            ->required()
                            ->numeric()
                            ->step(0.0000001)
                            ->placeholder('55.751244')
                            ->helperText('Формат: 55.751244'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Долгота')
                            ->required()
                            ->numeric()
                            ->step(0.0000001)
                            ->placeholder('37.618423')
                            ->helperText('Формат: 37.618423'),
                    ])->columns(2),

                Forms\Components\Section::make('Дополнительно')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('area')
                            ->label('Площадь')
                            ->maxLength(255)
                            ->placeholder('10.5 га'),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'active' => 'Активен',
                                'pending' => 'На рассмотрении',
                                'inactive' => 'Неактивен',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\TextInput::make('activation_count')
                            ->label('Количество активаций')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Референс')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('city')
                    ->label('Город')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('region')
                    ->label('Регион')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('activation_count')
                    ->label('Активаций')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'inactive' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Активен',
                        'pending' => 'На рассмотрении',
                        'inactive' => 'Неактивен',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city')
                    ->label('Город')
                    ->options(function () {
                        return Park::distinct()->pluck('city', 'city')->toArray();
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активен',
                        'pending' => 'На рассмотрении',
                        'inactive' => 'Неактивен',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('reference', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParks::route('/'),
            'create' => Pages\CreatePark::route('/create'),
            'edit' => Pages\EditPark::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
