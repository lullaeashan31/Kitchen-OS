<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * Admin → Users. Lets the Super Admin create HR / Accounts / Outlet
 * Manager logins from the browser. Previously this only existed as an
 * artisan command, which is unreachable on hosting without a terminal.
 */
class UserController extends Controller
{
    /** Human-readable labels for the seeded permission profiles. */
    public const ROLE_LABELS = [
        'super_admin' => 'Super Admin — full access, including users and audit log',
        'hr_manager' => 'HR / Manager — recruitment, onboarding, employee master',
        'accounts' => 'Accounts — payroll lock & pay, bank file, statutory reports',
        'outlet_manager' => 'Outlet Manager — restricted to a single outlet',
    ];

    public function index(Request $request)
    {
        $this->authorizeManage($request);

        return view('users.index', [
            'users' => User::with('roles', 'outlet')->orderBy('name')->paginate(25),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeManage($request);

        return view('users.form', $this->formData(new User));
    }

    public function store(Request $request)
    {
        $this->authorizeManage($request);
        $data = $this->validated($request, null);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'outlet_id' => $data['role'] === 'outlet_manager' ? $data['outlet_id'] : null,
            'active' => true,
        ]);
        $user->syncRoles([$data['role']]);

        AuditLogger::log('user_created', $user, meta: ['role' => $data['role']]);

        return redirect()->route('users.index')
            ->with('status', "{$user->name} can now sign in. They'll be asked to set up two-factor authentication on first login.");
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizeManage($request);

        return view('users.form', $this->formData($user));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeManage($request);
        $data = $this->validated($request, $user);

        // Never let the last Super Admin demote or deactivate themselves out
        // of the system — that locks everyone out of user management for good.
        if ($this->wouldRemoveLastSuperAdmin($user, $data)) {
            return back()->withErrors(['role' => 'This is the only active Super Admin. Promote another user first.'])->withInput();
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'outlet_id' => $data['role'] === 'outlet_manager' ? $data['outlet_id'] : null,
            'active' => $request->boolean('active'),
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
            AuditLogger::log('user_password_reset', $user);
        }

        $user->syncRoles([$data['role']]);
        AuditLogger::log('user_updated', $user, meta: ['role' => $data['role'], 'active' => $user->active]);

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    /**
     * Clears a user's TOTP enrolment so they can re-enrol — the recovery
     * path for a lost or replaced phone. Deliberately does not disable 2FA:
     * they are forced to set it up again at next login.
     */
    public function resetTwoFactor(Request $request, User $user)
    {
        $this->authorizeManage($request);

        // Whether this is a reset or an outright switch-off depends on the
        // 2FA policy, so work it out BEFORE clearing the enrolment.
        $willReEnrol = $user->requiresTwoFactor();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled_at' => null,
        ])->save();

        AuditLogger::log('user_2fa_reset', $user);

        return back()->with('status', $willReEnrol
            ? "Two-factor reset for {$user->name}. They'll be asked to set it up again at next login."
            : "Two-factor turned off for {$user->name}. They'll sign in with just their password from now on.");
    }

    /**
     * Deactivation, not deletion — a user who witnessed a signing must
     * remain resolvable on that record forever.
     */
    public function destroy(Request $request, User $user)
    {
        $this->authorizeManage($request);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }
        if ($this->wouldRemoveLastSuperAdmin($user, ['role' => null])) {
            return back()->withErrors(['user' => 'This is the only active Super Admin. Promote another user first.']);
        }

        $user->update(['active' => false]);
        AuditLogger::log('user_deactivated', $user);

        return redirect()->route('users.index')->with('status', "{$user->name} deactivated. Their history is retained.");
    }

    private function validated(Request $request, ?User $user): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email:rfc', 'max:255',
                // Defensive: reject control characters outright. Guards the
                // known CRLF-injection class of bug in the framework's
                // default email rule without a major-version upgrade.
                'regex:/^[^\r\n\t]+$/',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(12)],
            'role' => ['required', Rule::in(array_keys(self::ROLE_LABELS))],
            'outlet_id' => ['nullable', 'required_if:role,outlet_manager', 'exists:outlets,id'],
        ], [
            'outlet_id.required_if' => 'An Outlet Manager must be assigned to an outlet.',
            'email.regex' => 'That email address contains invalid characters.',
        ]);
    }

    private function wouldRemoveLastSuperAdmin(User $user, array $data): bool
    {
        if (! $user->hasRole('super_admin')) {
            return false;
        }
        if (($data['role'] ?? null) === 'super_admin') {
            return false;
        }

        return User::role('super_admin')->where('active', true)->where('id', '!=', $user->id)->doesntExist();
    }

    private function formData(User $user): array
    {
        return [
            'user' => $user,
            'roleLabels' => self::ROLE_LABELS,
            'currentRole' => $user->roles->first()?->name,
            'outlets' => Outlet::where('active', true)->orderBy('name')->get(),
        ];
    }

    private function authorizeManage(Request $request): void
    {
        abort_unless($request->user()->can('user.manage'), 403);
    }
}
