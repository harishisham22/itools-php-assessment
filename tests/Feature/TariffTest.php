<?php

namespace Tests\Feature;

use App\BuildingType;
use App\Models\Tariff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TariffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_tariffs_index_is_publicly_accessible()
    {
        Tariff::create($this->tariffAttributes());

        $response = $this->get(route('tariffs.index'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tariffs/Index')
                ->has('tariffs.data', 1)
                ->where('tariffs.data.0.category', 'For the first 100 kWh')
                ->where('tariffs.total', 1)
                ->where('filters.building_type', null)
                ->has('buildingTypes', count(BuildingType::cases())));
    }

    public function test_tariffs_index_can_be_filtered_by_building_type()
    {
        Tariff::create($this->tariffAttributes());

        Tariff::create($this->tariffAttributes([
            'building_type' => BuildingType::COMMERCIAL->value,
            'category' => 'Commercial first tier',
        ]));

        $response = $this->get(route('tariffs.index', [
            'building_type' => BuildingType::COMMERCIAL->value,
        ]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tariffs/Index')
                ->has('tariffs.data', 1)
                ->where('tariffs.data.0.category', 'Commercial first tier')
                ->where('filters.building_type', BuildingType::COMMERCIAL->value));
    }

    public function test_create_tariff_page_can_be_rendered()
    {
        $response = $this->get(route('tariffs.create'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tariffs/Create')
                ->has('buildingTypes', count(BuildingType::cases())));
    }

    public function test_edit_tariff_page_can_be_rendered()
    {
        $tariff = Tariff::create($this->tariffAttributes());

        $response = $this->get(route('tariffs.edit', $tariff));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tariffs/Edit')
                ->where('tariff.id', $tariff->id)
                ->where('tariff.category', 'For the first 100 kWh')
                ->has('buildingTypes', count(BuildingType::cases())));
    }

    public function test_a_tariff_can_be_created()
    {
        $response = $this->post(route('tariffs.store'), $this->tariffAttributes());

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('tariffs.index'));

        $tariff = Tariff::firstOrFail();

        $this->assertSame('For the first 100 kWh', $tariff->category);
        $this->assertEqualsWithDelta(0.5, $tariff->rate, 0.001);
        $this->assertTrue($tariff->is_initial_rate);
    }

    public function test_a_tariff_can_be_updated_without_overlapping_itself()
    {
        $tariff = Tariff::create($this->tariffAttributes());

        $response = $this->put(route('tariffs.update', $tariff), $this->tariffAttributes([
            'category' => 'Updated first tier',
            'rate' => 0.75,
        ]));

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('tariffs.index'));

        $tariff->refresh();

        $this->assertSame('Updated first tier', $tariff->category);
        $this->assertEqualsWithDelta(0.75, $tariff->rate, 0.001);
    }

    public function test_a_tariff_overlapping_another_tier_is_rejected()
    {
        Tariff::create($this->tariffAttributes());

        $response = $this->post(route('tariffs.store'), $this->tariffAttributes([
            'category' => 'Overlapping tier',
            'min_kwh' => 50,
            'max_kwh' => 150,
        ]));

        $response->assertSessionHasErrors('min_kwh');

        $this->assertDatabaseCount('tariffs', 1);
    }

    public function test_tariffs_index_is_paginated()
    {
        foreach (range(1, 12) as $index) {
            Tariff::create($this->tariffAttributes([
                'category' => "Tier {$index}",
                'min_kwh' => $index * 10,
                'max_kwh' => $index * 10 + 9,
                'is_initial_rate' => $index === 1,
            ]));
        }

        $firstPage = $this->get(route('tariffs.index'));

        $firstPage
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tariffs/Index')
                ->has('tariffs.data', 10)
                ->where('tariffs.current_page', 1)
                ->where('tariffs.last_page', 2)
                ->where('tariffs.per_page', 10)
                ->where('tariffs.total', 12));

        $secondPage = $this->get(route('tariffs.index', ['page' => 2]));

        $secondPage
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tariffs/Index')
                ->has('tariffs.data', 2)
                ->where('tariffs.current_page', 2));
    }

    public function test_a_tariff_can_be_deleted()
    {
        $tariff = Tariff::create($this->tariffAttributes());

        $response = $this->delete(route('tariffs.destroy', $tariff));

        $response->assertRedirect(route('tariffs.index'));

        $this->assertDatabaseMissing('tariffs', ['id' => $tariff->id]);
    }

    public function test_tariffs_index_api_returns_json()
    {
        Tariff::create($this->tariffAttributes());

        $response = $this->getJson(route('api.tariffs.index'));

        $response
            ->assertOk()
            ->assertJsonPath('data.0.category', 'For the first 100 kWh')
            ->assertJsonPath('data.0.is_initial_rate', true);
    }

    /**
     * Get a valid set of tariff attributes.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function tariffAttributes(array $overrides = []): array
    {
        return array_merge([
            'building_type' => BuildingType::RESIDENTIAL->value,
            'category' => 'For the first 100 kWh',
            'min_kwh' => 1,
            'max_kwh' => 100,
            'rate' => 0.5,
            'is_initial_rate' => true,
        ], $overrides);
    }
}
