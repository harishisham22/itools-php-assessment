<?php

namespace App\Http\Controllers;

use App\BuildingType;
use App\Http\Resources\ReportResource;
use App\Models\Building;
use App\Month;
use App\State;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
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
            'report' => $this->buildReport($filters),
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
                ...$this->buildReport($filters),
            ],
        ]);
    }

    /**
     * Get summary of bills by month.
     */
    public function byMonth(Request $request): AnonymousResourceCollection
    {
        return ReportResource::collection(
            $this->monthlyRows($this->filteredQuery($this->filters($request))),
        );
    }

    /**
     * Get summary of bills by building type.
     */
    public function byBuildingType(Request $request): AnonymousResourceCollection
    {
        return ReportResource::collection(
            $this->typeRows($this->filteredQuery($this->filters($request))),
        );
    }

    /**
     * Get summary of bills by state.
     */
    public function byState(Request $request): AnonymousResourceCollection
    {
        return ReportResource::collection(
            $this->stateRows($this->filteredQuery($this->filters($request))),
        );
    }

    /**
     * Build the report payload for the given filters.
     *
     * @param  array{type: ?string, state: ?string, month: ?int}  $filters
     * @return array{
     *     summary: array{buildings: int, usability: float, bill: float, average_bill: float},
     *     by_month: array<int, array{month: int, label: string, buildings: int, usability: float, bill: float}>,
     *     by_type: array<int, array{label: string, buildings: int, usability: float, bill: float}>,
     *     by_state: array<int, array{label: string, buildings: int, usability: float, bill: float}>
     * }
     */
    private function buildReport(array $filters): array
    {
        $query = $this->filteredQuery($filters);
        $totals = $this->totals($query);

        return [
            'summary' => [
                ...$totals,
                'average_bill' => $totals['buildings'] > 0
                    ? round($totals['bill'] / $totals['buildings'], 2)
                    : 0.0,
            ],
            'by_month' => $this->monthlyRows($query),
            'by_type' => $this->typeRows($query),
            'by_state' => $this->stateRows($query),
        ];
    }

    /**
     * Apply the report filters to a building query.
     *
     * @param  array{type: ?string, state: ?string, month: ?int}  $filters
     * @return Builder<Building>
     */
    private function filteredQuery(array $filters): Builder
    {
        return Building::query()
            ->ofType($filters['type'])
            ->ofState($filters['state'])
            ->ofMonth($filters['month']);
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
     * Get the totals for a filtered building query.
     *
     * @param  Builder<Building>  $query
     * @return array{buildings: int, usability: float, bill: float}
     */
    private function totals(Builder $query): array
    {
        $row = (clone $query)->toBase()
            ->selectRaw('COUNT(*) as buildings, COALESCE(SUM(usability), 0) as usability, COALESCE(SUM(bill), 0) as bill')
            ->first();

        return [
            'buildings' => (int) ($row->buildings ?? 0),
            'usability' => round((float) ($row->usability ?? 0), 2),
            'bill' => round((float) ($row->bill ?? 0), 2),
        ];
    }

    /**
     * Get bill totals for every month, including months without buildings.
     *
     * @param  Builder<Building>  $query
     * @return array<int, array{month: int, label: string, buildings: int, usability: float, bill: float}>
     */
    private function monthlyRows(Builder $query): array
    {
        $rows = (clone $query)->toBase()
            ->selectRaw('month, COUNT(*) as buildings, COALESCE(SUM(usability), 0) as usability, COALESCE(SUM(bill), 0) as bill')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        return array_map(function (Month $month) use ($rows): array {
            $row = $rows->get($month->number());

            return [
                'month' => $month->number(),
                'label' => Str::headline($month->value),
                'buildings' => (int) ($row->buildings ?? 0),
                'usability' => round((float) ($row->usability ?? 0), 2),
                'bill' => round((float) ($row->bill ?? 0), 2),
            ];
        }, Month::cases());
    }

    /**
     * Get bill totals for every building type, sorted by bill.
     *
     * @param  Builder<Building>  $query
     * @return array<int, array{label: string, buildings: int, usability: float, bill: float}>
     */
    private function typeRows(Builder $query): array
    {
        $rows = (clone $query)->toBase()
            ->selectRaw('type, COUNT(*) as buildings, COALESCE(SUM(usability), 0) as usability, COALESCE(SUM(bill), 0) as bill')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return $this->formatGroupedRows(BuildingType::cases(), $rows);
    }

    /**
     * Get bill totals for every state, sorted by bill.
     *
     * @param  Builder<Building>  $query
     * @return array<int, array{label: string, buildings: int, usability: float, bill: float}>
     */
    private function stateRows(Builder $query): array
    {
        $rows = (clone $query)->toBase()
            ->selectRaw('state, COUNT(*) as buildings, COALESCE(SUM(usability), 0) as usability, COALESCE(SUM(bill), 0) as bill')
            ->groupBy('state')
            ->get()
            ->keyBy('state');

        return $this->formatGroupedRows(State::cases(), $rows);
    }

    /**
     * Format grouped bill totals for the given enum cases.
     *
     * @param  array<int, BuildingType|State>  $cases
     * @param  Collection<int|string, \stdClass>  $rows
     * @return array<int, array{label: string, buildings: int, usability: float, bill: float}>
     */
    private function formatGroupedRows(array $cases, Collection $rows): array
    {
        $grouped = array_map(function (BuildingType|State $case) use ($rows): array {
            $row = $rows->get($case->value);

            return [
                'label' => Str::headline($case->value),
                'buildings' => (int) ($row->buildings ?? 0),
                'usability' => round((float) ($row->usability ?? 0), 2),
                'bill' => round((float) ($row->bill ?? 0), 2),
            ];
        }, $cases);

        usort($grouped, fn (array $a, array $b): int => $b['bill'] <=> $a['bill']);

        return $grouped;
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
