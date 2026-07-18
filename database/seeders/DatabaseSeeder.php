<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ParkSeeder::class,
            // ActivationSeeder — только для локальной разработки (тестовые активации).
            // Намеренно НЕ вызывается: на боевом деплое нужны реальные данные, без тестовых.
            // Запустить вручную при необходимости: php artisan db:seed --class=ActivationSeeder
        ]);
    }
}
