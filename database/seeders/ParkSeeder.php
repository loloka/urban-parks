<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Park;

/**
 * Реальные парки Новосибирска для программы UPTA (боевые данные).
 *
 * Данные выгружены из рабочей базы после ручной правки координат/описаний
 * в админке; референсы перенумерованы по порядку UP-RU-NSK-0001..0007.
 *
 * Идемпотентно: updateOrCreate по (country_code, region_code, name) —
 * повторный запуск не плодит дубли, обновляет поля и выставляет reference.
 * Связи с активациями идут по park_id, смена reference их не рвёт.
 */
class ParkSeeder extends Seeder
{
    public function run(): void
    {
        $parks = [
            [
                'reference' => 'UP-RU-NSK-0001',
                'name' => "Центральный парк",
                'name_en' => "Central Park",
                'description' => "Центральный парк культуры и отдыха в историческом центре Новосибирска, рядом с театром оперы и балета.",
                'description_en' => "Central park of culture and leisure in the historic centre of Novosibirsk, next to the Opera and Ballet Theatre.",
                'city' => "Новосибирск",
                'region' => "Новосибирская область",
                'latitude' => 55.0352571,
                'longitude' => 82.9249269,
                'area' => "10 га",
            ],
            [
                'reference' => 'UP-RU-NSK-0002',
                'name' => "Заельцовский парк",
                'name_en' => "Zaeltsovsky Park",
                'description' => "Крупнейший парк культуры и отдыха города на севере, в сосновом бору рядом с зоопарком. \nЛесной массив, бор, расположенный в Заельцовском районе Новосибирска.\n\nНаходится близ побережья реки Обь",
                'description_en' => "The largest park of culture and leisure in the north of the city, in a pine forest next to the zoo and the Ob river.",
                'city' => "Новосибирск",
                'region' => "Новосибирская область",
                'latitude' => 55.0600891,
                'longitude' => 82.8633151,
                'area' => "270 га",
            ],
            [
                'reference' => 'UP-RU-NSK-0003',
                'name' => "Парк Березовая роща",
                'name_en' => "Birch Grove Park",
                'description' => "Парк в Дзержинском районе у одноимённой станции метро «Берёзовая роща».",
                'description_en' => "A park in the Dzerzhinsky district next to the \"Beryozovaya Roshcha\" metro station.",
                'city' => "Новосибирск",
                'region' => "Новосибирская область",
                'latitude' => 55.047276,
                'longitude' => 82.9518655,
                'area' => "30 га",
            ],
            [
                'reference' => 'UP-RU-NSK-0004',
                'name' => "Бугринская роща",
                'name_en' => "Bugrinskaya Grove",
                'description' => "Лесопарк на левом берегу Оби у Бугринского моста, популярное место отдыха с пляжем.",
                'description_en' => "A forest park on the left bank of the Ob near the Bugrinsky bridge, a popular recreation spot with a beach.",
                'city' => "Новосибирск",
                'region' => "Новосибирская область",
                'latitude' => 54.9733697,
                'longitude' => 82.9457492,
                'area' => "30 га",
            ],
            [
                'reference' => 'UP-RU-NSK-0005',
                'name' => "Дендрологический парк",
                'name_en' => "Dendrological Park",
                'description' => "Дендрологический парк с коллекцией древесных растений на севере города.",
                'description_en' => "A dendrological park with a collection of woody plants in the north of the city.",
                'city' => "Новосибирск",
                'region' => "Новосибирская область",
                'latitude' => 55.0612113,
                'longitude' => 82.8823534,
                'area' => "166 га",
            ],
            [
                'reference' => 'UP-RU-NSK-0006',
                'name' => "Центральный сибирский ботанический сад",
                'name_en' => "Central Siberian Botanical Garden",
                'description' => "Крупнейший ботанический сад за Уралом (ЦСБС СО РАН) в Академгородке.",
                'description_en' => "The largest botanical garden beyond the Urals (CSBG SB RAS) in Akademgorodok.",
                'city' => "Новосибирск",
                'region' => "Новосибирская область",
                'latitude' => 54.8243907,
                'longitude' => 83.1090482,
                'area' => "1000 га",
            ],
            [
                'reference' => 'UP-RU-NSK-0007',
                'name' => "Парк «Сосновый бор»",
                'name_en' => "Sosnovy Bor Park",
                'description' => "Парк культуры и отдыха в сосновом бору на севере города (Калининский район).",
                'description_en' => "A park of culture and leisure in a pine forest in the north of the city (Kalininsky district).",
                'city' => "Новосибирск",
                'region' => "Новосибирская область",
                'latitude' => 55.081254,
                'longitude' => 82.9456745,
                'area' => "30 га",
            ],
        ];

        foreach ($parks as $data) {
            Park::updateOrCreate(
                [
                    'country_code' => 'RU',
                    'region_code' => 'NSK',
                    'name' => $data['name'],
                ],
                array_merge($data, [
                    'country_code' => 'RU',
                    'region_code' => 'NSK',
                    'status' => 'active',
                ]),
            );
        }
    }
}
