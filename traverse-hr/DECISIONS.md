# DECISIONS.md — Assumptions & decisions log

Running record of every place I decided something because the owner (or the
brief) was silent, or because a real source document wasn't available yet.
Review and correct anything here — nothing in this file should be treated as
final until confirmed.

## Infra

- **Repo location**: brief called for a fully separate repository. GitHub
  API access in this session returned `403 Resource not accessible by
  integration` on repo creation, and the session's push target is hard-locked
  to `lullaeashan31/Kitchen-OS` branch `claude/traverse-hr-system-ountsf`.
  Fallback: built as a fully independent Laravel project under `/traverse-hr`
  in this same repo — own `composer.json`, `artisan`, `.env`, migrations,
  no `require`/`use` of any Kitchen-OS code. Can be `git subtree split` or
  copied into its own repo later with zero rework; only the git history
  origin changes, not the code.
- **PHP/Laravel versions**: PHP 8.4 is what's installed in this dev
  environment; brief asks for 8.2+, so this is compatible. Production
  hosting should be checked for actual available PHP version on the
  cPanel account — if it's capped below 8.2, Laravel 11 will need
  downgrading to 10.x. Flagging for confirmation before deploy.
- **Packages chosen**: `spatie/laravel-permission` (permission engine),
  `pragmarx/google2fa-laravel` (self-hosted TOTP), `bacon/bacon-qr-code`
  (QR generation, no external service), `barryvdh/laravel-dompdf`
  (server-side PDF, pure-PHP, no headless Chrome — matches the hard
  constraint). All MIT/permissive licensed, all composer-only (no Node
  runtime requirement in production).

## Statutory / payroll

- Per your answer: PF, ESIC, and PT are all seeded as reference data with
  placeholder-but-cited rates, and **all three toggles start OFF per
  outlet** until confirmed with your CA. Nothing is deducted from a real
  payroll run until an outlet explicitly enables a statute.
- Worked payroll examples in `PAYROLL_SPEC.md` use illustrative rates
  (PF 12%, ESIC ceiling ₹21,000, PF wage ceiling ₹15,000, MH PT slabs)
  that are common current figures but **not verified against your CA** —
  do not run live payroll against them.
- Reconciliation residual component defaults to "Special Allowance" —
  confirm this is present in your real salary structures, or tell me the
  correct component name once you send the salary split.

## Retention

- Per your answer, using standard defaults: payroll/statutory records
  retained 8 years (flag-for-review only, never auto-delete, per §9);
  rejected-applicant data anonymised after 12 months; exited-employee
  document locker stays live 12 months post-exit then converts to
  internal-access-only. All stored as editable rows in
  `retention_policies`, not hardcoded.

## Schema

- Reused `audit_logs` as the single append-only log for *all* auditable
  events (unmask views, document views/signatures/downloads, stage
  changes) rather than a parallel `document_access_log` table — simpler
  export/breach-query story ("whose data was exposed and when" is one
  query against one table), and §3.4's audit requirements are a subset of
  the general audit trail's shape. Revisit if document-specific fields
  (e.g. language_viewed) turn out to need first-class columns instead of
  living in `audit_logs.meta` json.
- `job_roles` (business data, brief §3.6.1) is a fully separate table from
  spatie's `roles` table (system permissions, §3.6.2) — enforced by using
  distinct model classes `JobRole` and no reuse of Spatie's `Role` model
  for business logic anywhere.
- Aadhaar: built exactly as specified — `aadhaar_last_four`,
  `aadhaar_verified_at`, `aadhaar_verified_by`, optional scan in
  restricted storage. No full-Aadhaar-number column exists anywhere in
  the schema.

## Placeholders awaiting real documents (see §5.1 of brief)

None of the following exist yet in the codebase and will not until you
provide the source material — building M2 (documents/signing), M3
(payroll live rates), and M4 (offers) further than schema-level without
them would mean throwing away guessed content:

1. **Offer letter** — needed before building the real offer template /
   token list (currently only the ERD's `offer_token_registry` concept
   exists, no seeded tokens or template HTML).
2. **HR policy + per-role document list** — needed before seeding
   `document_packs`/`document_templates` content.
3. **Salary component split** — needed before seeding real
   `salary_component_definitions` (schema is ready, no real rows seeded).
4. **HDFC bulk transfer file spec** — needed before building the bank-file
   exporter driver (interface will be built in M3, HDFC driver
   implementation waits on the spec).
5. **Outlet/role list** — using `Alinea` as the only seeded outlet and a
   short placeholder job-role list (Commis, CDP, Sous Chef, Server,
   Captain, Bartender, Host, Steward, Manager — taken directly from the
   examples in §3.6.1 of the brief) until you confirm the real list.

## M2 slice: document types, variants, per-role assignment

Owner sent the real "Manager Onboarding Kit" PDF and gave explicit direction:
use it for all roles for now; support multiple uploaded variants per
document type (e.g. Manager vs Housekeeping) later; provide a per-job-role
dropdown to choose which variant a role gets. Built exactly that:

- `document_templates` = a document *type* (e.g. "POSH Policy").
  `document_template_variants` = named uploads under a type (e.g.
  "Default", "Manager"); exactly one is `is_default`.
  `document_template_versions` = the actual uploaded file, versioned —
  a new upload is always a new version row, never an overwrite, so a
  version already sent to an employee never changes under them.
- `job_role_document_variant_map` is the dropdown itself: one row per
  (job role, document type) that deviates from the type's default variant.
  No row = default variant. `DocumentVariantResolver` implements the
  lookup. Admin UI: Job roles → "Documents" (per-role dropdown grid),
  Document types → per-type variant/version management with file upload.
