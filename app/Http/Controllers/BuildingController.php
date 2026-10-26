<?php

namespace App\Http\Controllers;

use App\BuildingType;
use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use App\Month;
use App\Service\TariffCalculator;
use App\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BuildingController extends Controller
{
    /**
     * Display a listing of the buildings.
     */
    public function index(Request $request): AnonymousResourceCollection|InertiaResponse
    {
        $type = $request->query('type');
        $state = $request->query('state');
        $sortBy = $request->query('sort_by');
        $direction = $request->query('direction', 'asc');

        $filters = [
            'type' => is_string($type) && $type !== '' ? $type : null,
            'state' => is_string($state) && $state !== '' ? $state : null,
            'sort_by' => is_string($sortBy) && $sortBy !== '' ? $sortBy : null,
            'direction' => is_string($direction) && strtolower($direction) === 'desc' ? 'desc' : 'asc',
        ];

        $buildings = Building::query()
            ->ofType($filters['type'])
            ->ofState($filters['state'])
            ->sortBy($filters['sort_by'], $filters['direction'])
            ->orderBy('id')
            ->get();

        if ($this->isApiRequest($request)) {
            return BuildingResource::collection($buildings);
        }

        return Inertia::render('buildings/Index', [
            'buildings' => BuildingResource::collection($buildings)->resolve($request),
            'filters' => $filters,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Show the form for creating a new building.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('buildings/Create', $this->formOptions());
    }

    /**
     * Store a newly created building in storage.
     */
    public function store(StoreBuildingRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $validated['bill'] = TariffCalculator::getBuildingBill(
            $validated['type'],
            (float) $validated['usability'],
        );

        $building = DB::transaction(fn (): Building => Building::create($validated));

        if ($this->isApiRequest($request)) {
            return response()->json([
                'data' => BuildingResource::make($building),
                'message' => 'Building created successfully.',
            ], 201);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Building created successfully.',
        ]);

        return to_route('buildings.index');
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
    public function edit(Request $request, Building $building): BuildingResource|InertiaResponse
    {
        if ($this->isApiRequest($request)) {
            return BuildingResource::make($building);
        }

        return Inertia::render('buildings/Edit', [
            'building' => BuildingResource::make($building)->resolve($request),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update a building in storage.
     */
    public function update(UpdateBuildingRequest $request, Building $building): BuildingResource|RedirectResponse
    {
        $validated = $request->validated();
        $validated['bill'] = TariffCalculator::getBuildingBill(
            $validated['type'],
            (float) $validated['usability'],
        );

        DB::transaction(fn () => $building->update($validated));

        if ($this->isApiRequest($request)) {
            return BuildingResource::make($building)->additional([
                'message' => 'Building updated successfully.',
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Building updated successfully.',
        ]);

        return to_route('buildings.index');
    }

    /**
     * Remove a building from storage.
     */
    public function destroy(Request $request, Building $building): Response|RedirectResponse
    {
        DB::transaction(fn () => $building->delete());

        if ($this->isApiRequest($request)) {
            return response()->noContent();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Building deleted successfully.',
        ]);

        return to_route('buildings.index');
    }

    /**
     * Determine whether the current request belongs to the API routes.
     */
    private function isApiRequest(Request $request): bool
    {
        return $request->routeIs('api.*');
    }

    /**
     * Get the options shared by the create and edit forms.
     *
     * @return array{
     *     types: array<int, array{value: string, label: string}>,
     *     states: array<int, array{value: string, label: string}>,
     *     months: array<int, array{value: int, label: string}>
     * }
     */
    private function formOptions(): array
    {
        return [
            'types' => array_map(fn (BuildingType $type): array => [
                'value' => $type->value,
                'label' => Str::headline($type->value),
            ], BuildingType::cases()),
            'states' => array_map(fn (State $state): array => [
                'value' => $state->value,
                'label' => Str::headline($state->value),
            ], State::cases()),
            'months' => array_map(fn (Month $month): array => [
                'value' => $month->number(),
                'label' => Str::headline($month->value),
            ], Month::cases()),
        ];
    }
}
