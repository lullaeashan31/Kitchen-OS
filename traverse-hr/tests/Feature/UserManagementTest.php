<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function superAdmin(): User
    {
        $u = User::factory()->create(['two_factor_enabled_at' => now(), 'active' => true]);
        $u->assignRole('super_admin');
        $this->withSession(['2fa_verified' => true]);

        return $u;
    }

    public function test_super_admin_can_create_an_hr_manager_login_from_the_browser(): void
    {
        $this->actingAs($this->superAdmin())->post('/users', [
            'name' => 'Priya HR',
            'email' => 'priya@traverseinc.in',
            'role' => 'hr_manager',
            'password' => 'a-long-enough-password',
            'password_confirmation' => 'a-long-enough-password',
        ])->assertRedirect(route('users.index'));

        $created = User::where('email', 'priya@traverseinc.in')->firstOrFail();
        $this->assertTrue($created->hasRole('hr_manager'));
        $this->assertTrue($created->active);
    }

    public function test_an_outlet_manager_must_be_given_an_outlet(): void
    {
        $this->actingAs($this->superAdmin())->post('/users', [
            'name' => 'Site Manager',
            'email' => 'sm@traverseinc.in',
            'role' => 'outlet_manager',
            'password' => 'a-long-enough-password',
            'password_confirmation' => 'a-long-enough-password',
        ])->assertSessionHasErrors('outlet_id');
    }

    public function test_the_last_super_admin_cannot_demote_themselves_out_of_the_system(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'hr_manager',
            'active' => 1,
        ])->assertSessionHasErrors('role');

        $this->assertTrue($admin->fresh()->hasRole('super_admin'));
    }

    public function test_hr_manager_cannot_reach_user_management(): void
    {
        $hr = User::factory()->create(['two_factor_enabled_at' => now()]);
        $hr->assignRole('hr_manager');
        $this->withSession(['2fa_verified' => true]);

        $this->actingAs($hr)->get('/users')->assertForbidden();
    }

    public function test_resetting_two_factor_forces_re_enrolment(): void
    {
        $admin = $this->superAdmin();
        $target = User::factory()->create([
            'two_factor_secret' => 'SECRET', 'two_factor_enabled_at' => now(),
        ]);
        $target->assignRole('hr_manager');

        $this->actingAs($admin)->post("/users/{$target->id}/reset-2fa")->assertRedirect();

        $this->assertFalse($target->fresh()->twoFactorEnabled());
    }

    /**
     * With 2FA optional, clearing an enrolment must actually switch it off —
     * the user signs in with their password alone afterwards, rather than
     * being pushed back into setting it up again.
     */
    public function test_turning_off_two_factor_leaves_the_user_on_password_only(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $target = User::factory()->create(['password' => bcrypt('correct-password-123')]);
        $target->assignRole('hr_manager');
        $target->forceFill([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enabled_at' => now(),
        ])->save();

        // Enrolled, so still challenged.
        $this->post('/login', ['email' => $target->email, 'password' => 'correct-password-123'])
            ->assertRedirect(route('two-factor.challenge'));

        $this->actingAs($admin)->post(route('users.reset-2fa', $target))->assertRedirect();

        $this->assertFalse($target->fresh()->twoFactorEnabled());
        $this->assertNull($target->fresh()->two_factor_secret);

        $this->post('/logout');
        $this->post('/login', ['email' => $target->email, 'password' => 'correct-password-123'])
            ->assertRedirect('/dashboard');
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123'), 'active' => false]);
        $user->assignRole('hr_manager');

        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password-123'])
            ->assertSessionHasErrors('email');
    }
}
