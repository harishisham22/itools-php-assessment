<?php

namespace App\Http\Controllers;

use App\BuildingType;
use App\Http\Requests\StoreTariffRequest;
use App\Http\Requests\UpdateTariffRequest;
use App\Http\Resources\TariffResource;
use App\Models\Tariff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TariffController extends Controller
{
    /**
     * Display a listing of the tariffs.
     */
    public function index(Request $request): AnonymousResourceCollection|InertiaResponse
    {
        $buildingType = $request->query('building_type');

        $filters = [
            'building_type' => is_string($buildingType) && $buildingType !== '' ? $buildingType : null,
        ];

        $tariffs = Tariff::query()
            ->when(
                $filters['building_type'],
                fn ($query, string $type) => $query->where('building_type', $type),
            )
            ->orderBy('building_type')
            ->orderByDesc('is_initial_rate')
            ->orderBy('min_kwh')
            ->orderBy('id')
            ->get();

        if ($this->isApiRequest($request)) {
            return TariffResource::collection($tariffs);
        }

        return Inertia::render('tariffs/Index', [
            'tariffs' => TariffResource::collection($tariffs)->resolve($request),
            'filters' => $filters,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Show the form for creating a new tariff.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('tariffs/Create', $this->formOptions());
    }

    /**
     * Store a newly created tariff in storage.
     */
    public function store(StoreTariffRequest $request): JsonResponse|RedirectResponse
    {
        $tariff = DB::transaction(fn (): Tariff => Tariff::create($request->validated()));

        if ($this->isApiRequest($request)) {
            return response()->json([
                'data' => TariffResource::make($tariff),
                'message' => 'Tariff created successfully.',
            ], 201);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tariff created successfully.',
        ]);

        return to_route('tariffs.index');
    }

    /**
     * Display a tariff.
     */
    public function show(Tariff $tariff): TariffResource
    {
        return TariffResource::make($tariff);
    }

    /**
     * Show the form for editing a tariff.
     */
    public function edit(Request $request, Tariff $tariff): TariffResource|InertiaResponse
    {
        if ($this->isApiRequest($request)) {
            return TariffResource::make($tariff);
        }

        return Inertia::render('tariffs/Edit', [
            'tariff' => TariffResource::make($tariff)->resolve($request),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update a tariff in storage.
     */
    public function update(UpdateTariffRequest $request, Tariff $tariff): TariffResource|RedirectResponse
    {
        DB::transaction(fn () => $tariff->update($request->validated()));

        if ($this->isApiRequest($request)) {
            return TariffResource::make($tariff)->additional([
                'message' => 'Tariff updated successfully.',
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tariff updated successfully.',
        ]);

        return to_route('tariffs.index');
    }

    /**
     * Remove a tariff from storage.
     */
    public function destroy(Request $request, Tariff $tariff): Response|RedirectResponse
    {
        DB::transaction(fn () => $tariff->delete());

        if ($this->isApiRequest($request)) {
            return response()->noContent();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tariff deleted successfully.',
        ]);

        return to_route('tariffs.index');
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
     *     buildingTypes: array<int, array{value: string, label: string}>
     * }
     */
    private function formOptions(): array
    {
        return [
            'buildingTypes' => array_map(fn (BuildingType $type): array => [
                'value' => $type->value,
                'label' => Str::headline($type->value),
            ], BuildingType::cases()),
        ];
    }
}
