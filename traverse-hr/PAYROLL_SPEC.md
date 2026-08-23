# Payroll Calculation Spec — Traverse HR

Status: DRAFT for approval. Do not treat as final until confirmed against real
salary structures and CA-confirmed statutory rates (see DECISIONS.md).

## 1. Core formula

```
paid_days   = days_present + paid_leave_days
divisor     = outlet setting: CALENDAR_DAYS_IN_MONTH (default) | FIXED_26 | FIXED_30
earned_gross = round( monthly_gross / divisor * paid_days, 2 )
```

Each salary component is pro-rated on the identical ratio:

```
component_earned = round( component_monthly_amount / divisor * paid_days, 2 )
```

### Reconciliation rule (rupee-exact)

Because each component is rounded independently, `sum(component_earned)` can
differ from `earned_gross` by a few paise/rupees. Rule:

1. Compute `earned_gross` first (rounded to 2dp).
2. Compute every component's `component_earned`, rounded to 2dp, **except**
   one designated "residual component" (default: **Special Allowance** —
   configurable per salary structure, must be an earning, non-statutory-capped
   component).
3. `residual_component_earned = earned_gross - sum(all other component_earned)`.
4. Assert `sum(all component_earned including residual) == earned_gross`
   exactly, to the paisa. This assertion is a unit test, not just a runtime
   check.

If the structure has no eligible residual component, payroll run creation is
blocked with a validation error asking the admin to designate one.

### Overtime and ad-hoc lines

Overtime, ad-hoc additions, and ad-hoc deductions are **not** pro-rated —
they are entered as absolute rupee amounts (or hours × configured hourly
rate for OT) and added/subtracted after `earned_gross` and its component
breakdown are finalized. They do not participate in the reconciliation
residual.

```
net_pay = earned_gross
        + overtime_amount
        + sum(adhoc_additions)
        - sum(adhoc_deductions)
        - statutory_deductions (PF employee share, ESIC employee share, PT)
```

Statutory deductions are computed on **wages defined per statute** (PF-wageable
sum, ESIC-wageable sum), not on net/gross indiscriminately — see §4.

## 2. Worked example A — full month, no exceptions

Employee: Server, monthly_gross = ₹18,000. Outlet divisor = CALENDAR_DAYS_IN_MONTH.
Month: August 2026 (31 days). days_present = 31, paid_leave_days = 0.

```
paid_days = 31
earned_gross = 18000 / 31 * 31 = 18000.00
```

Components (example structure, all earning, sum = 18000):
| Component | Monthly | Ratio applied | Earned |
|---|---|---|---|
| Basic | 9000 | 31/31 | 9000.00 |
| HRA | 4500 | 31/31 | 4500.00 |
| Conveyance | 1600 | 31/31 | 1600.00 |
| Special Allowance (residual) | 2900 | 31/31 | 2900.00 |
| **Total** | 18000 | | **18000.00** ✓ matches earned_gross |

No OT, no ad-hoc lines. `net_pay = 18000.00 - statutory (see §4 example)`.

## 3. Worked example B — 28-day month, mid-month joiner

Employee: Commis, monthly_gross = ₹15,000, joined 2026-08-04. Outlet divisor
= CALENDAR_DAYS_IN_MONTH. Payable days for August from joining date = 28
(Aug 4–31 inclusive). days_present = 27, paid_leave_days = 1 (1 paid sick day
approved by manager, recorded as a payroll input — this system does not do
leave accrual/approval workflow, HR enters the number directly per §3.5).

```
paid_days = 27 + 1 = 28
divisor = 31 (August has 31 calendar days)
earned_gross = 15000 / 31 * 28 = 13548.387... → round to 13548.39
```

