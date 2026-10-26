<?php

namespace Database\Seeders;

use App\BuildingType;
use App\Models\Tariff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TariffSeeder extends Seeder
{
    const TARIFFS = [
        // Residential Buildings Tariffs
        [
            'building_type' => BuildingType::RESIDENTIAL->value,
            'category' => "For the first 200 kWh (1 - 200 kWh)",
            'min_kwh' => 1,
            'max_kwh' => 200,
            'rate' => 0.45,
            'is_initial_rate' => true
        ],
        [
            'building_type' => BuildingType::RESIDENTIAL->value,
            'category' => "For the next kWh (200 kWh onwards)",
            'min_kwh' => 201,
            'max_kwh' => null,
            'rate' => 0.97
        ],

        // Commercial Buildings Tariffs
        [
            'building_type' => BuildingType::COMMERCIAL->value,
            'category' => "For the first 200 kWh (1 - 200 kWh)",
            'min_kwh' => 1,
            'max_kwh' => 200,
            'rate' => 0.89,
            'is_initial_rate' => true
        ],
        [
            'building_type' => BuildingType::COMMERCIAL->value,
            'category' => "For the next kWh (200 kWh onwards)", // I assumed the question is a typo so i changed from 300 onwards to 200 onwards
            'min_kwh' => 201,
            'max_kwh' => null,
            'rate' => 1.13
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            collect(self::TARIFFS)->each(function ($tariff) {
                Tariff::firstOrCreate([
                    'building_type' => $tariff['building_type'],
                    'min_kwh' => $tariff['min_kwh'],
                    'max_kwh' => $tariff['max_kwh'],
                ], $tariff);
            });
        });
    }
}