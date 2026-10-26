<?php

namespace App\Http\Controllers;

use App\BuildingType;
use App\Http\Resources\ReportResource;
use App\Models\Building;
use App\Month;
use App\State;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportController extends Controller
{
    /**
     * Get summary of bills by month.
     * 
     * @return AnonymousResourceCollection
     */
    public function byMonth(): AnonymousResourceCollection
    {
        $bills = Building::query()
            ->selectRaw("month, SUM(bill) as total_bill")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $mappedBills = collect(Month::cases())->map(function ($month) use ($bills) {
            return [
                'label' => $month->value,
                'value' => $bills->firstWhere('month', $month->number()) ?? 0
            ];
        });

        return ReportResource::collection($mappedBills);
    }

    /**
     * Get summary of bills by building type.
     * 
     * @return AnonymousResourceCollection
     */
    public function byBuildingType()
    {
        $bills = Building::query()
            ->selectRaw("type, SUM(bill) as total_bill")
            ->groupBy('type')
            ->orderBy('type')
            ->get();

        $mappedBills = collect(BuildingType::cases())->map(function ($type) use ($bills) {
            return [
                'label' => $type->value,
                'value' => $bills->firstWhere('type', $type->value) ?? 0
            ];
        });

        return ReportResource::collection($mappedBills);
    }

    /**
     * Get summary of bills by state.
     * 
     * @return AnonymousResourceCollection
     */
    public function byState()
    {
        $bills = Building::query()
            ->selectRaw("state, SUM(bill) as total_bill")
            ->groupBy('state')
            ->orderBy('state')
            ->get();

        $mappedBills = collect(State::cases())->map(function ($state) use ($bills) {
            return [
                'label' => $state->value,
                'value' => $bills->firstWhere('state', $state->value) ?? 0
            ];
        });

        return ReportResource::collection($mappedBills);
    }
}
