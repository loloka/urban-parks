<?php

namespace Tests\Feature;

use App\Models\Activation;
use App\Models\Park;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivationUploadTest extends TestCase
{
    use RefreshDatabase;

    private Park $park;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->park = Park::create([
            'country_code' => 'RU',
            'region_code' => 'NSK',
            'reference' => 'UP-RU-NSK-0001',
            'name' => 'Центральный парк',
            'city' => 'Новосибирск',
            'region' => 'Новосибирская область',
            'latitude' => 55.0296,
            'longitude' => 82.9145,
            'status' => 'active',
        ]);
    }

    private function adifFile(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'log.adi',
            file_get_contents(base_path('tests/fixtures/sample.adi'))
        );
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'park_id' => $this->park->id,
            'callsign' => 'R9OGL',
            'adif' => $this->adifFile(),
            'screenshot' => UploadedFile::fake()->image('qthnow.png', 800, 600),
            'photos' => [
                UploadedFile::fake()->image('antenna.jpg', 800, 600),
                UploadedFile::fake()->image('rig.jpg', 800, 600),
            ],
            'notes' => 'Тестовая активация',
        ], $overrides);
    }

    public function test_uploads_adif_log_and_creates_activation_with_qsos_and_proofs(): void
    {
        $response = $this->post(route('activations.store'), $this->validPayload());

        $response->assertRedirect(route('activations.create'));
        $response->assertSessionHas('success');

        $activation = Activation::first();

        $this->assertNotNull($activation);
        $this->assertSame(Activation::STATUS_PENDING, $activation->status);
        $this->assertSame(Activation::SOURCE_ADIF, $activation->source);
        $this->assertSame('R9OGL', $activation->callsign);
        $this->assertSame('2026-07-15', $activation->activation_date->format('Y-m-d'));

        // 4 валидных QSO из фикстуры (2 отбрасывает парсер)
        $this->assertSame(4, $activation->qsos()->count());
        $this->assertSame(4, $activation->qso_count);

        // Пруфы: 1 скриншот + 2 фото
        $this->assertSame(3, $activation->proofs()->count());
        $this->assertSame(1, $activation->proofs()->where('type', 'screenshot')->count());
        $this->assertSame(2, $activation->proofs()->where('type', 'photo')->count());

        // Файлы реально сохранены на private-диск
        Storage::disk('local')->assertExists($activation->adif_path);
        $activation->proofs->each(
            fn ($proof) => Storage::disk('local')->assertExists($proof->path)
        );
    }

    public function test_rejects_duplicate_activation_same_park_callsign_date(): void
    {
        $this->post(route('activations.store'), $this->validPayload());
        $this->assertSame(1, Activation::count());

        $response = $this->post(route('activations.store'), $this->validPayload());

        $response->assertSessionHasErrors('adif');
        $this->assertSame(1, Activation::count()); // второй не создался
    }

    public function test_rejects_non_adif_file(): void
    {
        $payload = $this->validPayload([
            'adif' => UploadedFile::fake()->createWithContent('log.adi', 'это вообще не лог'),
        ]);

        $response = $this->post(route('activations.store'), $payload);

        $response->assertSessionHasErrors('adif');
        $this->assertSame(0, Activation::count());
    }

    public function test_screenshot_is_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['screenshot']);

        $response = $this->post(route('activations.store'), $payload);

        $response->assertSessionHasErrors('screenshot');
        $this->assertSame(0, Activation::count());
    }

    public function test_proof_files_are_not_public(): void
    {
        $this->post(route('activations.store'), $this->validPayload());
        $proof = Activation::first()->proofs()->first();

        // Без авторизации пруф не отдаётся (редирект на логин)
        $this->get(route('proofs.show', $proof))->assertRedirect();
    }
}
