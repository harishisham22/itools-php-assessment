<?php

namespace App\Service;

use App\Models\Tariff;

class TariffCalculator
{
    /**
     * Get building bill using progressive tariff rate.
     *
     * @param string $type
     * @param float $usability
     * @return float
     */
    public static function getBuildingBill(string $type, float $usability = 0): float
    {
        if ($usability <= 0) {
            return 0.0;
        }

        $tariffs = Tariff::where('building_type', $type)
            ->orderBy('is_initial_rate', 'desc')
            ->orderBy('min_kwh', 'asc')
            ->get();

        $totalBill = 0.0;
        $remainingUsability = $usability;

        foreach ($tariffs as $tariff) {
            if ($remainingUsability <= 0) {
                break;
            }

            if ($tariff->max_kwh === null) {
                $kwhInTier = $remainingUsability;
            } else {
                $tierCapacity = ($tariff->max_kwh - $tariff->min_kwh) + 1;

                $kwhInTier = min($remainingUsability, $tierCapacity);
            }

            $totalBill += $kwhInTier * $tariff->rate;
            $remainingUsability -= $kwhInTier;
        }

        return $totalBill;
    }
}