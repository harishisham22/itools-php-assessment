<?php

namespace App\Http\Controllers;

use App\Http\Resources\BuildingResource;
use App\Models\Building;
use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Customer;
use App\Service\TariffCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BuildingController extends Controller
{
    /**
     * Display a listing of the buildings.
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Building::query();

        $query->when($request->filled('type'), function ($q) use ($request) {
            $q->where('type', $request->input('type'));
        });

        $query->when($request->filled('state'), function ($q) use ($request) {
            $q->where('state', $request->input('state'));
        });

        $sortBy = $request->input('sort_by');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, ['type', 'usability'])) {
            $query->orderBy($sortBy, $direction);
        } else {
            $query->latest();
        }

        return BuildingResource::collection($query->get());
    }

    /**
     * Show the form for creating a new building.
     */
    public function create(StoreBuildingRequest $request)
    {
        //TODO: create building form
    }

    /**
     * Store a newly created building in storage.
     */
    public function store(StoreBuildingRequest $request): JsonResponse
    {
        DB::beginTransaction();
        $building = Building::create($request->merge([
            'bill' => TariffCalculator::getBuildingBill($request->input('type'), $request->input('usability'))
        ])->toArray());
        DB::commit();

        return response()->json([
            'data' => BuildingResource::make($building),
            'message' => 'Building created successfully.'
        ], 201);
    }

    /**
     * Display a building.
     */
    public function show(Building $building): BuildingResource
    {
        return BuildingResource::make($building);
    }

    /**
     * Show the form for editing a building.
     */
    public function edit(Building $building)
    {
        //TODO: edit building form
    }

    /**
     * Update the a building in storage.
     */
    public function update(UpdateBuildingRequest $request, Building $building): BuildingResource
    {
        DB::beginTransaction();

        $building->update($request->all());

        DB::commit();

        return BuildingResource::make($building)->additional([
            'message' => 'Building updated successfully.'
        ]);
    }

    /**
     * Remove a building from storage.
     */
    public function destroy(Building $building): Response
    {
        DB::beginTransaction();

        $building->delete();

        DB::commit();

        return response()->noContent();
    }
}
