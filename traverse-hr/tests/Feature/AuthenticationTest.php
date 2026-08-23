<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_super_admin_must_enroll_2fa_before_reaching_dashboard(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);
        $user->assignRole('super_admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password-123',
        ]);

        $response->assertRedirect(route('two-factor.setup'));

        // Dashboard must stay unreachable until 2FA is actually enrolled.
        $this->get('/dashboard')->assertRedirect(route('two-factor.setup'));
    }

    public function test_outlet_manager_with_no_2fa_requirement_reaches_dashboard_directly(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);
        $user->assignRole('outlet_manager');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password-123',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
    }

    public function test_failed_logins_lock_the_account_with_backoff(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $user->refresh();
        $this->assertTrue($user->isLocked(), 'Account should be locked after 5 consecutive failed logins.');

        // Even the correct password is rejected while locked.
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password-123',
        ]);
        $response->assertSessionHasErrors('email');
    }

    public function test_login_attempt_is_written_to_the_append_only_audit_log(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login_failed',
        ]);
    }
}
