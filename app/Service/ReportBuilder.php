<?php

namespace App\Service;

use App\BuildingType;
use App\Models\Building;
use App\Month;
use App\State;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReportBuilder
{
    /**
     * Build the report payload for the given filters.
     *
     * @param  array{type?: ?string, state?: ?string, month?: ?int}  $filters
     * @return array{
     *     summary: array{buildings: int, usability: float, bill: float, average_bill: float},
     *     by_month: array<int, array{month: int, label: string, buildings: int, usability: float, bill: float}>,
     *     by_type: array<int, array{label: string, buildings: int, usability: float, bill: float}>,
     *     by_state: array<int, array{label: string, buildings: int, usability: float, bill: float}>
     * }
     */
    public static function build(array $filters = []): array
    {
        $query = self::filteredQuery($filters);
        $totals = self::totals($query);

        return [
            'summary' => [
                ...$totals,
                'average_bill' => $totals['buildings'] > 0
                    ? round($totals['bill'] / $totals['buildings'], 2)
                    : 0.0,
            ],
            'by_month' => self::monthlyRows($query),
            'by_type' => self::typeRows($query),
            'by_state' => self::stateRows($query),
        ];
    }

    /**
     * Get bill totals for every month, including months without buildings.
     *
     * @param  array{type?: ?string, state?: ?string, month?: ?int}  $filters
     * @return array<int, array{month: int, label: string, buildings: int, usability: float, bill: float}>
     */
    public static function monthly(array $filters = []): array
    {
        return self::monthlyRows(self::filteredQuery($filters));
    }

    /**
     * Get bill totals for every building type, sorted by bill.
     *
     * @param  array{type?: ?string, state?: ?string, month?: ?int}  $filters
     * @return array<int, array{label: string, buildings: int, usability: float, bill: float}>
     */
    public static function byType(array $filters = []): array
    {
        return self::typeRows(self::filteredQuery($filters));
    }

    /**
     * Get bill totals for every state, sorted by bill.
     *
     * @param  array{type?: ?string, state?: ?string, month?: ?int}  $filters
     * @return array<int, array{label: string, buildings: int, usability: float, bill: float}>
     */
    public static function byState(array $filters = []): array
    {
        return self::stateRows(self::filteredQuery($filters));
    }

    /**
     * Apply the report filters to a building query.
     *
     * @param  array{type?: ?string, state?: ?string, month?: ?int}  $filters
     * @return Builder<Building>
     */
    private static function filteredQuery(array $filters): Builder
    {
        return Building::query()
            ->ofType($filters['type'] ?? null)
            ->ofState($filters['state'] ?? null)
            ->ofMonth($filters['month'] ?? null);
    }

    /**
     * Get the totals for a filtered building query.
     *
     * @param  Builder<Building>  $query
     * @return array{buildings: int, usability: float, bill: float}
     */
    private static function totals(Builder $query): array
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
    private static function monthlyRows(Builder $query): array
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
    private static function typeRows(Builder $query): array
    {
        $rows = (clone $query)->toBase()
            ->selectRaw('type, COUNT(*) as buildings, COALESCE(SUM(usability), 0) as usability, COALESCE(SUM(bill), 0) as bill')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return self::formatGroupedRows(BuildingType::cases(), $rows);
    }

    /**
     * Get bill totals for every state, sorted by bill.
     *
     * @param  Builder<Building>  $query
     * @return array<int, array{label: string, buildings: int, usability: float, bill: float}>
     */
    private static function stateRows(Builder $query): array
    {
        $rows = (clone $query)->toBase()
            ->selectRaw('state, COUNT(*) as buildings, COALESCE(SUM(usability), 0) as usability, COALESCE(SUM(bill), 0) as bill')
            ->groupBy('state')
            ->get()
            ->keyBy('state');

        return self::formatGroupedRows(State::cases(), $rows);
    }

    /**
     * Format grouped bill totals for the given enum cases.
     *
     * @param  array<int, BuildingType|State>  $cases
     * @param  Collection<int|string, \stdClass>  $rows
     * @return array<int, array{label: string, buildings: int, usability: float, bill: float}>
     */
    private static function formatGroupedRows(array $cases, Collection $rows): array
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
}