Components (structure: Basic 50%, HRA 25%, Conveyance ₹1600 flat, Special
Allowance = residual):
| Component | Monthly | Raw pro-rated (÷31×28) | Rounded | Note |
|---|---|---|---|---|
| Basic | 7500 | 6774.19354... | 6774.19 | |
| HRA | 3750 | 3387.09677... | 3387.10 | |
| Conveyance | 1600 | 1445.16129... | 1445.16 | |
| Special Allowance (residual) | 2150 | 1941.93548... | **1941.94** | forced to reconcile |
| **Sum of first 3** | | | 11606.45 | |
| **earned_gross** | | | **13548.39** | |
| **Residual = 13548.39 − 11606.45** | | | **1941.94** | matches naive rounding here; won't always |

This shows the residual component absorbs whatever the straight rounding of
the other components would have missed by (in this example, ₹0.00 drift —
worked deliberately with a case that shows drift is included below in a
supplementary note in the test suite, since a hand-picked example with zero
drift would understate the rule).

## 4. Worked example C — unpaid leave + advance recovery, with statutory

Employee: Bartender, monthly_gross = ₹22,000, divisor = FIXED_30 (this
outlet's setting). days_present = 25, paid_leave_days = 0, unpaid_leave_days
= 5 (25+5=30, accounted for, doesn't affect paid_days). Ad-hoc deduction:
₹2,000 advance recovery (fixed installment). PF applicable, ESIC applicable
(wage below ceiling), PT applicable (Maharashtra slab).

```
paid_days = 25 + 0 = 25
earned_gross = 22000 / 30 * 25 = 18333.33 (rounded)
```

Components (Basic 50% / HRA 20% / Conveyance flat 1600 / Special Allowance
residual), pro-rated at 25/30:
| Component | Monthly | Earned |
|---|---|---|
| Basic | 11000 | 9166.67 |
| HRA | 4400 | 3666.67 |
| Conveyance | 1600 | 1333.33 |
| Special Allowance (residual) | 5000 | 4166.66 |
| **Total** | 22000 | **18333.33** ✓ |

PF-wageable sum (Basic + special allowances flagged PF-wageable — assume
Basic only per common config, capped at statutory wage ceiling ₹15,000/mo
pro-rated): PF wage this month = min(9166.67, 15000/30*25=12500) = 9166.67.
`PF employee share = 12% of 9166.67 = 1100.00` (rounded).

ESIC-wageable sum (gross wages if ≤ ₹21,000/month threshold — pro-rated
threshold check uses **full monthly gross**, not earned_gross, per ESIC
rules: 22000 > 21000 → **not ESIC applicable this month**, employee is
above the ESIC wage ceiling). `ESIC = 0.00`.

Professional Tax (Maharashtra slab on gross earned this month, ₹18,333.33 →
falls in the >₹10,000 slab): `PT = 200.00` (standard MH monthly slab,
₹300 in Feb per MH rule — seeder must encode the Feb exception, see
DECISIONS.md).

```
net_pay = earned_gross (18333.33)
        - PF employee share (1100.00)
        - ESIC employee share (0.00)
        - PT (200.00)
        - advance recovery ad-hoc deduction (2000.00)
        = 15033.33
```

This example deliberately exercises: FIXED_30 divisor, unpaid leave not
counted in paid_days, an ad-hoc deduction outside the pro-ration
reconciliation, the ESIC wage-ceiling cutoff using full (not pro-rated)
monthly gross, and PF wage ceiling capping.

## 5. Open items requiring CA / owner confirmation before first live run

- Exact PF wage ceiling (₹15,000/month is the current central govt figure —
  confirm still current), employee vs employer share split, whether
  employer share is tracked in this system (recommend: yes, for statutory
  reports, even though it's not deducted from employee).
- ESIC wage ceiling (₹21,000/month current figure — confirm) and rate split.
- Maharashtra PT slab table including the February higher-slab exception —
  confirm current slabs.
- Which component(s) are PF-wageable / ESIC-wageable per your structures —
  placeholder above assumes Basic only; confirm.
- Confirm rounding convention: this spec rounds half-up to 2dp at each step
  except the residual, which is computed by subtraction (not independently
  rounded) so it can carry a 3rd-decimal remainder resolved to paise.
