<?php

namespace App\Filament\Resources\ActivationResource\Pages;

use App\Filament\Resources\ActivationResource;
use App\Models\Activation;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewActivation extends ViewRecord
{
    protected static string $resource = ActivationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('✅ Одобрить')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => Activation::STATUS_APPROVED]);
                    $this->redirect(ActivationResource::getUrl('index'));
                })
                ->visible(fn () => $this->record->status === Activation::STATUS_PENDING),

            Actions\Action::make('reject')
                ->label('❌ Отклонить')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('moderator_note')
                        ->label('Причина отклонения (увидит активатор)')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => Activation::STATUS_REJECTED,
                        'moderator_note' => $data['moderator_note'],
                    ]);
                    $this->redirect(ActivationResource::getUrl('index'));
                })
                ->visible(fn () => $this->record->status === Activation::STATUS_PENDING),

            Actions\Action::make('download_adif')
                ->label('⬇ Скачать лог')
                ->color('gray')
                ->url(fn () => route('activations.adif', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => (bool) $this->record->adif_path),

            Actions\EditAction::make(),
        ];
    }
}
