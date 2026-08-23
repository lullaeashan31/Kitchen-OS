# Traverse HR

Employee lifecycle system for Traverse Inc. / Gautam Industries (Alinea and
future outlets): hiring → offer → digital onboarding paperwork → employee
master → attendance-driven payroll. Standalone Laravel app, its own login,
no dependency on Kitchen-OS.

## Status: M1 (foundation) complete

- Auth with TOTP 2FA (mandatory for Super Admin / HR / Accounts), failed-login
  lockout with exponential backoff, Super-Admin email alert on repeated
  failures, 30-minute session timeout.
- Granular permission engine (spatie/laravel-permission) seeded with the four
  system-user profiles from `PERMISSION_MATRIX.md`.
- Outlets and job roles (business data, kept separate from system
  permissions) — soft-deletable, blocks deletion of a role with employees
  attached.
- Employee master with encrypted statutory identifiers, masked display, and
  the restricted Aadhaar-last-four-only field set.
- Outlet scoping enforced as an Eloquent global scope, not a controller
  filter.
- Append-only audit log with CSV export; unmasking a statutory identifier
  requires a reason and forced password re-confirmation, and is logged.
- 12 automated tests covering login/2FA/lockout, outlet scoping, the
  job-role deletion guard, and the audit trail (`php artisan test`).

See `ERD.md`, `PERMISSION_MATRIX.md`, `PAYROLL_SPEC.md` and `DECISIONS.md`
for the design docs this was built against, and for everything still
waiting on real source documents from the owner (offer letter, HR policy,
salary component split, bank file spec, outlet/role list).

Milestones M2 (documents/signing), M3 (payroll), M4 (recruitment/offers),
M5 (multilingual) are not started.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan traverse:create-admin   # creates your first named Super Admin
php artisan serve
```

## Tests

```bash
php artisan test
```
