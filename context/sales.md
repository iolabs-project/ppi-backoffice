# Sales Module Context

## Overview
The Sales module handles the complete sales lifecycle: **Sales Orders → Delivery Orders → Sales Invoices**. It's built with Laravel and follows a service-layer architecture with controllers, services, models, and enums.

---

## Module Structure

### Controllers (`app/Http/Controllers/Sales/`)
| Controller | Responsibility |
|------------|----------------|
| `SalesOrderController` | CRUD for Sales Orders, status transitions (draft → open → closed/cancelled) |
| `DeliveryOrderController` | CRUD for Delivery Orders, draft/finished/cancelled status, inventory integration |
| `SalesInvoiceController` | CRUD for Sales Invoices, draft/open/partial/paid/cancelled status |

### Services (`app/Services/Sales/`)
| Service (file) | Class | Responsibility |
|----------------|-------|----------------|
| `SalesOrderService.php` | `SalesOrderService` | SO number generation, data fetching (with sums for quantities), create/update (items + costs + charges in transaction), status changes, SO items for DO |
| `DeliveryOrderService.php` | `DeliveryOrderService` | DO number generation, data fetching, create from SO, update (draft save vs finalize), batch validation, unit cost calc, ProductBatch/InventoryTransaction creation, SO updates, DO items for SI |
| `SalesInvoiceService.php` | `SalesInvoiceService` | SI number generation, data fetching, create from SO, update (items + charges + OPEN side effects), cancel with SO reversal |

### Models (`app/Models/`)
| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `SalesOrder` | `sales_orders` | items, costs, charges, customer, warehouse, salesPerson, creator, deliveryOrders |
| `SalesOrderItem` | `sales_order_items` | product, salesOrder |
| `SalesOrderCost` | `sales_order_costs` | account, salesOrder |
| `SalesOrderCharge` | `sales_order_charges` | account, salesOrder |
| `DeliveryOrder` | `delivery_orders` | items, costs, salesOrder, customer, warehouse, creator |
| `DeliveryOrderItem` | `delivery_order_items` | product, salesOrderItem, deliveryOrder |
| `DeliveryOrderItemBatch` | `delivery_order_item_batches` | productBatch, deliveryOrderItem |
| `DeliveryOrderCost` | `delivery_order_costs` | account, deliveryOrder |
| `SalesInvoice` | `sales_invoices` | items, charges, salesOrder, customer, salesPerson, warehouse, creator |
| `SalesInvoiceItem` | `sales_invoice_items` | product, salesOrderItem, deliveryOrderItem, salesInvoice |
| `SalesInvoiceCharge` | `sales_invoice_charges` | account, salesInvoice |

### Enums (`app/Enums/`)
| Enum | Values |
|------|--------|
| `SalesOrderStatus` | `draft`, `open`, `closed`, `cancelled` |
| `DeliveryOrderStatus` | `draft`, `finished`, `cancelled` |
| `SalesInvoiceStatus` | `draft`, `open`, `partial`, `paid`, `cancelled` |
| `PaymentTerm` | `net_7` (7 days), `net_14` (14), `net_30` (30), `net_45` (45) |
| `AccountCategory` | `CASH_BANK` (used for down payment account selection) |

---

## Database Schema (Migrations)

### Sales Orders
```sql
sales_orders:
- id, company_id, customer_id, warehouse_id, sales_person_id
- number (unique), reference_number
- order_date, due_date
- payment_terms (enum: net_7, net_14, net_30, net_45)
- status (enum: draft, open, closed, cancelled)
- subtotal, discount_percentage, discount_amount
- tax_percentage, tax_amount
- down_payment_amount, down_payment_remaining_amount, down_payment_account_id
- total_amount, note, created_by
- timestamps

sales_order_items:
- id, sales_order_id, product_id
- quantity, shipped_quantity, invoiced_quantity
- unit_price, discount_percentage, discount_amount, total_amount
- timestamps

sales_order_costs:
- id, sales_order_id, account_id, description, amount
- timestamps

sales_order_charges:
- id, sales_order_id, account_id, description, amount
- timestamps
```

### Delivery Orders
```sql
delivery_orders:
- id, company_id, sales_order_id, customer_id, warehouse_id
- number (unique), reference_number
- delivery_date
- status (enum: draft, finished, cancelled)
- subtotal, total_amount, note, created_by
- timestamps

delivery_order_items:
- id, delivery_order_id, sales_order_item_id, product_id
- quantity
- timestamps

delivery_order_item_batches:
- id, delivery_order_item_id, product_batch_id
- quantity, unit_cost
- timestamps

delivery_order_costs:
- id, delivery_order_id, account_id, description, amount
- timestamps
```

