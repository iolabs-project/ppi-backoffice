# Purchasing Module Context

## Overview
The Purchasing module handles the complete procurement lifecycle: **Purchase Orders → Goods Receipts → Purchase Invoices**. It's built with Laravel and follows a service-layer architecture with controllers, services, models, and enums.

---

## Module Structure

### Controllers (`app/Http/Controllers/Purchasing/`)
| Controller | Responsibility |
|------------|----------------|
| `PurchaseOrderController` | CRUD for Purchase Orders, status transitions (draft → open → closed/cancelled) |
| `GoodsReceiptController` | CRUD for Goods Receipts, draft/finished/cancelled status, inventory integration |
| `PurchaseInvoiceController` | CRUD for Purchase Invoices, draft/open/partial/paid/cancelled status |

### Services (`app/Services/Purchasing/`)
| Service (file) | Class | Responsibility |
|----------------|-------|----------------|
| `PurchaseOrderService.php` | `PurchaseOrderService` | PO number generation, data fetching (with sums for quantities), create/update (items + costs in transaction), status changes, PO items for GR |
| `GoodsReceiptService.php` | `GoodsReceiptService` | GR number generation, data fetching, create from PO, update (draft save vs finalize), batch validation, unit cost calc, ProductBatch/InventoryTransaction creation, PO updates, GR items for PI |
| `PurchaseInvoiceService.php` | `PurchasingService` (aliased as `PurchasingService`) | PI number generation, data fetching, create from PO, update (items + costs + OPEN side effects), cancel with PO reversal |

### Models (`app/Models/`)
| Model | Table | Key Relationships |
|-------|-------|-------------------|
| `PurchaseOrder` | `purchase_orders` | items, costs, supplier, warehouse, goodsReceipts, creator |
| `PurchaseOrderItem` | `purchase_order_items` | product, purchaseOrder |
| `PurchaseOrderCost` | `purchase_order_costs` | account, purchaseOrder |
| `GoodsReceipt` | `goods_receipts` | items, costs, purchaseOrder, supplier, warehouse, creator |
| `GoodsReceiptItem` | `goods_receipt_items` | product, purchaseOrderItem, goodsReceipt |
| `GoodsReceiptCost` | `goods_receipt_costs` | account, goodsReceipt |
| `PurchaseInvoice` | `purchase_invoices` | items, costs, purchaseOrder, supplier, warehouse, creator |
| `PurchaseInvoiceItem` | `purchase_invoice_items` | product, purchaseOrderItem, goodsReceiptItem, purchaseInvoice |
| `PurchaseInvoiceCost` | `purchase_invoice_costs` | account, purchaseInvoice |

### Enums (`app/Enums/`)
| Enum | Values |
|------|--------|
| `PurchaseOrderStatus` | `draft`, `open`, `closed`, `cancelled` |
| `GoodsReceiptStatus` | `draft`, `finished`, `cancelled` |
| `PurchaseInvoiceStatus` | `draft`, `open`, `partial`, `paid`, `cancelled` |
| `PaymentTerm` | `net_7` (7 days), `net_14` (14), `net_30` (30), `net_45` (45) |
| `AccountCategory` | `CASH_BANK` (used for down payment account selection) |

---

## Database Schema (Migrations)

### Purchase Orders
```sql
purchase_orders:
- id, company_id, supplier_id, warehouse_id
- number (unique), reference_number
- order_date, due_date
- payment_terms (enum: net_7, net_14, net_30, net_45)
- status (enum: draft, open, closed, cancelled)
- subtotal, discount_percentage, discount_amount
- tax_percentage, tax_amount
- transport_cost, other_cost
- down_payment_amount, down_payment_remaining_amount, down_payment_account_id
- total_amount, note, created_by
- timestamps

purchase_order_items:
- id, purchase_order_id, product_id
- quantity, received_quantity, invoiced_quantity
- unit_price, subtotal, discount_percentage, discount_amount, total_amount
- timestamps

purchase_order_costs:
- id, purchase_order_id, account_id, description, amount
- billed_by (enum: supplier, third_party, internal)
- is_inventory_cost (boolean)
- timestamps
- index on (purchase_order_id, account_id)
```

