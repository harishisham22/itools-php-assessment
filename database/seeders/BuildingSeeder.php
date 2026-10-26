<?php

namespace Database\Seeders;

use App\BuildingType;
use App\Models\Building;
use App\Month;
use App\Service\TariffCalculator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = [
            [
                'customer_name' => "John",
                'month' => Month::JUNE->number(),
                'type' => BuildingType::COMMERCIAL,
                'usability' => 210,
                'bill' => TariffCalculator::getBuildingBill(BuildingType::COMMERCIAL->value, 210)
            ],
            [
                'customer_name' => "Ahmad",
                'month' => Month::OCTOBER->number(),
                'type' => BuildingType::COMMERCIAL,
                'usability' => 100,
                'bill' => TariffCalculator::getBuildingBill(BuildingType::COMMERCIAL->value, 100)
            ],
            [
                'customer_name' => "Mary",
                'month' => Month::JANUARY->number(),
                'type' => BuildingType::COMMERCIAL,
                'usability' => 150,
                'bill' => TariffCalculator::getBuildingBill(BuildingType::COMMERCIAL->value, 150)
            ],
        ];

        DB::transaction(function () use ($buildings) {
            collect($buildings)->each(function ($building) {
                Building::firstOrCreate([
                    'customer_name' => $building['customer_name'],
                    'month' => $building['month'],
                    'type' => $building['type'],
                ], $building);
            });
        });
    }
}