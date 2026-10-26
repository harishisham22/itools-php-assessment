<?php

namespace Tests\Feature;

use App\BuildingType;
use App\Models\Building;
use App\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_reports_page_is_publicly_accessible()
    {
        $response = $this->get(route('reports.index'));

        $response->assertOk();
    }

    public function test_reports_page_shows_bill_totals()
    {
        $this->seedBuildings();

        $response = $this->get(route('reports.index'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('reports/Index')
                ->where('report.summary.buildings', 3)
                ->where('report.summary.bill', fn ($bill): bool => abs((float) $bill - 400) < 0.001)
                ->where('report.summary.usability', fn ($usability): bool => abs((float) $usability - 600) < 0.001)
                ->where('report.summary.average_bill', fn ($bill): bool => abs((float) $bill - 133.33) < 0.001)
                ->has('report.by_month', 12)
                ->where('report.by_month.0.label', 'January')
                ->where('report.by_month.0.bill', 0)
                ->where('report.by_month.5.label', 'June')
                ->where('report.by_month.5.bill', fn ($bill): bool => abs((float) $bill - 150) < 0.001)
                ->where('report.by_month.9.label', 'October')
                ->where('report.by_month.9.bill', fn ($bill): bool => abs((float) $bill - 250) < 0.001)
                ->has('report.by_type', 2)
                ->where('report.by_type.0.label', 'Commercial')
                ->where('report.by_type.0.bill', fn ($bill): bool => abs((float) $bill - 350) < 0.001)
                ->has('report.by_state', count(State::cases()))
                ->where('report.by_state.0.label', 'Penang')
                ->where('filters.type', null)
                ->has('types', count(BuildingType::cases()))
                ->has('states', count(State::cases()))
                ->has('months', 12));
    }

    public function test_reports_page_shows_zeros_without_buildings()
    {
        $response = $this->get(route('reports.index'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('reports/Index')
                ->where('report.summary.buildings', 0)
                ->where('report.summary.bill', 0)
                ->where('report.summary.average_bill', 0)
                ->has('report.by_month', 12)
                ->where('report.by_month.4.bill', 0)
                ->has('report.by_type', count(BuildingType::cases())));
    }

    public function test_reports_can_be_filtered_by_building_type()
    {
        $this->seedBuildings();

        $response = $this->get(route('reports.index', [
            'type' => BuildingType::COMMERCIAL->value,
        ]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.type', BuildingType::COMMERCIAL->value)
                ->where('report.summary.buildings', 2)
                ->where('report.summary.bill', fn ($bill): bool => abs((float) $bill - 350) < 0.001)
                ->where('report.by_type.0.label', 'Commercial')
                ->where('report.by_type.1.bill', 0));
    }

    public function test_reports_can_be_filtered_by_state()
    {
        $this->seedBuildings();

        $response = $this->get(route('reports.index', [
            'state' => State::SELANGOR->value,
        ]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.state', State::SELANGOR->value)
                ->where('report.summary.buildings', 2)
                ->where('report.summary.bill', fn ($bill): bool => abs((float) $bill - 150) < 0.001));
    }

    public function test_reports_can_be_filtered_by_month()
    {
        $this->seedBuildings();

        $response = $this->get(route('reports.index', ['month' => 10]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.month', 10)
                ->where('report.summary.buildings', 1)
                ->where('report.by_month.5.bill', 0)
                ->where('report.by_month.9.bill', fn ($bill): bool => abs((float) $bill - 250) < 0.001));
    }

    public function test_invalid_report_filters_are_ignored()
    {
        $this->seedBuildings();

        $response = $this->get(route('reports.index', [
            'type' => 'invalid',
            'state' => 'invalid',
            'month' => 13,
        ]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.type', null)
                ->where('filters.state', null)
                ->where('filters.month', null)
                ->where('report.summary.buildings', 3));
    }

    public function test_api_reports_summary_returns_all_aggregates()
    {
        $this->seedBuildings();

        $response = $this->getJson(route('api.reports.index'));

        $response
            ->assertOk()
            ->assertJsonPath('data.filters.type', null)
            ->assertJsonPath('data.summary.buildings', 3)
            ->assertJsonPath('data.summary.average_bill', fn ($bill): bool => abs((float) $bill - 133.33) < 0.001)
            ->assertJsonCount(12, 'data.by_month')
            ->assertJsonCount(2, 'data.by_type')
            ->assertJsonCount(count(State::cases()), 'data.by_state');
    }

    public function test_api_reports_by_month_returns_numeric_totals()
    {
        $this->seedBuildings();

        $response = $this->getJson(route('api.reports.by-month'));

        $response
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('data.0.label', 'January')
            ->assertJsonPath('data.0.value', 0)
            ->assertJsonPath('data.5.label', 'June')
            ->assertJsonPath('data.5.value', fn ($value): bool => abs((float) $value - 150) < 0.001)
            ->assertJsonPath('data.5.buildings', 2)
            ->assertJsonPath('data.9.value', fn ($value): bool => abs((float) $value - 250) < 0.001);
    }

    public function test_api_reports_by_building_type_returns_sorted_totals()
    {
        $this->seedBuildings();

        $response = $this->getJson(route('api.reports.by-building-type'));

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.label', 'Commercial')
            ->assertJsonPath('data.0.value', fn ($value): bool => abs((float) $value - 350) < 0.001)
            ->assertJsonPath('data.0.buildings', 2)
            ->assertJsonPath('data.1.label', 'Residential');
    }

    public function test_api_reports_by_state_returns_sorted_totals()
    {
        $this->seedBuildings();

        $response = $this->getJson(route('api.reports.by-state'));

        $response
            ->assertOk()
            ->assertJsonCount(count(State::cases()), 'data')
            ->assertJsonPath('data.0.label', 'Penang')
            ->assertJsonPath('data.0.value', fn ($value): bool => abs((float) $value - 250) < 0.001)
            ->assertJsonPath('data.1.label', 'Selangor')
            ->assertJsonPath('data.1.value', fn ($value): bool => abs((float) $value - 150) < 0.001);
    }

    /**
     * Seed a small set of buildings spanning two months, types, and states.
     */
    private function seedBuildings(): void
    {
        Building::create($this->buildingAttributes([
            'customer_name' => 'Ahmad',
            'type' => BuildingType::COMMERCIAL->value,
            'month' => 6,
            'usability' => 200,
            'state' => State::SELANGOR->value,
            'bill' => 100,
        ]));

        Building::create($this->buildingAttributes([
            'customer_name' => 'Siti',
            'type' => BuildingType::RESIDENTIAL->value,
            'month' => 6,
            'usability' => 100,
            'state' => State::SELANGOR->value,
            'bill' => 50,
        ]));

        Building::create($this->buildingAttributes([
            'customer_name' => 'John',
            'type' => BuildingType::COMMERCIAL->value,
            'month' => 10,
            'usability' => 300,
            'state' => State::PENANG->value,
            'bill' => 250,
        ]));
    }

    /**
     * Get a valid set of building attributes.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function buildingAttributes(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Ahmad',
            'type' => BuildingType::RESIDENTIAL->value,
            'month' => 6,
            'usability' => 100,
            'state' => State::SELANGOR->value,
            'bill' => 50,
        ], $overrides);
    }
}
