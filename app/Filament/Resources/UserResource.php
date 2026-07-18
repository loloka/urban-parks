<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $modelLabel = 'пользователь';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?int $navigationSort = 3;

    /**
     * Управление пользователями — только для админов.
     * Модераторы модерируют активации, но не трогают аккаунты/роли.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Профиль')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('callsign')
                            ->label('Позывной')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state) => $state ? strtoupper($state) : null)
                            ->placeholder('R9OGL'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('role')
                            ->label('Роль')
                            ->options([
                                User::ROLE_USER => 'Пользователь',
                                User::ROLE_MODERATOR => 'Модератор',
                                User::ROLE_ADMIN => 'Администратор',
                            ])
                            ->default(User::ROLE_USER)
                            ->required()
                            ->helperText('Модератор и админ имеют доступ в эту админку'),
                    ])->columns(2),

                Forms\Components\Section::make('Доступ')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->minLength(8)
                            ->helperText('При редактировании оставьте пустым, чтобы не менять'),

                        Forms\Components\Toggle::make('email_verified')
                            ->label('Email подтверждён')
                            ->helperText('Нужен для загрузки активаций')
                            ->afterStateHydrated(function (Forms\Components\Toggle $component, $record) {
                                $component->state($record?->email_verified_at !== null);
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('callsign')
                    ->label('Позывной')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Роль')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'danger',
                        User::ROLE_MODERATOR => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN => 'Админ',
                        User::ROLE_MODERATOR => 'Модератор',
                        default => 'Пользователь',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email ✓')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->email_verified_at !== null),

                Tables\Columns\TextColumn::make('activations_count')
                    ->label('Активаций')
                    ->counts('activations')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Роль')
                    ->options([
                        User::ROLE_USER => 'Пользователь',
                        User::ROLE_MODERATOR => 'Модератор',
                        User::ROLE_ADMIN => 'Администратор',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('verifyEmail')
                    ->label('Подтвердить email')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->email_verified_at === null)
                    ->action(fn (User $record) => $record->forceFill(['email_verified_at' => now()])->save()),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    // Нельзя удалить самого себя
                    ->visible(fn (User $record): bool => $record->id !== auth()->id()),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
