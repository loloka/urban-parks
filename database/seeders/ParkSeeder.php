<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Park;

class ParkSeeder extends Seeder
{
    public function run(): void
    {
        // Минимальный набор для теста
        $parks = [
            [
                'country_code' => 'RU',
                'region_code' => 'NSK',
                'name' => 'Центральный парк',
                'name_en' => 'Central Park',
                'description' => 'Центральный парк Новосибирска',
                'description_en' => 'Central Park of Novosibirsk',
                'city' => 'Новосибирск',
                'region' => 'Новосибирская область',
                'latitude' => 55.0296,
                'longitude' => 82.9145,
                'area' => '10.5 га',
                'status' => 'active',
            ],
            [
                'country_code' => 'RU',
                'region_code' => 'NSK',
                'name' => 'Заельцовский парк',
                'name_en' => 'Zaeltsovsky Park',
                'description' => 'Один из крупнейших парков города',
                'description_en' => 'One of the largest parks',
                'city' => 'Новосибирск',
                'region' => 'Новосибирская область',
                'latitude' => 54.9927,
                'longitude' => 82.8650,
                'area' => '200 га',
                'status' => 'active',
            ],
            [
                'country_code' => 'RU',
                'region_code' => 'NSK',
                'name' => 'Парк Березовая роща',
                'name_en' => 'Birch Grove Park',
                'description' => 'Парк в Академгородке',
                'description_en' => 'Park in Akademgorodok',
                'city' => 'Новосибирск',
                'region' => 'Новосибирская область',
                'latitude' => 54.8406,
                'longitude' => 83.0947,
                'area' => '68 га',
                'status' => 'active',
            ],
        ];

        foreach ($parks as $parkData) {
            Park::create($parkData);
        }
    }
}
