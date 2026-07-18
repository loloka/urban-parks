<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                // Нельзя удалить самого себя
                ->visible(fn (): bool => $this->record->id !== auth()->id()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Тумблер «Email подтверждён» → колонка email_verified_at (не перетираем дату, если уже была)
        $verified = ! empty($data['email_verified']);
        $data['email_verified_at'] = $verified ? ($this->record->email_verified_at ?? now()) : null;
        unset($data['email_verified']);

        return $data;
    }
}