### Sales Invoices
```sql
sales_invoices:
- id, company_id, sales_order_id, customer_id, sales_person_id, warehouse_id
- number (unique), reference_number
- invoice_date, due_date
- payment_terms (enum), status (enum: draft, open, partial, paid, cancelled)
- subtotal, discount_percentage, discount_amount
- tax_percentage, tax_amount
- down_payment_amount, total_amount, remaining_amount, note, created_by
- timestamps

sales_invoice_items:
- id, sales_invoice_id, sales_order_item_id, delivery_order_item_id, product_id
- quantity, unit_price, discount_percentage, discount_amount, total_amount
- timestamps

sales_invoice_charges:
- id, sales_invoice_id, account_id, description, amount
- timestamps
```

---

## Routes (`routes/web.php`)

### Sales Orders (`sales.sales_orders.*`)
| Method | URI | Action |
|--------|-----|--------|
| GET | `/sales/sales-orders` | index |
| GET | `/sales/sales-orders/datatable` | datatable |
| GET | `/sales/sales-orders/create` | create |
| POST | `/sales/sales-orders` | store |
| GET | `/sales/sales-orders/{id}` | show |
| GET | `/sales/sales-orders/{id}/edit` | edit |
| PUT | `/sales/sales-orders/{id}` | update |
| POST | `/sales/sales-orders/{id}/open` | open |
| POST | `/sales/sales-orders/{id}/close` | close |
| POST | `/sales/sales-orders/{id}/cancel` | cancel |

### Delivery Orders (`sales.delivery_orders.*`)
| Method | URI | Action |
|--------|-----|--------|
| GET | `/sales/delivery-orders` | index |
| GET | `/sales/delivery-orders/datatable` | datatable |
| POST | `/sales/delivery-orders` | store |
| GET | `/sales/delivery-orders/{id}` | show |
| GET | `/sales/delivery-orders/{id}/edit` | edit |
| PUT | `/sales/delivery-orders/{id}` | update |
| POST | `/sales/delivery-orders/{id}/cancel` | cancel |

### Sales Invoices (`sales.sales_invoices.*`)
| Method | URI | Action |
|--------|-----|--------|
| GET | `/sales/sales-invoices` | index |
| GET | `/sales/sales-invoices/datatable` | datatable |
| POST | `/sales/sales-invoices` | store |
| GET | `/sales/sales-invoices/{id}` | show |
| GET | `/sales/sales-invoices/{id}/edit` | edit |
| PUT | `/sales/sales-invoices/{id}` | update |
| POST | `/sales/sales-invoices/{id}/cancel` | cancel |

---

## Business Flow

### 1. Sales Order (SO)
```
Draft → Open → Closed (when all items shipped) / Cancelled
```
- **Draft**: Can be edited freely, minimal validation
- **Open**: Full validation required (customer, warehouse, items, payment terms)
- **Closed**: Auto-set when all SO items fully shipped
- **Cancelled**: Manual action

**Number Format**: `SO-{COMPANY_CODE}-{YEAR}-{SEQUENCE}` (e.g., `SO-PPI-2026-0001`)

### 2. Delivery Order (DO)
```
Draft → Finished → (auto-updates SO shipped_quantity, creates InventoryTransaction & ProductBatch)
```
- Created from a SO (copies customer, warehouse; stores with draft status)
- **Draft**: Can edit items, batch numbers optional
- **Finished**: 
  - Validates batch quantities match item quantity
  - Validates available stock in ProductBatch
  - Creates `DeliveryOrderItemBatch` for each batch
  - Decrements `ProductBatch.quantity`
  - Creates `InventoryTransaction` (type=sale, direction=-1) with stock_before/stock_after tracking
  - Increments `SalesOrderItem.shipped_quantity`
  - Auto-closes SO if all items have `shipped_quantity >= quantity`
  - Creates `DeliveryOrderCost` for each cost item

**Number Format**: `DO-{COMPANY_CODE}-{YEAR}-{SEQUENCE}`

### 3. Sales Invoice (SI)
```
Draft → Open → Partial / Paid / Cancelled
```
- Created from a SO (copies customer, warehouse, sales_person, payment_terms, discount/tax percentages; stores with draft status)
- **Draft**: Can edit freely
- **Open**: Full validation, links to DO items
  - Increments `SalesOrderItem.invoiced_quantity` for each detail item
  - Decrements `SalesOrder.down_payment_remaining_amount` by down_payment_amount
- **Partial**: Some payment received
- **Paid**: Fully paid
- **Cancelled**: Only allowed if Draft or Open with no payments (total_amount == remaining_amount)
  - Reverses `SalesOrderItem.invoiced_quantity` (decrements for each item)
  - Restores `SalesOrder.down_payment_remaining_amount`

**Number Format**: `SI-{COMPANY_CODE}-{YEAR}-{SEQUENCE}`

