<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivationResource\Pages;
use App\Models\Activation;
use App\Models\ActivationProof;
use App\Models\Park;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components as Info;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;

class ActivationResource extends Resource
{
    protected static ?string $model = Activation::class;
    protected static ?string $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationLabel = 'Активации';
    protected static ?string $modelLabel = 'активация';
    protected static ?string $pluralModelLabel = 'Активации';
    protected static ?int $navigationSort = 2;

    /**
     * Экран модерации: всё для решения за 10 секунд —
     * кто → где → пруфы → сводка лога → кнопки (в header ViewActivation)
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Info\Section::make()
                ->schema([
                    Info\TextEntry::make('callsign')
                        ->label('Позывной')
                        ->weight('bold')
                        ->size('lg')
                        ->url(fn (Activation $r) => "https://www.qrz.com/db/{$r->callsign}", shouldOpenInNewTab: true),
                    Info\TextEntry::make('park.reference')
                        ->label('Парк')
                        ->badge()
                        ->color('primary'),
                    Info\TextEntry::make('park.name')
                        ->label(''),
                    Info\TextEntry::make('activation_date')
                        ->label('Дата')
                        ->date('d.m.Y'),
                    Info\TextEntry::make('status')
                        ->label('Статус')
                        ->badge()
                        ->color(fn (string $state) => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state) => match ($state) {
                            'pending' => 'На модерации',
                            'approved' => 'Одобрено',
                            'rejected' => 'Отклонено',
                            default => $state,
                        }),
                    Info\TextEntry::make('source')
                        ->label('Источник')
                        ->badge()
                        ->color(fn (string $state) => $state === 'adif' ? 'success' : 'gray')
                        ->formatStateUsing(fn (string $state) => $state === 'adif' ? 'ADIF-лог' : 'Ручной ввод'),
                ])
                ->columns(3),

            Info\Section::make('Пруфы')
                ->description('Скриншот QTHnow обязателен; фото — галерея активации')
                ->schema([
                    Info\TextEntry::make('proofs')
                        ->label('')
                        ->getStateUsing(function (Activation $record) {
                            if ($record->proofs->isEmpty()) {
                                return new HtmlString('<em>Пруфы не прикреплены (активация добавлена вручную)</em>');
                            }

                            $html = '<div style="display:flex;flex-wrap:wrap;gap:12px;">';
                            foreach ($record->proofs as $proof) {
                                $url = route('proofs.show', $proof);
                                $label = match ($proof->type) {
                                    ActivationProof::TYPE_SCREENSHOT => '📱 QTHnow',
                                    ActivationProof::TYPE_GPX => '🗺 GPX-трек',
                                    default => '📷 Фото',
                                };
                                if ($proof->type === ActivationProof::TYPE_GPX) {
                                    $html .= "<a href=\"{$url}\" target=\"_blank\" style=\"display:block;padding:12px;border:1px solid #e5e7eb;border-radius:8px;\">{$label}</a>";
                                } else {
                                    $html .= "<a href=\"{$url}\" target=\"_blank\" style=\"display:block;\">"
                                        . "<img src=\"{$url}\" style=\"height:180px;border-radius:8px;object-fit:cover;\" loading=\"lazy\" />"
                                        . "<div style=\"font-size:12px;color:#6b7280;margin-top:4px;text-align:center;\">{$label}</div>"
                                        . '</a>';
                                }
                            }

                            return new HtmlString($html . '</div>');
                        }),
                ])
                ->visible(fn (Activation $r) => $r->source === Activation::SOURCE_ADIF || $r->proofs->isNotEmpty()),

            Info\Section::make('Лог')
                ->schema([
                    Info\TextEntry::make('qso_count')
                        ->label('Всего QSO')
                        ->badge()
                        ->color('success'),
                    Info\TextEntry::make('bands_summary')
                        ->label('Диапазоны')
                        ->getStateUsing(fn (Activation $r) => self::groupSummary($r, 'band')),
                    Info\TextEntry::make('modes_summary')
                        ->label('Моды')
                        ->getStateUsing(fn (Activation $r) => self::groupSummary($r, 'mode')),
                    Info\TextEntry::make('time_span')
                        ->label('Время работы (UTC)')
                        ->getStateUsing(function (Activation $r) {
                            $first = $r->qsos()->min('time_on');
                            $last = $r->qsos()->max('time_on');

                            return $first ? substr($first, 0, 5) . ' — ' . substr($last, 0, 5) : '—';
                        }),
                ])
                ->columns(4)
                ->visible(fn (Activation $r) => $r->source === Activation::SOURCE_ADIF),

            Info\Section::make('Заметки')
                ->schema([
                    Info\TextEntry::make('notes')->label('Заметки активатора')->placeholder('—'),
                    Info\TextEntry::make('moderator_note')->label('Комментарий модератора')->placeholder('—'),
                ])
                ->columns(2)
                ->collapsed(),
        ]);
    }

    /** Сводка вида "40M ×32 · 20M ×15" по колонке qsos */
    private static function groupSummary(Activation $record, string $column): string
    {
        $groups = $record->qsos()
            ->selectRaw("{$column}, COUNT(*) as cnt")
            ->groupBy($column)
            ->orderByDesc('cnt')
            ->pluck('cnt', $column);

        return $groups->isEmpty()
            ? '—'
            : $groups->map(fn ($cnt, $key) => "{$key} ×{$cnt}")->implode(' · ');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Модерация')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'pending' => '⏳ На модерации',
                                'approved' => '✅ Одобрено',
                                'rejected' => '❌ Отклонено',
                            ])
                            ->required()
                            ->default('pending')
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('moderator_note')
                            ->label('Комментарий модератора')
                            ->rows(2)
                            ->placeholder('Причина отклонения или дополнительные заметки...')
                            ->columnSpanFull()
                            ->visible(fn($get) => $get('status') === 'rejected'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Информация об активации')
                    ->schema([
                        Forms\Components\Select::make('park_id')
                            ->label('Парк')
                            ->options(Park::pluck('name', 'id'))
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
                            ->dehydrateStateUsing(fn($state) => strtoupper($state)),

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
                            ->label('Заметки активатора')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Дополнительная информация об активации...'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'На модерации',
                        'approved' => 'Одобрено',
                        'rejected' => 'Отклонено',
                        default => $state
                    }),

                Tables\Columns\TextColumn::make('park.reference')
                    ->label('Парк')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

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

                Tables\Columns\TextColumn::make('source')
                    ->label('Источник')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state === 'adif' ? 'ADIF' : 'вручную')
                    ->color(fn($state) => $state === 'adif' ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Добавлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'На модерации',
                        'approved' => 'Одобрено',
                        'rejected' => 'Отклонено',
                    ])
                    ->default('pending'), // По умолчанию показываем только pending

                SelectFilter::make('park_id')
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
                Tables\Actions\ViewAction::make()
                    ->label('👁 Проверить'),

                Tables\Actions\Action::make('approve')
                    ->label('✅ Одобрить')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(Activation $record) => $record->update(['status' => 'approved']))
                    ->visible(fn(Activation $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label('❌ Отклонить')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('moderator_note')
                            ->label('Причина отклонения')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Activation $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'moderator_note' => $data['moderator_note']
                        ]);
                    })
                    ->visible(fn(Activation $record) => $record->status === 'pending'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('✅ Одобрить выбранные')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['status' => 'approved'])),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivations::route('/'),
            'create' => Pages\CreateActivation::route('/create'),
            'view' => Pages\ViewActivation::route('/{record}'),
            'edit' => Pages\EditActivation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Показываем количество активаций на модерации
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() > 0
            ? 'warning'
            : 'primary';
    }
}
