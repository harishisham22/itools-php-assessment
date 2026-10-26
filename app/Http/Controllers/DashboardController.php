<?php

namespace App\Http\Controllers;

use App\Http\Resources\BuildingResource;
use App\Models\Building;
use App\Models\Tariff;
use App\Service\ReportBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with a billing overview.
     */
    public function __invoke(Request $request): InertiaResponse
    {
        $report = ReportBuilder::build();

        $recentBuildings = Building::query()
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'summary' => [
                ...$report['summary'],
                'customers' => Building::query()->distinct()->count('customer_name'),
                'tariffs' => Tariff::count(),
            ],
            'byMonth' => $report['by_month'],
            'byType' => $report['by_type'],
            'recentBuildings' => BuildingResource::collection($recentBuildings)->resolve($request),
        ]);
    }
}
