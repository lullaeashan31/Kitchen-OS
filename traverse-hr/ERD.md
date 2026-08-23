# Entity-Relationship Model — Traverse HR

Status: DRAFT for approval. Tables grouped by module. FK = foreign key,
nullable unless marked required. All tables get `id` (bigint PK),
`created_at`/`updated_at`, and `deleted_at` where soft-delete is specified.

## Core / Admin

- **outlets**: id, name, code (prefix used in employee codes), address,
  timezone, active (bool), payroll_divisor_setting (enum: calendar/26/30),
  created_by.
- **job_roles**: id, outlet_id? (nullable = group-wide), name, department,
  default_designation, default_salary_band_min, default_salary_band_max,
  default_document_pack_id (FK), default_offer_template_id (FK),
  default_pipeline_id (FK), probation_months, notice_period_days,
  sort_order, active (bool), soft-deletes. **Block hard delete if any
  employee references this role.**
- **departments**: id, name, outlet_id?.
- **users** (system/software users, NOT staff): id, name, email,
  password, permission role(s) via spatie `model_has_roles`, outlet_id
  (nullable, required if role=outlet_manager), 2fa_secret (encrypted),
  2fa_recovery_codes (encrypted json), 2fa_enabled_at, last_login_at,
  failed_login_count, locked_until.
- **audit_logs**: id, user_id? (nullable = system/unauthenticated actor via
  token), action (string), auditable_type, auditable_id, ip_address,
  user_agent, reason (nullable, required for unmask actions), meta (json),
  created_at. **Append-only — no update/delete routes, ever.**

## Recruitment

- **vacancies**: id, job_role_id (FK), outlet_id (FK), headcount,
  target_start_date, budgeted_salary_min, budgeted_salary_max,
  status (enum: open/on_hold/filled/cancelled), created_by (FK users).
- **applicants**: id, name, phone (indexed, primary identifier),
  email?, vacancy_id (FK), source (enum), resume_path?, notes,
  duplicate_of_applicant_id? (self-FK, set when phone matches a prior
  applicant), created_at.
- **pipeline_stages**: id, job_role_id? (nullable = group default),
  name, sort_order, is_terminal_success (bool, e.g. "Joined"),
  is_terminal_failure (bool, e.g. "Rejected"/"Withdrawn").
- **applicant_stage_events**: id, applicant_id (FK), pipeline_stage_id
  (FK), moved_by (FK users), note, reason (required when moving to a
  terminal_failure stage), created_at. **Append-only history; current
  stage = latest event.**
- **trial_shifts**: id, applicant_id (FK), date, department,
  evaluator_id (FK users), scorecard (json — structured criteria +
  scores), recommendation (enum: hire/no_hire), notes.

## Offers

- **offer_templates**: id, job_role_id? (nullable = group default),
  name, version (int), html_body (text, merge tokens), active (bool),
  created_by. Versioned: new edits create a new row with incremented
  version, never mutate a version already used by a sent offer.
- **offer_token_registry**: id, token (string, e.g. `ctc_annual`),
  label, description. Reference table the admin UI validates templates
  against — not a per-offer table.
- **offers**: id, applicant_id (FK), offer_template_id (FK, pinned
  version), token (random 32+ byte, unique, indexed), status (enum:
  draft/approved/sent/viewed/accepted/declined/expired), merge_data
  (json snapshot of token values at generation time), expires_at,
  approved_by?, sent_at?, viewed_at?, decided_at?, pdf_path,
  pdf_sha256.
- **offer_signatures**: id, offer_id (FK), signature_id (FK →
  signatures, shared table with §Documents), decided_as (enum:
  accepted/declined).

## Employee master

- **employees**: id, employee_code (unique, generated per outlet
  prefix+sequence), applicant_id? (FK, set if converted from an
  offer), name, photo_path?, phone, emergency_contact_name,
  emergency_contact_phone, address, dob, date_of_joining, job_role_id
  (FK), designation, department_id (FK), outlet_id (FK),
  reporting_manager_id (self-FK), employment_type (enum), probation_end_date,
  confirmation_date?, status (enum: active/on_notice/exited),
  exit_date?, exit_reason?, pan_encrypted, uan_encrypted,
  esic_number_encrypted, bank_account_encrypted, ifsc_encrypted,
  aadhaar_last_four (string(4), not encrypted — not sensitive alone),
  aadhaar_verified_at?, aadhaar_verified_by? (FK users),
  aadhaar_scan_document_id? (FK → documents, restricted storage).
- **employee_documents_locker_tokens**: id, employee_id (FK), token,
  expires_at (nullable = permanent per spec), revoked_at?.
  (Distinct from the per-pack signing token — this is the standing
  locker link that survives after the pack is complete.)
- **salary_structures**: id, employee_id (FK), effective_from,
  effective_to? (nullable = current), monthly_gross, divisor_override?
  (nullable = use outlet default), created_by, superseded_by_id?
  (self-FK — never edit a past structure, always supersede).