### Goods Receipts
```sql
goods_receipts:
- id, company_id, purchase_order_id, supplier_id, warehouse_id
- number (unique), reference_number
- receipt_date
- status (enum: draft, finished, cancelled)
- subtotal, discount_percentage, discount_amount
- note, created_by
- timestamps

goods_receipt_items:
- id, goods_receipt_id, purchase_order_item_id, product_id
- batch_number, expected_quantity, shrinkage_quantity, received_quantity
- unit_price, subtotal, discount_percentage, discount_amount
- unit_cost, total_amount
- timestamps

goods_receipt_costs:
- id, goods_receipt_id, account_id, description, amount
- billed_by (enum: supplier, third_party, internal)
- timestamps
```

### Purchase Invoices
```sql
purchase_invoices:
- id, company_id, purchase_order_id, supplier_id, warehouse_id
- number (unique), reference_number
- invoice_date, due_date
- payment_terms (enum), status (enum: draft, open, partial, paid, cancelled)
- subtotal, discount_percentage, discount_amount
- tax_percentage, tax_amount
- down_payment_amount, total_amount, remaining_amount, note, created_by
- timestamps

purchase_invoice_items:
- id, purchase_invoice_id, purchase_order_item_id, goods_receipt_item_id, product_id
- quantity, unit_price, subtotal, discount_percentage, discount_amount, total_amount
- timestamps

purchase_invoice_costs:
- id, purchase_invoice_id, account_id, description, amount
- timestamps
```

---

## Routes (`routes/web.php`)

### Purchase Orders (`purchasings.purchase_orders.*`)
| Method | URI | Action |
|--------|-----|--------|
| GET | `/purchasings/purchase-orders` | index |
| GET | `/purchasings/purchase-orders/datatable` | datatable |
| GET | `/purchasings/purchase-orders/create` | create |
| POST | `/purchasings/purchase-orders` | store |
| GET | `/purchasings/purchase-orders/{id}` | show |
| GET | `/purchasings/purchase-orders/{id}/edit` | edit |
| PUT | `/purchasings/purchase-orders/{id}` | update |
| POST | `/purchasings/purchase-orders/{id}/open` | open |
| POST | `/purchasings/purchase-orders/{id}/close` | close |
| POST | `/purchasings/purchase-orders/{id}/cancel` | cancel |

### Goods Receipts (`purchasings.goods_receipts.*`)
| Method | URI | Action |
|--------|-----|--------|
| GET | `/purchasings/goods-receipts` | index |
| GET | `/purchasings/goods-receipts/datatable` | datatable |
| POST | `/purchasings/goods-receipts` | store |
| GET | `/purchasings/goods-receipts/{id}` | show |
| GET | `/purchasings/goods-receipts/{id}/edit` | edit |
| PUT | `/purchasings/goods-receipts/{id}` | update |
| POST | `/purchasings/goods-receipts/{id}/cancel` | cancel |

### Purchase Invoices (`purchasings.purchase_invoices.*`)
| Method | URI | Action |
|--------|-----|--------|
| GET | `/purchasings/purchase-invoices` | index |
| GET | `/purchasings/purchase-invoices/datatable` | datatable |
| POST | `/purchasings/purchase-invoices` | store |
| GET | `/purchasings/purchase-invoices/{id}` | show |
| GET | `/purchasings/purchase-invoices/{id}/edit` | edit |
| PUT | `/purchasings/purchase-invoices/{id}` | update |
| POST | `/purchasings/purchase-invoices/{id}/cancel` | cancel |

---

## Business Flow

### 1. Purchase Order (PO)
```
Draft → Open → Closed (when all items received) / Cancelled
```
- **Draft**: Can be edited freely, minimal validation
- **Open**: Full validation required (supplier, warehouse, items, payment terms)
- **Closed**: Auto-set when all PO items fully received
- **Cancelled**: Manual action

