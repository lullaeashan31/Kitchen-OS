<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mandatory two-factor authentication
    |--------------------------------------------------------------------------
    |
    | When true, users holding the roles listed below must enrol in TOTP and
    | pass a challenge at every login. The owner has chosen password-only
    | sign-in for now, so this defaults to false.
    |
    | The machinery is intact, not removed: flip REQUIRE_2FA=true in .env to
    | turn it back on for everyone in these roles, and individual users can
    | still enable 2FA voluntarily from their own account at any time.
    |
    | Worth knowing: this system holds staff bank details, PAN and UAN. With
    | this off, one guessed or reused password is enough to reach them, which
    | is why the account lockout, session timeout and audit log below carry
    | more weight than they otherwise would.
    |
    */

    'require_two_factor' => env('REQUIRE_2FA', false),

    'two_factor_roles' => ['super_admin', 'hr_manager', 'accounts'],

];
