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

    /**
     * The owner's decision: password-only sign-in is the default for every
     * role, including Super Admin. Two-factor remains available but is no
     * longer imposed (config/security.php, REQUIRE_2FA).
     */
    public function test_super_admin_signs_in_with_password_alone_by_default(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);
        $user->assignRole('super_admin');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password-123',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
    }

    /** The mandatory-2FA machinery is retained and still works when enabled. */
    public function test_two_factor_can_be_made_mandatory_again_by_configuration(): void
    {
        config()->set('security.require_two_factor', true);
        config()->set('security.two_factor_roles', ['super_admin']);

        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);
        $user->assignRole('super_admin');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password-123',
        ])->assertRedirect(route('two-factor.setup'));

        $this->get('/dashboard')->assertRedirect(route('two-factor.setup'));
    }

    /**
     * Opting in must never be weaker than opting out: a user who has
     * enrolled is challenged even though 2FA is not mandatory.
     */
    public function test_a_user_who_enrolled_voluntarily_is_still_challenged(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct-password-123')]);
        $user->assignRole('hr_manager');
        $user->forceFill(['two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_enabled_at' => now()])->save();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password-123',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->get('/dashboard')->assertRedirect(route('two-factor.challenge'));
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