**Number Format**: `PO-{COMPANY_CODE}-{YEAR}-{SEQUENCE}` (e.g., `PO-PPI-2026-0001`)

### 2. Goods Receipt (GR)
```
Draft → Finished → (auto-updates PO received_quantity, creates InventoryTransaction & ProductBatch)
```
- Created from a PO (copies supplier, warehouse; stores with draft status)
- **Draft**: Can edit items, batch numbers optional
- **Finished**: 
  - Validates unique batch numbers per product per company
  - Calculates unit_cost (allocates additional costs & discounts proportionally by quantity/value ratio)
  - Creates `GoodsReceiptCost` for each cost item
  - Creates `ProductBatch` for each item
  - Creates `InventoryTransaction` (type=purchase, direction=1) with stock_before/stock_after tracking
  - Increments `PurchaseOrderItem.received_quantity`
  - Auto-closes PO if all items have `received_quantity >= quantity`
  - TODO: Create drafted cost invoice for costs with billed_by ≠ 'supplier'

**Number Format**: `GR-{COMPANY_CODE}-{YEAR}-{SEQUENCE}`

### 3. Purchase Invoice (PI)
```
Draft → Open → Partial / Paid / Cancelled
```
- Created from a PO (copies supplier, warehouse, payment_terms, tax/discount percentages; stores with draft status)
- **Draft**: Can edit freely
- **Open**: Full validation, links to GR items
  - Increments `PurchaseOrderItem.invoiced_quantity` for each detail item
  - Decrements `PurchaseOrder.down_payment_remaining_amount` by down_payment_amount
- **Partial**: Some payment received
- **Paid**: Fully paid
- **Cancelled**: Only allowed if Draft or Open with no payments (total_amount == remaining_amount)
  - Reverses `PurchaseOrderItem.invoiced_quantity` (decrements for each item)
  - Restores `PurchaseOrder.down_payment_remaining_amount`

**Number Format**: `PI-{COMPANY_CODE}-{YEAR}-{SEQUENCE}`

---

## Key Service Methods

### Purchase Order (`PurchaseOrderService`)
| Method | Description |
|--------|-------------|
| `generatePONumber()` | Generates unique PO number |
| `fetchPurchaseOrderTableData(Request)` | Paginated list with search/filter, includes withSum for total_quantity, total_received_quantity, total_invoiced_quantity |
| `fetchPurchaseOrderByID(int)` | Full PO with items, costs, relationships (unit, creator) |
| `storePurchaseOrder(Request)` | Creates PO + items + costs in transaction; calculates subtotal, discount, tax, total_amount |
| `updatePurchaseOrder(Request, int)` | Updates PO + replaces items + costs (delete all + recreate) |
| `changePurchaseOrderStatus(int, string)` | Simple status update (used by open/close/cancel) |
| `fetchPOItemsForGoodsReceipt(int)` | PO items with remaining quantities for GR; maps product_code, product_name, unit |

### Goods Receipt (`GoodsReceiptService`)
| Method | Description |
|--------|-------------|
| `generateGoodsReceiptNumber()` | Generates unique GR number |
| `fetchGoodsReceiptTableData(Request)` | Paginated list with search/filter, includes withSum for total_received_quantity, total_shrinkage_quantity |
| `storeGoodsReceipt(Request)` | Creates GR from PO (draft status, copies company/supplier/warehouse from PO) |
| `fetchGoodsReceiptByID(int)` | Full GR with items, costs, purchaseOrderItem, PO item reference |
| `updateGoodsReceipt(Request, int)` | Updates GR; delegates to `saveDraftGoodsReceipt()` or `finalizeGoodsReceipt()` based on status |
| `saveDraftGoodsReceipt()` | Saves items/costs without inventory impact; calculates shrinkage = expected - received |
| `finalizeGoodsReceipt()` | **Core logic**: batch validation, unit_cost calculation (qtyRatio/valueRatio), creates ProductBatch, InventoryTransaction (with stock_before/stock_after), updates PO received_quantity, auto-closes PO |
| `validateBatchNumber(string, int, int): bool` | Checks if batch_number already exists per product per company |
| `changeGoodsReceiptStatus(int, string)` | Simple status update |
| `fetchGRItemsForPurchaseInvoice(int)` | Finished GR items for PI creation; filters by purchase_order_id and finished status |