---

## Key Service Methods

### Sales Order (`SalesOrderService`)
| Method | Description |
|--------|-------------|
| `generateSONumber()` | Generates unique SO number |
| `fetchSalesOrderTableData(Request)` | Paginated list with search/filter, includes withSum for total_quantity, total_shipped_quantity, total_invoiced_quantity |
| `fetchSalesOrderByID(int)` | Full SO with items, costs, charges, relationships (unit, creator) |
| `storeSalesOrder(Request)` | Creates SO + items + costs + charges in transaction; calculates subtotal, discount, tax, total_amount |
| `updateSalesOrder(Request, int)` | Updates SO + replaces items + costs + charges (delete all + recreate) |
| `changeSalesOrderStatus(int, string)` | Simple status update (used by open/close/cancel) |
| `fetchSOItemsForDeliveryOrder(int)` | SO items with remaining quantities for DO; maps product_code, product_name, unit |

### Delivery Order (`DeliveryOrderService`)
| Method | Description |
|--------|-------------|
| `generateDONumber()` | Generates unique DO number |
| `fetchDeliveryOrderTableData(Request)` | Paginated list with search/filter, includes withSum for total_shipped_quantity |
| `storeDeliveryOrder(Request)` | Creates DO from SO (draft status, copies company/customer/warehouse from SO) |
| `fetchDeliveryOrderByID(int)` | Full DO with items, batches, costs, salesOrder reference |
| `fetchAvailableBatches(int, array)` | Available product batches for warehouse & products |
| `updateDeliveryOrder(Request, int)` | Updates DO; delegates to `saveDraftDeliveryOrder()` or `finalizeDeliveryOrder()` based on status |
| `saveDraftDeliveryOrder()` | Saves items/batches/costs without inventory impact |
| `finalizeDeliveryOrder()` | **Core logic**: batch validation, stock check, decrements ProductBatch, creates InventoryTransaction (with stock_before/stock_after), updates SO shipped_quantity, auto-closes SO |
| `changeDeliveryOrderStatus(int, string)` | Simple status update |
| `fetchDOItemsForSalesInvoice(int)` | Finished DO items for SI creation; filters by sales_order_id and finished status |

### Sales Invoice (`SalesInvoiceService`)
| Method | Description |
|--------|-------------|
| `generateSINumber()` | Generates unique SI number |
| `fetchSalesInvoiceTableData(Request)` | Paginated list with search/filter |
| `storeSalesInvoice(Request)` | Creates SI from SO (draft status); copies payment_terms, discount/tax percentages, charges |
| `updateSalesInvoice(Request, int)` | Updates SI + items + charges (delete all + recreate); if status=OPEN: increments SO invoiced_quantity, decrements down_payment_remaining_amount |
| `fetchSalesInvoiceByID(int)` | Full SI with items, charges, salesOrder, DO item references |
| `cancelSalesInvoice(int)` | Cancels SI; validates allowed status (Draft or Open with no payments), reverses SO invoiced_quantity & restores down_payment_remaining_amount |

---

## Validation Rules

### Sales Order Store/Update
- **Open status**: All fields required (customer_id, warehouse_id, number, order_date, payment_terms, details min 1 with product_id, quantity, unit_price)
- **Draft status**: Only customer_id, warehouse_id, number, order_date required
- Costs: account_id + amount required together (required_with on any cost)
- Charges: account_id + amount required together (required_with on any charge)
- Number uniqueness check against `sales_orders.number`

### Delivery Order Update (non-draft)
- Required: delivery_date, status (draft|finished), details (min 1)
- Each detail: sales_order_item_id, product_id, quantity, batches (min 1)
- Each batch: product_batch_id, quantity
- Costs: account_id + amount required together
- Validation error messages in Indonesian

### Sales Invoice Update (non-draft)
- Required: invoice_date, due_date (>= invoice_date), status=open, payment_terms, details (min 1)
- Each detail: delivery_order_item_id, sales_order_item_id, product_id, quantity, unit_price, discount_percentage
- Charges: account_id + amount required together
- Validation error messages in Indonesian

---

## Inventory Integration (Delivery Order Finalize)

When DO status changes to **Finished** (in `finalizeDeliveryOrder`):

1. **Batch Validation**: 
   - Total batch quantity must equal item quantity
   - ProductBatch must exist for the warehouse & product
   - Available quantity must be sufficient

2. **Stock Decrement**: Decrements `ProductBatch.quantity` for each batch

3. **Creates InventoryTransaction**:
   - type: 'sale', direction: -1 (out)
   - stock_before: latest transaction's stock_after (or 0 if none)
   - stock_after: stock_before - quantity
   - reference: DeliveryOrder (polymorphic)
   - note: 'Pengiriman Barang dari DO #{number}'

