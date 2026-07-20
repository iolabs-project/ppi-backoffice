# Payment Module Context

## Overview
The Payment module handles payment recording for both **Account Payable (Hutang)** and **Account Receivable (Piutang)**. It sits under the Finance group and links directly to `PurchaseInvoice` and `SalesInvoice`. When a payment is stored, the related invoice's `remaining_amount` is decremented and its status is updated accordingly.

---

## Module Structure

### Controllers (`app/Http/Controllers/Finance/`)
| Controller | Responsibility |
|------------|----------------|
| `AccountPayableController` | List purchase invoices with outstanding balances, show invoice detail + payment history, record a new purchase payment |
| `AccountReceivableController` | List sales invoices with outstanding balances, show invoice detail + payment history, record a new sales payment |

### Services (`app/Services/Finance/`)
| Service | Responsibility |
|---------|----------------|
| `AccountPayableService` | Generate AP payment number (`PP-<CompanyCode>-<Year>-<Counter>`), fetch paginated AP table data, fetch paginated payment history per invoice, store purchase payment with invoice status update |
| `AccountReceivableService` | Generate AR payment number (`SP-<CompanyCode>-<Year>-<Counter>`), fetch paginated AR table data, fetch paginated payment history per invoice, store sales payment with invoice status update |

### Models (`app/Models/`)
| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `PurchasePayment` | `purchase_payments` | `purchaseInvoice`, `account` (ChartOfAccount), `company`, `creator` (User) |
| `SalesPayment` | `sales_payments` | `salesInvoice`, `account` (ChartOfAccount), `company`, `creator` (User) |

---

## Database Schema

### Purchase Payments
```sql
purchase_payments:
- id
- company_id (FK → companies)
- purchase_invoice_id (FK → purchase_invoices)
- account_id (FK → chart_of_accounts)  -- cash/bank account used
- number (unique, 50)                   -- auto-generated: PP-<code>-<year>-<counter>
- payment_date (dateTime)
- payment_method (enum: cash, bank_transfer, credit_card) default: cash
- reference_number (nullable, 50)
- amount (decimal 18,4)
- note (nullable text)
- created_by (FK → users)
- timestamps
```

### Sales Payments
```sql
sales_payments:
- id
- company_id (FK → companies)
- sales_invoice_id (FK → sales_invoices)
- account_id (FK → chart_of_accounts)  -- cash/bank account used
- number (unique, 50)                   -- auto-generated: SP-<code>-<year>-<counter>
- payment_date (dateTime)
- payment_method (enum: cash, bank_transfer, credit_card) default: cash
- reference_number (nullable, 50)
- amount (decimal 18,4)
- note (nullable text)
- created_by (FK → users)
- timestamps
```

---

## Routes

All routes are under the `finances` prefix and require authentication.

```
GET  /finances/account-payables              → account_payables.index
GET  /finances/account-payables/datatable    → account_payables.datatable   (JSON)
GET  /finances/account-payables/{id}         → account_payables.show
POST /finances/account-payables/{id}         → account_payables.store

GET  /finances/account-receivables           → account_receivables.index
GET  /finances/account-receivables/datatable → account_receivables.datatable (JSON)
GET  /finances/account-receivables/{id}      → account_receivables.show
POST /finances/account-receivables/{id}      → account_receivables.store
```

---

## Business Logic

### Payment Number Generation
- **Purchase Payment**: `PP-{CompanyCode}-{Year}-{4-digit counter}` — counter is total payments for the company in the current year + 1.
- **Sales Payment**: `SP-{CompanyCode}-{Year}-{4-digit counter}` — same logic.

### Store Payment Flow
1. Validate request fields (`account_id`, `payment_date`, `payment_method`, `reference_number?`, `amount`, `note?`).
2. Guard checks:
   - If `remaining_amount <= 0` or status is `PAID` → throw validation error (already paid).
   - If status is `CANCELLED` → throw validation error (invoice cancelled).
3. Inside a DB transaction:
   - Create `PurchasePayment` / `SalesPayment` record with auto-generated number.
   - Decrement `invoice.remaining_amount` by `amount`.
   - Update invoice status:
     - `remaining_amount <= 0` → `paid`
     - `remaining_amount < total_amount` → `partial`
4. **TODO**: Insert Journal Entry for the payment (not yet implemented).

### Invoice Status Affected
| Condition | New Status |
|-----------|-----------|
| `remaining_amount <= 0` | `paid` |
| `0 < remaining_amount < total_amount` | `partial` |

This links to `PurchaseInvoiceStatus` (`draft`, `open`, `partial`, `paid`, `cancelled`) and `SalesInvoiceStatus` (same values).

---

## Views (`resources/views/finance/`)
| View | Purpose |
|------|---------|
| `account-payable/index.blade.php` | Paginated list of purchase invoices with outstanding balances |
| `account-payable/show.blade.php` | Invoice detail + payment history table + add payment form |
| `account-receivable/index.blade.php` | Paginated list of sales invoices with outstanding balances |
| `account-receivable/show.blade.php` | Invoice detail + payment history table + add payment form |

---

## Key Conventions
- `account_id` refers to a `ChartOfAccount` entry (typically a cash/bank account) used as the source of payment.
- `payment_method` is an enum: `cash`, `bank_transfer`, `credit_card`.
- `amount` must be `> 0` (validated `min:0.01`).
- All amounts are stored as `decimal(18,4)`.
- `payment_date` is cast to `date:Y-m-d` on both models.
- The `AccountPayableController` reuses `PurchaseInvoiceService::fetchPurchaseInvoiceByID()` for the show page.
- The `AccountReceivableController` reuses `SalesInvoiceService::fetchSalesInvoiceByID()` for the show page.