- The kit's own 16 documents were split one-PDF-per-document with `qpdf`
  (page ranges taken from the extracted text) and committed as real seed
  data under `database/seed-documents/manager-onboarding-kit/`, seeded by
  `DocumentTemplateSeeder` into a single "Default" variant, English, for
  each of the 16 types — all applicable to every role for now, per the
  owner's instruction. Uploaded files are stored on the `local` disk
  (`storage/app/private`, outside the public web root) and served only
  through `DocumentTemplateVersionController::download`, which requires
  `document.view` and logs every download.
- **Two items from the kit's own checklist page were deliberately NOT
  seeded as document types:**
  - **"Letter of Appointment"** — this is the appointment/offer letter
    itself, which belongs to the separate Offer module (§3.2, still
    blocked on that source document), not the onboarding document pack.
    Don't seed it here when the real offer letter arrives — build it as
    part of M4's offer-template engine instead.
  - **"Stores & Inventory Custody Accountability Undertaking (if
    applicable)"** — referenced in the kit's own checklist table but no
    such document was actually included in the PDF provided. Flagging in
    case the owner has it separately.
- Kind classification per document (acknowledge_only vs sign_with_fields)
  was inferred from what's actually fillable by a person versus what's
  pre-filled from employee-master data (name/designation/date, which are
  merge tokens, not "fields" in the brief's sense). Where a document has a
  genuine blank for someone to fill in, it's `sign_with_fields` with a
  `field_schema` entry — e.g. Cash & Float's float amount, Conflict of
  Interest's declared interests, Media Consent's consent choice, Company
  Property's items-issued detail, Food Handler's existing-certificate
  checkbox. Everything else (POSH, Drug & Alcohol, Confidentiality,
  Non-Solicitation, IT Acceptable-Use, HR Handbook receipt, Background
  Check consent, Trial/Probation terms, Acceptance of Appointment) is
  `acknowledge_only`.
- **Not built yet, deliberately deferred to the next M2 slice**: sending a
  pack to a specific employee (`employee_document_instances`), the
  tokenised link + QR delivery, the signature capture screens, PDF
  finalization with the audit footer, and the employee document locker.
  This slice only covers the admin-side "what document, which variant,
  for which role" configuration the owner asked for by name.

## HR Policy Manual — editable placeholder (owner instruction)

Owner: the HR policy keeps getting updated, and Super Admin should be able
to edit it directly whenever, rather than re-uploading a file each time.
Built a second content path on `document_template_versions` alongside file
upload: `DocumentTemplateController::storeTextVersion` lets an admin write/
edit `body_html` directly from Admin → Document Types, with a textarea
pre-filled with the current text. Saving still creates a new version
(never mutates one already shown to an employee) — the UI just reloads
with the new text so it *feels* like editing in place. Seeded a new
document type "HR Policy Manual" (distinct from "HR Handbook & Code of
Conduct — Receipt", which is the signed acknowledgement that the manual
was received) with placeholder text pointing the admin at where to edit it.

This same text-editing path is available on every document type, not only
the HR Policy Manual — useful for any other frequently-revised policy
later, without a schema change.

## Signing is in-person on a pad, confirmed by owner

Owner confirmed: onboarding signing happens in person, on a USB signature
pad at the restaurant, at the point of joining — not primarily by sending
staff a remote link to sign on their own phone (§3.4 describes both paths;
the owner is prioritizing the on-site kiosk path). After signing, the
document becomes readable any time via the employee's document locker
link, per §3.4's "permanent read-only document locker" requirement — no
change needed there, just confirms which capture path (`CanvasDriver` /
on-site kiosk) to build and demo first when the signature-capture slice of
M2 is built. Remote signing stays in scope as the fallback described in
the brief, just not the primary flow to design around.

## Signing simplified to typed-name acceptance (owner instruction)

Owner: "the signing session will just be like a classic one... people can
just digitally accept it by... writing their name down." Explicit
simplification of §3.4's signature capture — no canvas/pointer-event
drawing, no stroke-data capture, no signature pad integration needed for
now. Built accordingly:

- New `employee_documents` table: one row per (employee, document type),
  pinned to the exact `document_template_version` shown, with
  `signer_typed_name`, `signed_at`, `signed_ip`, `signed_user_agent`, and
  `recorded_by` (the HR/manager running the session). This is
  deliberately a leaner shape than the ERD's
  `employee_document_instances` + `signatures` split — no token/expiry
  (this flow is in-person, not a remote tokenised link) and no
  `signature_type` enum (only "typed" exists today). These rows migrate
  forward without loss whenever a pad or remote-phone signing path is
  actually built — same idea as the brief's requirement that `drawn` /
  `aadhaar_esign` can be added later without a schema rewrite, just
  starting from `typed` instead of `drawn` as the first type.
- Flow: Employees → "Documents" → pick a pending document → HR opens it
  with the employee (file download link, or the written policy text
  inline) → employee types their own name in the box → "Record
  acceptance." Every view and every acceptance is audit-logged
  (`document_viewed`, `document_signed`).
- Accepting a document twice updates the same row rather than creating a
  duplicate — treated as a correction during the same session, not a
  re-signature event. If that's wrong (e.g. you want every acceptance
  attempt kept), say so and I'll change it to append instead.
- PDF finalization (rendering a signed copy with the audit footer/hash)
  and the employee's own read-anytime document locker link are still not
  built — this slice only covers the HR-side "classic session" recording.
  Confirm whether staff need their own copy/link at all given signing is
  fully in-person and HR is the one opening the document, or whether the
  locker is still wanted so they can re-read it later on their own phone.

## Permission matrix open question

Flagged in `PERMISSION_MATRIX.md`: whether HR can self-approve an offer
they drafted, or a second approver is always required. Defaulted to
self-approval allowed, Super Admin can always override — confirm.