4. **Updates SO**: Increments `SalesOrderItem.shipped_quantity`

5. **Auto-closes SO**: If all items have `shipped_quantity >= quantity` (uses `whereDoesntHave` check)

6. **Creates DeliveryOrderCost**: For each cost item

---

## Cost & Charge Handling

### Cost Fields
- **SalesOrderCost**: Simple account_id, description, amount
- **DeliveryOrderCost**: Simple account_id, description, amount — created during finalize
- **SalesInvoiceCharge**: Simple account_id, description, amount

### Charge Fields
- **SalesOrderCharge**: Simple account_id, description, amount
- **SalesInvoiceCharge**: Simple account_id, description, amount

### Cost/Charge Flow
```
SO Costs & Charges (all categories)
    ↓
DO Costs (created during finalize; copies from SO costs)
    ↓
SI Charges (copied from SO charges during SI creation; updated during SI update)
```

### Key Behaviors
- DO only creates costs during `finalizeDeliveryOrder()` (not during draft save)
- SI charges are copied from SO charges during `storeSalesInvoice()`
- SI charges are recreated fresh during SI update (not copied from DO)

---

## Configuration

### Context Config (`config/context.php`)
```php
'selected_company_id' => 1
```
Used for number generation and company-scoped queries.

---

## Views (`resources/views/sales/`)

### Sales Order
- `index.blade.php` - List with datatable
- `create.blade.php` - Form with products, warehouses, customers, sales persons, cash/bank accounts
- `edit.blade.php` - Edit form
- `show.blade.php` - Detail view

### Delivery Order
- `index.blade.php` - List with datatable
- `edit.blade.php` - Edit form with remaining SO items (uses `fetchSOItemsForDeliveryOrder`) and available batches
- `show.blade.php` - Detail view

### Sales Invoice
- `index.blade.php` - List with datatable
- `edit.blade.php` - Edit form with payment terms, remaining DO items (uses `fetchDOItemsForSalesInvoice`)
- `show.blade.php` - Detail view
- (No create view - created from SO via store action, redirects to edit)

---

## Key Business Rules

1. **SO → DO → SI Chain**: Each document references the previous one (DO references SO, SI references SO and DO items)
2. **Quantity Tracking**: 
   - SO: `quantity` → `shipped_quantity` (via DO) → `invoiced_quantity` (via SI)
   - DO: `quantity` (linked to SO item, validated against batches)
3. **Down Payment**: SO tracks `down_payment_amount` & `down_payment_remaining_amount`; SI reduces remaining on OPEN and restores on Cancel
4. **Batch Tracking**: Required on DO finish; validates against ProductBatch; creates InventoryTransaction for inventory valuation
5. **Auto-close SO**: When all items have `shipped_quantity >= quantity` (checked via `whereDoesntHave`)
6. **SI Status Effects on OPEN**:
   - Increments `SalesOrderItem.invoiced_quantity` for each item
   - Decrements `SalesOrder.down_payment_remaining_amount` by down_payment_amount
7. **Cancellation Rules**:
   - SO: Simple status update (any status can be changed)
   - DO: Simple status update (inventory reversal handled separately if finished)
   - SI: Only Draft or Open with total_amount == remaining_amount (no payments received)
     - Reverses SO invoiced_quantity (decrements)
     - Restores SO down_payment_remaining_amount

---

## Dependencies

### External Services Used
- `App\Services\Master\ContactService` - Customer, sales person, supplier data
- `App\Services\Master\WarehouseService` - Warehouse data
- `App\Services\Master\AccountService` - Chart of accounts (cash/bank for down payments)
- `App\Services\InventoryService` - Global inventory stock data

### Models Referenced
- `App\Models\Company` - Company code for number generation
- `App\Models\Contact` - Customer, sales person
- `App\Models\Warehouse` - Warehouse
- `App\Models\Product` - Product
- `App\Models\ProductBatch` - Batch tracking
- `App\Models\ChartOfAccount` - Account for costs/charges
- `App\Models\InventoryTransaction` - Inventory movements
- `App\Models\User` - Creator tracking

---

## Legacy/Alternative Routes (`PembelianController`)
Older ERP-style routes under `/pembelian` prefix using `ErpDataService`:
- `/pembelian` - PO list
- `/pembelian/create` - Create PO
- `/pembelian/{id}` - Show PO
- `/pembelian/{id}/edit` - Edit PO
- `/pembelian/{id}/pengiriman` - Pengiriman from PO
- `/pembelian/{id}/penerimaan` - GR from PO
- `/pembelian/{id}/tagihan` - PI from PO
- `/pembelian/penerimaan` - GR list
- `/pembelian/tagihan-list` - PI list
- `/pembelian/tagihan/create` - Create PI
- `/pembelian/tagihan/{id}` - Show PI