<?php

namespace Tests\Feature;

use App\BuildingType;
use App\Models\Building;
use App\Models\Tariff;
use App\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BuildingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_buildings_index_is_publicly_accessible()
    {
        $response = $this->get(route('buildings.index'));

        $response->assertOk();
    }

    public function test_buildings_index_can_be_rendered()
    {
        $this->seedTariffs();

        Building::create($this->buildingAttributes(['bill' => 100]));

        $response = $this->get(route('buildings.index'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('buildings/Index')
                ->has('buildings', 1)
                ->where('buildings.0.customer_name', 'Ahmad')
                ->where('buildings.0.bill', fn ($bill): bool => abs((float) $bill - 100) < 0.001)
                ->where('filters.type', null)
                ->has('types', count(BuildingType::cases()))
                ->has('states', count(State::cases()))
                ->has('months', 12));
    }

    public function test_buildings_index_can_be_filtered_by_month()
    {
        Building::create($this->buildingAttributes([
            'month' => 6,
            'customer_name' => 'June building',
        ]));

        Building::create($this->buildingAttributes([
            'month' => 7,
            'customer_name' => 'July building',
        ]));

        $response = $this->get(route('buildings.index', ['month' => 7]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('buildings/Index')
                ->has('buildings', 1)
                ->where('buildings.0.customer_name', 'July building')
                ->where('filters.month', 7));
    }

    public function test_create_building_page_can_be_rendered()
    {
        $response = $this->get(route('buildings.create'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('buildings/Create')
                ->has('types')
                ->has('states')
                ->has('months', 12));
    }

    public function test_edit_building_page_can_be_rendered()
    {
        $building = Building::create($this->buildingAttributes());

        $response = $this->get(route('buildings.edit', $building));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('buildings/Edit')
                ->where('building.id', $building->id)
                ->where('building.customer_name', 'Ahmad'));
    }

    public function test_a_building_can_be_created_with_a_calculated_bill()
    {
        $this->seedTariffs();

        $response = $this->post(route('buildings.store'), [
            'customer_name' => 'Ahmad',
            'type' => BuildingType::RESIDENTIAL->value,
            'month' => 6,
            'usability' => 150,
            'state' => State::SELANGOR->value,
        ]);

        $response->assertRedirect(route('buildings.index'));

        $building = Building::firstOrFail();

        $this->assertSame('Ahmad', $building->customer_name);
        $this->assertEqualsWithDelta(100, $building->bill, 0.001);
    }

    public function test_a_building_can_be_updated_with_a_recalculated_bill()
    {
        $this->seedTariffs();

        $building = Building::create($this->buildingAttributes([
            'usability' => 100,
            'bill' => 0,
        ]));

        $response = $this->put(route('buildings.update', $building), [
            'customer_name' => 'Ahmad Updated',
            'type' => BuildingType::RESIDENTIAL->value,
            'month' => 7,
            'usability' => 150,
            'state' => State::SELANGOR->value,
        ]);

        $response->assertRedirect(route('buildings.index'));

        $building->refresh();

        $this->assertSame('Ahmad Updated', $building->customer_name);
        $this->assertEqualsWithDelta(100, $building->bill, 0.001);
    }

    public function test_a_building_can_be_deleted()
    {
        $building = Building::create($this->buildingAttributes());

        $response = $this->delete(route('buildings.destroy', $building));

        $response->assertRedirect(route('buildings.index'));

        $this->assertSoftDeleted($building);
    }

    /**
     * Seed the tariff tiers used by the progressive calculator.
     */
    private function seedTariffs(): void
    {
        Tariff::create([
            'building_type' => BuildingType::RESIDENTIAL->value,
            'category' => 'For the first 100 kWh',
            'min_kwh' => 1,
            'max_kwh' => 100,
            'rate' => 0.5,
            'is_initial_rate' => true,
        ]);

        Tariff::create([
            'building_type' => BuildingType::RESIDENTIAL->value,
            'category' => 'For the next kWh',
            'min_kwh' => 101,
            'max_kwh' => null,
            'rate' => 1.0,
            'is_initial_rate' => false,
        ]);
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
            'usability' => 150,
            'state' => State::SELANGOR->value,
            'bill' => 0,
        ], $overrides);
    }
}
