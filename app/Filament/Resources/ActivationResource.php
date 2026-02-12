<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivationResource\Pages;
use App\Models\Activation;
use App\Models\Park;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ActivationResource extends Resource
{
    protected static ?string $model = Activation::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationLabel = 'Активации';

    protected static ?string $modelLabel = 'активация';

    protected static ?string $pluralModelLabel = 'Активации';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация об активации')
                    ->schema([
                        Forms\Components\Select::make('park_id')
                            ->label('Парк')
                            ->options(Park::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->preload()
                            ->helperText('Выберите парк из списка'),

                        Forms\Components\TextInput::make('callsign')
                            ->label('Позывной')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('R0AA')
                            ->helperText('Позывной активатора')
                            ->dehydrateStateUsing(fn($state) => strtoupper($state)), // Преобразуем в верхний регистр при сохранении

                        Forms\Components\DatePicker::make('activation_date')
                            ->label('Дата активации')
                            ->required()
                            ->default(now())
                            ->maxDate(now())
                            ->native(false),

                        Forms\Components\TextInput::make('qso_count')
                            ->label('Количество QSO')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Сколько связей проведено'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Заметки')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Дополнительная информация об активации...'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('park.reference')
                    ->label('Парк')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('park.name')
                    ->label('Название парка')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('callsign')
                    ->label('Позывной')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('activation_date')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('qso_count')
                    ->label('QSO')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Добавлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('park_id')
                    ->label('Парк')
                    ->options(Park::pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('activation_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('С даты'),
                        Forms\Components\DatePicker::make('until')
                            ->label('По дату'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('activation_date', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('activation_date', '<=', $date));
                    }),
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
            ->defaultSort('activation_date', 'desc');
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
            'index' => Pages\ListActivations::route('/'),
            'create' => Pages\CreateActivation::route('/create'),
            'edit' => Pages\EditActivation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
