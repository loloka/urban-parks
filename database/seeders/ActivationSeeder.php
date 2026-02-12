<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activation;
use App\Models\Park;

class ActivationSeeder extends Seeder
{
    public function run(): void
    {
        $callsigns = ['R0AA', 'R3AM', 'R5AW', 'R7GA', 'R9AB', 'RA3CO', 'RA4LW', 'RK3PWJ', 'RW3AI', 'UA3DPM'];
        $parks = Park::active()->get();

        // Создаём по 2-5 активаций для каждого парка
        foreach ($parks as $park) {
            $activationCount = rand(2, 5);

            for ($i = 0; $i < $activationCount; $i++) {
                Activation::create([
                    'park_id' => $park->id,
                    'callsign' => $callsigns[array_rand($callsigns)],
                    'activation_date' => now()->subDays(rand(1, 365)),
                    'qso_count' => rand(10, 150),
                    'notes' => rand(0, 1) ? 'Отличные условия прохождения' : null,
                ]);
            }
        }

        $this->command->info('✅ Создано ' . Activation::count() . ' активаций!');
    }
}
