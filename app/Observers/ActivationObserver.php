<?php

namespace App\Observers;

use App\Models\Activation;

class ActivationObserver
{
    /**
     * Handle the Activation "created" event.
     */
    public function created(Activation $activation): void
    {
        $this->updateParkActivationCount($activation->park_id);
    }

    /**
     * Handle the Activation "updated" event.
     */
    public function updated(Activation $activation): void
    {
        $this->updateParkActivationCount($activation->park_id);

        // Если изменился парк, обновляем оба
        if ($activation->isDirty('park_id')) {
            $this->updateParkActivationCount($activation->getOriginal('park_id'));
        }
    }

    /**
     * Handle the Activation "deleted" event.
     */
    public function deleted(Activation $activation): void
    {
        $this->updateParkActivationCount($activation->park_id);
    }

    /**
     * Обновить счётчик активаций парка
     */
    private function updateParkActivationCount(int $parkId): void
    {
        $count = Activation::where('park_id', $parkId)->count();

        \App\Models\Park::where('id', $parkId)->update([
            'activation_count' => $count
        ]);
    }
}
