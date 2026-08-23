<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OutletScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_outlet_manager_only_sees_employees_at_their_own_outlet(): void
    {
        $outletA = Outlet::factory()->create();
        $outletB = Outlet::factory()->create();

        Employee::factory()->create(['outlet_id' => $outletA->id, 'name' => 'Alinea Staffer']);
        Employee::factory()->create(['outlet_id' => $outletB->id, 'name' => 'Other Outlet Staffer']);

        $manager = User::factory()->create(['outlet_id' => $outletA->id]);
        $manager->assignRole('outlet_manager');

        $this->actingAs($manager);

        $visible = Employee::all();

        $this->assertCount(1, $visible);
        $this->assertEquals('Alinea Staffer', $visible->first()->name);
    }

    public function test_super_admin_bypasses_outlet_scope(): void
    {
        $outletA = Outlet::factory()->create();
        $outletB = Outlet::factory()->create();

        Employee::factory()->create(['outlet_id' => $outletA->id]);
        Employee::factory()->create(['outlet_id' => $outletB->id]);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        $this->assertCount(2, Employee::all());
    }
}