- **salary_structure_components**: id, salary_structure_id (FK),
  salary_component_definition_id (FK), amount_or_percent (decimal),
  is_percent_of (enum: flat/percent_of_basic/percent_of_gross).
- **salary_component_definitions**: id, name, type (enum:
  earning/deduction), is_pf_wageable (bool), is_esic_wageable (bool),
  is_taxable (bool), is_residual_component (bool — exactly one
  earning component per structure should be eligible), active.
- **fnf_settlements**: id, employee_id (FK), exit_date,
  last_working_day, dues_json (itemized), recovery_json (itemized),
  net_settlement_amount, status (enum: draft/finalized/paid),
  finalized_by, finalized_at.

## Documents & signatures (shared by onboarding packs AND offers)

- **document_templates**: id, name, kind (enum:
  acknowledge_only/sign_with_fields/upload_required), created_by.
- **document_template_versions**: id, document_template_id (FK),
  version (int), language (enum: en/hi/mr), html_body?, field_schema?
  (json, for sign_with_fields), active (bool).
- **document_packs**: id, job_role_id (FK), name.
- **document_pack_items**: id, document_pack_id (FK),
  document_template_id (FK), sort_order, required (bool).
- **employee_document_instances**: id, employee_id (FK),
  document_template_version_id (FK, pinned at instantiation),
  status (enum: pending/viewed/completed/expired), token (32+ byte
  random, unique), token_expires_at, phone_gate_last_four?
  (optional 2nd factor), completed_pdf_path?, completed_pdf_sha256?,
  field_values (json, for sign_with_fields), uploaded_file_path? (for
  upload_required kind), language_viewed (enum: en/hi/mr, **logged for
  legal record**).
- **signatures**: id, employee_document_instance_id? (FK, nullable),
  offer_id? (FK, nullable — exactly one of these two set),
  signer_name, signature_type (enum: `drawn` now; `aadhaar_esign`
  reserved for future — schema supports without rewrite), png_path,
  stroke_data (json — points w/ timestamp+pressure), signed_at,
  ip_address, user_agent, capture_driver (string, e.g. `canvas`).
- **document_access_log**: view/field-entry/signature/download events
  — this may simply be `audit_logs` filtered by `auditable_type =
  EmployeeDocumentInstance`, avoiding a duplicate table. (Decision:
  reuse audit_logs rather than a parallel table — see DECISIONS.md.)

## Payroll

- **payroll_runs**: id, outlet_id (FK), month (date, first-of-month),
  status (enum: draft/locked/paid), locked_at?, locked_by?, paid_at?,
  paid_by?, divisor_used (snapshot at lock time).
- **payroll_run_lines**: id, payroll_run_id (FK), employee_id (FK),
  salary_structure_id (FK, the version in force that month),
  days_present, paid_leave_days, unpaid_leave_days, overtime_hours,
  overtime_amount, earned_gross, net_pay, pf_employee, pf_employer,
  esic_employee, esic_employer, professional_tax.
- **payroll_line_components**: id, payroll_run_line_id (FK),
  salary_component_definition_id (FK), earned_amount. (One row per
  component per line — this is where the reconciliation-to-the-rupee
  is asserted.)
- **payroll_adhoc_lines**: id, payroll_run_line_id (FK), type (enum:
  addition/deduction), label, amount, note.
- **statutory_slabs**: id, statute (enum: pf/esic/pt), outlet_id?
  (nullable = group default), effective_from, effective_to?,
  rule_json (rates/thresholds/ceilings), source_note (citation,
  required), active (bool).

## Retention

- **retention_policies**: id, record_type (string, e.g.
  `applicant_rejected`, `employee_document_signed`,
  `payroll_record`), retention_months, action (enum:
  flag_for_review — never `auto_delete` per brief §9).
- **retention_flags**: id, record_type, record_id, flagged_at,
  reviewed_at?, reviewed_by?, decision (enum:
  pending/retained/anonymised/deleted).

## Key relationships summary

```
Outlet 1—* JobRole, Employee, Vacancy, PayrollRun
JobRole 1—* Vacancy, Employee; 1—1 default DocumentPack/OfferTemplate/Pipeline
Vacancy 1—* Applicant
Applicant 1—* ApplicantStageEvent, TrialShift; 0—1 → Employee (on offer accept)
Offer *—1 Applicant, *—1 OfferTemplate(version); 1—1 Signature
Employee 1—* SalaryStructure (versioned), EmployeeDocumentInstance,
         PayrollRunLine, AuditLog(as auditable)
SalaryStructure 1—* SalaryStructureComponent → SalaryComponentDefinition
DocumentPack 1—* DocumentPackItem → DocumentTemplate 1—* DocumentTemplateVersion(per language)
EmployeeDocumentInstance 1—1 Signature (nullable until signed)
PayrollRun 1—* PayrollRunLine 1—* PayrollLineComponent, PayrollAdhocLine
```
