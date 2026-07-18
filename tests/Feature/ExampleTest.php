<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Тесты ходят в sqlite :memory: (см. phpunit.xml) — миграции накатываются сами
    use RefreshDatabase;

    /**
     * Главная страница открывается (пустая БД — тоже валидное состояние)
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Vite-манифест в тестах не собираем
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
