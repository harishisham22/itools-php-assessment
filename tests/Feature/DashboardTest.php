<?php

namespace Tests\Feature;

use App\BuildingType;
use App\Models\Building;
use App\Models\Tariff;
use App\Models\User;
use App\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_can_visit_the_dashboard()
    {
        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_shows_a_billing_overview()
    {
        $this->seedOverview();

        $response = $this->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('summary.buildings', 6)
                ->where('summary.customers', 6)
                ->where('summary.tariffs', 3)
                ->where('summary.bill', fn ($bill): bool => abs((float) $bill - 210) < 0.001)
                ->where('summary.usability', fn ($usability): bool => abs((float) $usability - 210) < 0.001)
                ->where('summary.average_bill', fn ($bill): bool => abs((float) $bill - 35) < 0.001)
                ->has('byMonth', 12)
                ->has('byType', count(BuildingType::cases()))
                ->has('recentBuildings', 5)
                ->where('recentBuildings.0.customer_name', 'Customer 6')
                ->where('recentBuildings.4.customer_name', 'Customer 2'));
    }

    public function test_dashboard_shows_empty_states_without_data()
    {
        $response = $this->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('summary.buildings', 0)
                ->where('summary.customers', 0)
                ->where('summary.tariffs', 0)
                ->where('summary.bill', 0)
                ->where('summary.average_bill', 0)
                ->has('byMonth', 12)
                ->has('byType', count(BuildingType::cases()))
                ->has('recentBuildings', 0));
    }

    /**
     * Seed buildings and tariffs used by the dashboard overview.
     */
    private function seedOverview(): void
    {
        foreach (range(1, 6) as $index) {
            Building::create([
                'customer_name' => "Customer {$index}",
                'type' => $index % 2 === 0
                    ? BuildingType::COMMERCIAL->value
                    : BuildingType::RESIDENTIAL->value,
                'month' => $index,
                'usability' => $index * 10,
                'state' => State::SELANGOR->value,
                'bill' => $index * 10,
            ]);
        }

        Tariff::create([
            'building_type' => BuildingType::RESIDENTIAL->value,
            'category' => 'First tier',
            'min_kwh' => 1,
            'max_kwh' => 100,
            'rate' => 0.5,
            'is_initial_rate' => true,
        ]);

        Tariff::create([
            'building_type' => BuildingType::RESIDENTIAL->value,
            'category' => 'Second tier',
            'min_kwh' => 101,
            'max_kwh' => 200,
            'rate' => 0.9,
            'is_initial_rate' => false,
        ]);

        Tariff::create([
            'building_type' => BuildingType::COMMERCIAL->value,
            'category' => 'First tier',
            'min_kwh' => 1,
            'max_kwh' => null,
            'rate' => 1.1,
            'is_initial_rate' => true,
        ]);
    }
}
