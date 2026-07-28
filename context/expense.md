# Expense Module Context

## Overview
The Expense module handles operational business expenses (Biaya) that are not directly tied to purchasing inventory. Expenses can be created manually or auto-generated from **Goods Receipt** or **Delivery Order** third-party costs. Each expense supports line items, additional charges, discount, tax, and payment tracking.

---

## Module Structure

### Controller (`app/Http/Controllers/`)
| Controller | Responsibility |
|------------|----------------|a
| `ExpenseController` | CRUD for Expenses, datatable JSON endpoint, cancel action |

### Service (`app/Services/`)
| Service | Responsibility |
|---------|----------------|
| `ExpenseService` | Number generation, datatable data, fetch by ID, store/update (items + costs in transaction), auto-create from GoodsReceipt/DeliveryOrder third-party costs, cancel with status validation |

### Models (`app/Models/`)
| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `Expense` | `expenses` | `company`, `contact`, `items`, `costs`, `payments`, `creator` |
| `ExpenseItem` | `expense_items` | `expense`, `account` (ChartOfAccount) |
| `ExpenseCost` | `expense_costs` | `expense`, `account` (ChartOfAccount) |
| `ExpensePayment` | `expense_payments` | `expense`, `account` (ChartOfAccount), `company`, `creator` |

### Enum (`app/Enums/`)
| Enum | Values |
|------|--------|
| `ExpenseStatus` | `draft`, `open`, `partial`, `paid`, `cancelled` |

---

## Database Schema

```sql
expenses:
- id
- company_id (FK → companies)
- contact_id (nullable, FK → contacts)
- number (unique, 50)                        -- auto-generated: EXP-<CompanyCode>-<Year>-<Counter>
- reference_number (nullable, 50)
- expense_date (dateTime)
- due_date (nullable, dateTime)
- payment_terms (enum: net_7, net_14, net_30, net_45) nullable, default: net_14
- status (enum: draft, open, partial, paid, cancelled) default: draft
- subtotal (decimal 18,4)
- discount_percentage (decimal 5,2)
- discount_amount (decimal 18,4)
- tax_percentage (decimal 5,2)
- tax_amount (decimal 18,4)
- total_amount (decimal 18,4)
- remaining_amount (decimal 18,4)
- note (nullable text)
- created_by (FK → users)
- timestamps

expense_items:
- id
- expense_id (FK → expenses, cascade delete)
- account_id (FK → chart_of_accounts)
- description (nullable text)
- amount (decimal 18,4)
- timestamps

expense_costs:
- id
- expense_id (FK → expenses, cascade delete)
- account_id (FK → chart_of_accounts)
- description (nullable text)
- amount (decimal 18,4)
- timestamps

expense_payments:
- id
- company_id (FK → companies)
- expense_id (FK → expenses, cascade delete)
- account_id (FK → chart_of_accounts)  -- cash/bank account used
- number (unique, 50)
- amount (decimal 18,4)
- payment_date (dateTime)
- payment_method (enum: cash, bank_transfer, credit_card) default: cash
- reference_number (nullable, 50)
- note (nullable text)
- created_by (FK → users)
- timestamps
```

---

## Routes

All routes are under the `expenses` prefix and require authentication (`auth` middleware).

```
GET  /expenses              → expenses.index       (list view)
GET  /expenses/datatable    → expenses.datatable   (JSON, paginated)
GET  /expenses/create       → expenses.create
POST /expenses              → expenses.store

GET  /expenses/{id}         → expenses.show
GET  /expenses/{id}/edit    → expenses.edit
PUT  /expenses/{id}         → expenses.update
POST /expenses/{id}/cancel  → expenses.cancel
```

---

## Status Lifecycle

```
draft → open → partial → paid
  ↓       ↓
cancelled cancelled
```

- **draft**: Expense saved but not yet confirmed. All fields optional except `expense_date`.
- **open**: Expense confirmed. Requires `contact_id` and at least one item.
- **partial**: Payment(s) recorded but not fully paid (set externally via payment logic).
- **paid**: Fully paid (`remaining_amount = 0`).
- **cancelled**: Cannot cancel if status is `partial` or `paid`. Cannot cancel an already-cancelled expense.

---

## Amount Calculation

```
subtotal         = sum of all expense_items.amount
discount_amount  = (discount_percentage / 100) * subtotal
tax_amount       = (tax_percentage / 100) * (subtotal - discount_amount)
charge_amount    = sum of all expense_costs.amount
total_amount     = subtotal - discount_amount + tax_amount + charge_amount
remaining_amount = total_amount  (decremented as payments are recorded)
```

---

## Number Generation

Pattern: `EXP-{CompanyCode}-{Year}-{Counter}`

- `CompanyCode`: from `companies.code` for the selected company context.
- `Year`: current year (4 digits).
- `Counter`: count of expenses in the current year for the selected company, zero-padded to 4 digits.

Example: `EXP-PPI-2026-0001`

---

## Auto-Creation from Other Modules

### From Goods Receipt (`storeExpenseFromGoodsReceipt`)
- Triggered when a Goods Receipt is finalized with costs billed by `third_party`.
- Creates one `Expense` per third-party cost, with a single `ExpenseItem` mirroring the cost's `account_id`, `description`, and `amount`.
- `reference_number` is set to the Goods Receipt number.
- Status starts as `draft`.

### From Delivery Order (`storeExpenseFromDeliveryOrder`)
- Same pattern as Goods Receipt but sourced from `DeliveryOrder.costs` where `billed_by = third_party`.
- `reference_number` is set to the Delivery Order number.

---

## Validation Rules

### Draft status (`store` / `update`)
| Field | Rule |
|-------|------|
| `contact_id` | nullable, must exist in `contacts` |
| `expense_date` | required, date |
| `due_date` | nullable, date, after_or_equal `expense_date` |
| `payment_terms` | nullable, one of `net_7`, `net_14`, `net_30`, `net_45` |
| `items` | nullable array |
| `items.*.account_id` | required when items present, must exist |
| `items.*.amount` | required when items present, numeric, min 0 |
| `costs` | nullable array |
| `costs.*.account_id` | required when costs present, must exist |
| `costs.*.amount` | required when costs present, numeric, min 0 |

### Open status (`store` / `update`)
Same as draft, plus:
| Field | Rule |
|-------|------|
| `contact_id` | **required**, must exist |
| `items` | **required**, array, min 1 item |

---

## Views (`resources/views/expense/`)
| View | Purpose |
|------|---------|
| `index.blade.php` | Expense list with datatable, status filter |
| `create.blade.php` | Expense creation form |
| `edit.blade.php` | Expense edit form (draft/open only) |
| `show.blade.php` | Expense detail view |

---

## Key Dependencies
- `AccountService` — provides chart-of-accounts dropdown for item/cost account selection.
- `PaymentTerm` enum — calculates `due_date` offset in days from `payment_terms` value.
- `config('context.selected_company_id')` — multi-company context used for scoping queries and number generation.
- `auth()->id()` — stored as `created_by` on expense and payment records.
