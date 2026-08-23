# Permission Matrix — Traverse HR

Status: DRAFT for approval. Built on `spatie/laravel-permission`. Two
distinct concepts, kept in separate tables per §3.6 of the brief:

- **Job roles** (`job_roles` table) — Commis, Server, Sous Chef, etc. Business
  data an Admin can add/edit/rename/deactivate/reorder. Not used for access
  control at all.
- **System permissions** (`roles` + `permissions` tables, spatie) — what a
  *user of the software* can do. Seeded profiles below.

Legend: **F** full (create/read/update/delete), **R** read-only, **RW**
read/write but not the specific restricted action named, **–** no access,
**Own** scoped to the user's assigned outlet(s) only via a global scope on
the Employee/Applicant/PayrollRun models.

| Capability | Super Admin | HR / Manager | Accounts | Outlet Manager |
|---|---|---|---|---|
| Manage system users & permissions | F | – | – | – |
| View audit log | F | – | – | – |
| Manage outlets, job roles, document/offer templates, statutory slabs | F | – | – | – |
| Recruitment: vacancies, applicants, pipeline, trial shifts | F | F | R | F (Own) |
| Offer generation & approval | F | F (create/send; Super Admin or HR approves per config) | – | R (Own) |
| Employee master: create/edit profile fields | F | F | R | RW (Own, non-statutory fields only) |
| View statutory identifiers (PAN/UAN/ESIC/bank) unmasked | F (logged) | F (logged) | F (logged) | – |
| Document packs: assign, send links, view signed docs | F | F | R | RW (Own) |
| Salary structures: create/version | F | RW (create draft; cannot publish without Accounts/Super Admin) | F | – |
| Payroll run: create draft, enter days/adhoc lines | F | F | F | R (Own, view only) |
| Payroll run: **lock** | F | – | F | – |
| Payroll run: **mark paid**, export bank file | F | – | F | – |
| Statutory reports (PF/ESIC/PT) | F | R | F | – |
| Payslip generation & distribution | F | F | F | R (Own) |
| 2FA required at login | Yes | Yes | Yes | No (phase 1; recommend Yes once outlet managers get real accounts — flag in DECISIONS.md) |
| Outlet scope | All outlets | All outlets (configurable to scope per HR hire, default: all) | All outlets | Single assigned outlet, enforced via Eloquent global scope, not controller filtering |

## Notes

- "Employee" as a login role does not exist in this phase (§3.6.2) — staff
  reach documents only via tokenised links, never authenticated app access.
- Outlet scoping is implemented as an Eloquent **global scope** applied in
  each scoped model's `booted()` method, keyed off `auth()->user()->outlet_id`
  when the user's permission role is `outlet_manager`. Super Admin/HR/Accounts
  bypass the scope entirely (checked via `Gate`/permission, not role name
  string comparison, so this stays configurable).
- Every permission is a granular Spatie permission string
  (`payroll.lock`, `payroll.mark-paid`, `employee.view-unmasked`, etc.), not
  a hardcoded role check — the table above is the *seeded default* mapping,
  editable later from Admin → Permissions by Super Admin.
- Forced re-authentication (`password.confirm` middleware) required before:
  `payroll.lock`, `payroll.mark-paid`, `employee.view-unmasked` action.

## Open question for owner

Should HR/Manager be able to *approve* an offer they drafted themselves
(self-approval), or does every offer need a second approver? Defaulting to
**self-approval allowed for HR, Super Admin can always override** — confirm
or correct in DECISIONS.md review.