### Purchase Invoice (`PurchasingService`)
| Method | Description |
|--------|-------------|
| `generatePurchaseInvoiceNumber()` | Generates unique PI number |
| `fetchPurchaseInvoiceTableData(Request)` | Paginated list with search/filter |
| `storePurchaseInvoice(Request)` | Creates PI from PO (draft status); copies payment_terms, discount/tax percentages |
| `updatePurchaseInvoice(Request, int)` | Updates PI + items + costs (delete all + recreate); if status=OPEN: increments PO invoiced_quantity, decrements down_payment_remaining_amount |
| `fetchPurchaseInvoiceByID(int)` | Full PI with items, costs, purchaseOrder, GR item references |
| `cancelPurchaseInvoice(int)` | Cancels PI; validates allowed status (Draft or Open with no payments), reverses PO invoiced_quantity & restores down_payment_remaining_amount |

---

## Validation Rules

### Purchase Order Store/Update
- **Open status**: All fields required (supplier_id, warehouse_id, number, order_date, payment_terms, details min 1 with product_id, quantity, unit_price)
- **Draft status**: Only supplier_id, warehouse_id, number, order_date required
- Costs: account_id + amount + billed_by required together (required_with on any cost)
- `billed_by` enum: `supplier`, `third_party`, `internal`
- Number uniqueness check against `purchase_orders.number`

### Goods Receipt Update (non-draft)
- Required: receipt_date, status (draft|finished), details (min 1)
- Each detail: purchase_order_item_id, product_id, batch_number (required), received_quantity, expected_quantity, unit_price
- batch_number: required for non-draft
- Costs: account_id + amount + billed_by required together
- `billed_by` enum: `supplier`, `third_party`, `internal`
- Validation error messages in Indonesian

### Purchase Invoice Update (non-draft)
- Required: invoice_date, due_date (>= invoice_date), status=open, payment_terms, details (min 1)
- Each detail: goods_receipt_item_id, purchase_order_item_id, product_id, quantity, unit_price
- Costs: account_id + amount required together
- Validation error messages in Indonesian

---

## Inventory Integration (Goods Receipt Finalize)

When GR status changes to **Finished** (in `finalizeGoodsReceipt`):

1. **Costs Creation**: Creates `GoodsReceiptCost` records for each cost item
2. **Batch Validation**: Checks `GoodsReceiptItem.batch_number` unique per product per company using `validateBatchNumber()`
3. **Unit Cost Calculation**:
   ```
   qtyRatio = qty / totalQty
   valueRatio = (total) / totalSubtotal
   additionalCostPerUnit = (additionalCost * qtyRatio) / qty
   discountAmountPerUnit = (discountAmount * valueRatio) / qty
   unitCost = unitPrice - (unitDiscountAmount / qty) + additionalCostPerUnit - discountAmountPerUnit
   ```
4. **Creates ProductBatch**: Links to GR item, stores quantity & unit_cost
5. **Creates InventoryTransaction**:
   - type: 'purchase', direction: 1 (in)
   - stock_before: latest transaction's stock_after (or 0 if none)
   - stock_after: stock_before + received_quantity
   - reference: GoodsReceipt (polymorphic)
   - note: 'Penerimaan Barang dari GR #{number}'
6. **Updates PO**: Increments `PurchaseOrderItem.received_quantity`
7. **Auto-closes PO**: If all items have `received_quantity >= quantity` (uses `whereDoesntHave` check)
8. **TODO**: Create drafted cost invoice for costs where `billed_by` ≠ 'supplier'

---

## Cost Handling

