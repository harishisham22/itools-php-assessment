<?php

namespace App\Http\Controllers;

use App\BuildingType;
use App\Http\Resources\ReportResource;
use App\Month;
use App\Service\ReportBuilder;
use App\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ReportController extends Controller
{
    /**
     * Display bill reports for the current filters.
     */
    public function index(Request $request): InertiaResponse
    {
        $filters = $this->filters($request);

        return Inertia::render('reports/Index', [
            'report' => ReportBuilder::build($filters),
            'filters' => $filters,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Get the full report payload as JSON.
     */
    public function summary(Request $request): JsonResponse
    {
        $filters = $this->filters($request);

        return response()->json([
            'data' => [
                'filters' => $filters,
                ...ReportBuilder::build($filters),
            ],
        ]);
    }

    /**
     * Get summary of bills by month.
     */
    public function byMonth(Request $request): AnonymousResourceCollection
    {
        return ReportResource::collection(
            ReportBuilder::monthly($this->filters($request)),
        );
    }

    /**
     * Get summary of bills by building type.
     */
    public function byBuildingType(Request $request): AnonymousResourceCollection
    {
        return ReportResource::collection(
            ReportBuilder::byType($this->filters($request)),
        );
    }

    /**
     * Get summary of bills by state.
     */
    public function byState(Request $request): AnonymousResourceCollection
    {
        return ReportResource::collection(
            ReportBuilder::byState($this->filters($request)),
        );
    }

    /**
     * Normalize and validate the report filters.
     *
     * @return array{type: ?string, state: ?string, month: ?int}
     */
    private function filters(Request $request): array
    {
        $type = $request->query('type');
        $state = $request->query('state');
        $month = filter_var($request->query('month'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 12],
        ]);

        $types = array_map(fn (BuildingType $case): string => $case->value, BuildingType::cases());
        $states = array_map(fn (State $case): string => $case->value, State::cases());

        return [
            'type' => is_string($type) && in_array($type, $types, true) ? $type : null,
            'state' => is_string($state) && in_array($state, $states, true) ? $state : null,
            'month' => $month === false ? null : $month,
        ];
    }

    /**
     * Get the options shared by the report filters.
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