### Cost Fields
- **PurchaseOrderCost**: `billed_by` (supplier/third_party/internal), `is_inventory_cost` (boolean)
- **GoodsReceiptCost**: `billed_by` (supplier/third_party/internal) — only created during finalize
- **PurchaseInvoiceCost**: Simple account_id, description, amount

### Cost Flow
```
PO Costs (all categories, with billed_by & is_inventory_cost)
    ↓
GR Costs (created during finalize; copies all PO costs with billed_by)
    ↓
PI Costs (all categories from PO; created during PI update)
```

### Key Behaviors
- GR only creates costs during `finalizeGoodsReceipt()` (not during draft save)
- GR costs copy `billed_by` from PO costs
- PI costs are created fresh during PI update (not copied from GR)
- TODO in GR finalize: Create drafted cost invoice for costs where `billed_by` ≠ 'supplier'

---

## Configuration

### Context Config (`config/context.php`)
```php
'selected_company_id' => 1
```
Used for number generation and company-scoped queries.

---

## Views (`resources/views/purchasing/`)

### Purchase Order
- `index.blade.php` - List with datatable
- `create.blade.php` - Form with products, warehouses, suppliers, cash/bank accounts
- `edit.blade.php` - Edit form
- `show.blade.php` - Detail view

### Goods Receipt
- `index.blade.php` - List with datatable
- `edit.blade.php` - Edit form with remaining PO items (uses `fetchPOItemsForGoodsReceipt`)
- `show.blade.php` - Detail view

### Purchase Invoice
- `index.blade.php` - List with datatable
- `edit.blade.php` - Edit form with payment terms, remaining GR items (uses `fetchGRItemsForPurchaseInvoice`)
- (No create/show view - created from PO via store action, redirects to edit)

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

---

## Key Business Rules

1. **PO → GR → PI Chain**: Each document references the previous one (GR references PO, PI references PO and GR items)
2. **Quantity Tracking**: 
   - PO: `quantity` → `received_quantity` (via GR) → `invoiced_quantity` (via PI)
   - GR: `expected_quantity` vs `received_quantity` → `shrinkage_quantity`
3. **Down Payment**: PO tracks `down_payment_amount` & `down_payment_remaining_amount`; PI reduces remaining on OPEN and restores on Cancel
4. **Batch Tracking**: Required on GR finish (validateBatchNumber); creates ProductBatch for inventory valuation
5. **Unit Cost Calculation**: Allocates additional costs proportionally by quantity ratio and discount proportionally by value ratio during GR finalize
6. **Auto-close PO**: When all items have `received_quantity >= quantity` (checked via `whereDoesntHave`)
7. **PI Status Effects on OPEN**:
   - Increments `PurchaseOrderItem.invoiced_quantity` for each item
   - Decrements `PurchaseOrder.down_payment_remaining_amount` by down_payment_amount
8. **Cancellation Rules**:
   - PO: Simple status update (any status can be changed)
   - GR: Simple status update (inventory reversal handled separately if finished)
   - PI: Only Draft or Open with total_amount == remaining_amount (no payments received)
     - Reverses PO invoiced_quantity (decrements)
     - Restores PO down_payment_remaining_amount

---

## Dependencies
- **Master Data**: Products, Contacts (suppliers), Warehouses, ChartOfAccounts, Units
- **Auth**: `auth()->user()->id` for created_by
- **Context**: `config('context.selected_company_id')` for company scoping
- **Inventory**: InventoryTransaction, ProductBatch models
- **Enums**: All status/payment term enums (PurchaseOrderStatus, GoodsReceiptStatus, PurchaseInvoiceStatus, PaymentTerm, AccountCategory)
- **Services**: PurchaseOrderService, GoodsReceiptService injected via DI
- **Models referenced**: PurchaseOrder, PurchaseOrderItem, PurchaseOrderCost, GoodsReceipt, GoodsReceiptItem, GoodsReceiptCost, PurchaseInvoice, PurchaseInvoiceItem, PurchaseInvoiceCost, Company

---

## Testing Notes
- Uses Pest PHP (`tests/Pest.php`, `tests/TestCase.php`)
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`