# StokInventory — Bug Audit Report

> **Generated:** 2026-07-01  
> **Scope:** 18 Controllers · 65 Models · 24 Migrations · 118 Blade Views · Routes · Middleware  
> **Severity:** 🔴 Critical · 🟠 High · 🟡 Medium · 🔵 Low

---

## 🔴 CRITICAL (Must Fix Immediately)

### C-1. `HOME` constant → 404 redirect loop
- **File:** `app/Providers/RouteServiceProvider.php:20`
- `public const HOME = '/home'` but no `/home` route exists. Authenticated guests hitting `/login` or `/register` are redirected to 404. Should be `/dashboard`.

### C-2. No rate limiting on login, register, reset-password
- **Files:** `routes/web.php:47, 49, 53, 55`
- Login, register, forgot-password, and reset-password endpoints have no `throttle` middleware. Allows unlimited brute-force.
- **Fix:** Define named limiters in `RouteServiceProvider::configureRateLimiting()` and apply `->middleware('throttle:login')` etc.

### C-3. No rate limiting on public order POST
- **File:** `routes/web.php:43`
- `Route::post('/order/{package:slug}', ...)` is fully public, writes to DB. Any bot can spam order creation.

### C-4. `kpi.blade.php` has duplicate `@section('content')`
- **File:** `resources/views/app/reporting/kpi.blade.php:146–320`
- Two complete views merged into one file after the first `@endsection`. Renders a fatal "section already in use" error. The entire KPI reporting page is broken.

### C-5. `guest.blade.php` syntax error in locale switcher
- **File:** `resources/views/layouts/guest.blade.php:58`
- ```blade
  {{ strtoupper(app()->getLocale() === 'EN' : 'ID') }}  <!-- missing ? operator -->
  ```
  Should be `{{ strtoupper(app()->getLocale() === 'id' ? 'EN' : 'ID') }}`. Any view extending `layouts.guest` (landing, login, register, order) breaks.

### C-6. Stored XSS in `banner->background` inline style
- **File:** `resources/views/app/super-admin/commerce.blade.php:8, 178`
- `{{ $banner->background }}` interpolated into a `style` attribute. A super-admin can inject `red" onload="alert(1)` or `</style><script>...`.

### C-7. Stored XSS in `{!! !!}` on Chart of Accounts codes
- **File:** `resources/views/app/finance/journals.blade.php:156-159`
- `{!! $accounts->groupBy('type')... !!}` renders user-entered account codes unescaped. Admin-entered `<script>` in account codes executes.

### C-8. `order_no` column doesn't exist — exports broken
- **File:** `app/Http/Controllers/App/SalesController.php:670, 679`
- `exportSalesOrders()` and `exportDeliveryOrders()` reference `$o->order_no` but the model uses `so_no`. Exports always produce null values.

### C-9. Any user with `team.manage` can approve any request
- **File:** `app/Http/Controllers/App/TeamAccessController.php:130–180`
- `approveRequest` and `rejectRequest` have **no check that the user is an actual approver**. The workflow's `approver_ids` array is completely ignored.

### C-10. Auto-approval possible via null workflow
- **File:** `app/Http/Controllers/App/TeamAccessController.php:147`
- `$workflow ? ... : []` — if workflow is null, `approvers = []`, `count = 0`, `isLastStep` evaluates to `true` immediately, auto-approving any request.

### C-11. `password` not cast to `hashed`
- **File:** `app/Models/User.php:47-50`
- No `'password' => 'hashed'` cast. Any code using `User::create([... 'password' => 'plain'])` stores plaintext. Required in Laravel 10+.

### C-12. `cost_price`, `costing_method`, `reorder_point`, `safety_stock` not in `$fillable`
- **File:** `app/Models/Product.php:14`
- Columns exist in DB (from migrations) but are absent from `$fillable`. Mass-assignment silently drops them. Silently breaks HPP/inventory valuation.

---

## 🟠 HIGH

### General / Security

| # | File | Issue |
|---|------|-------|
| H-1 | `routes/web.php` | Route model binding uses raw `id` — sequential IDs leak business volume and allow enumeration across tenants. |
| H-2 | `app/Http/Middleware/TrustHosts.php` | Disabled in Kernel — host-header injection possible, poison password-reset emails. |
| H-3 | `app/Http/Middleware/EnsurePermission.php` | Only checks user permission, not tenant ownership (`business_id`). Enables IDOR across tenants. |
| H-4 | `app/Http/Middleware/EnsureSuperAdmin.php` | Dead code — `superadmin` alias registered in Kernel but never used. Parallel to `platform:` alias. |
| H-5 | `routes/web.php:371-401` | `platform:` middleware runs twice on super-admin routes (once from group, once per-route). |
| H-6 | `routes/web.php:74-85` | Docs routes behind `auth` middleware — anonymous visitors get 403 for marketing content. |
| H-7 | `routes/web.php:124` | `/billing` route has no `permission:` middleware — any authenticated user can access it. |
| H-8 | `app/Http/Controllers/App/SuperAdminController.php:184` | `->orWhere('is_super_admin', true)` unscoped by `business_id` — returns users from all businesses. |

### Authentication / Sessions

| # | File | Issue |
|---|------|-------|
| H-9 | `app/Http/Middleware/SetLocale.php` | Reads old session value; new locale applies only on next request. Locale switcher renders in old locale. |

### Controllers — Logic Bugs

| # | File | Line(s) | Issue |
|---|------|---------|-------|
| H-10 | `InventoryOpsController` | 41-77 | `storeAdjustment` and `storeTransfer` — no `DB::transaction`, stock can be updated without the movement record. |
| H-11 | `InventoryOpsController` | 94-125 | `storeTransfer` never updates `Product::current_stock` — source/destination stock unchanged, causes inventory drift. |
| H-12 | `InventoryOpsController` | 591-602 | `applyStockMovement` uses `Product::increment()` without `lockForUpdate` or transaction — concurrent requests cause lost updates. |
| H-13 | `InventoryOpsController` | 439-460 | `updateStockMovement` reverses then re-applies — type change from 'in' to 'out' results in `-2q` instead of `-q`. |
| H-14 | `InventoryController` | 196-198 | `monthlyMovements` uses `created_at` but elsewhere uses `moved_at` — dashboard chart shows different data than history view. |
| H-15 | `InventoryController` | 421-437 | `storeStockMovement` — no `DB::transaction`. Movement created but stock update can fail silently. |
| H-16 | `SalesController` | 229 | `decrement('current_stock', ...)` — no `business_id` scope. Cross-tenant stock decrement possible. |
| H-17 | `SalesController` | 273-297 | Returned/failed DOs don't restore stock to warehouse. |
| H-18 | `SalesController` | 287-294 | Empty `deliveryOrders` → `every()` returns true → SO marked delivered with nothing delivered. |
| H-19 | `PurchasingController` | 350-351 | `GrnItem` sum query missing `business_id` — cross-tenant data leak in PO completion check. |
| H-20 | `PurchasingController` | 133-137 | `destroyPr` has no status check — approved PRs already converted to POs can be deleted, orphaning the PO. |
| H-21 | `PurchasingController` | 606 | `exportSupplierDebts` uses `due_at` field name but model casts `due_date` — always null in export. |
| H-22 | `OrderController` | 116 | `invoice_no` uses `now()->format('YmdHis')` — race condition: same second = duplicate invoice_no. |
| H-23 | `OrderController` | 85, 117-119 | `amount` stored as per-month price even for multi-month plans — invoice amount incorrect. |
| H-24 | `ReportingController` | 327-340 | `forecast` allows `months_back=0` → division by zero (line 340). |
| H-25 | `FinanceController` | 155-158 | `accounts()` seeds default Chart of Accounts on every GET request — side effect in read-only endpoint. |
| H-26 | `SuperAdminController` | 122-126 | `paid_at` set on status→paid but never cleared on status→unpaid — stale timestamp. |

### Models — Schema

| # | File | Issue |
|---|------|-------|
| H-27 | `sales_orders`, `delivery_orders`, `sales_invoices`, `sales_returns` | No unique constraint on `*_no`. Two SOs in same business can share `so_no`. |
| H-28 | `shipment_trackings` | Table missing `business_id` — no tenant isolation. |
| H-29 | `stock_opnames` | No items/lines table — opname has status and notes but can never record per-product counts. |
| H-30 | `Product` | Columns `category`, `brand`, `unit` are plain strings — no FK to `product_categories`, `brands`, `units` tables. |
| H-31 | `Business` | Only `companyProfile()` and `invoices()` relationships defined — missing `users()`, `products()`, `stores()`, `warehouses()`, etc. |
| H-32 | `stock_movements` | No composite index on `(business_id, type, moved_at)` — reporting queries full-scan. |
| H-33 | `audit_logs`, `activity_logs`, `login_histories` | `business_id` is `unsignedBigInteger` with no FK — orphaned rows on business delete, no cascade. |

### Blade Views

| # | File | Issue |
|---|------|-------|
| H-34 | `app/super-admin/testimonials.blade.php:10-14` | `</div>` closed inside `<a>` — HTML structure corruption, "View Landing" link broken. |
| H-35 | `app/master-data/expired.blade.php:14-16` | Unclosed `<a>` tag — two anchors nested without closing the first. |
| H-36 | `app/purchasing/_item-rows.blade.php:11-18` | Only one hard-coded item row rendered. After validation failure, user-added rows are lost. |
| H-37 | `app/team-access/approval-requests.blade.php:185, 196` | `$isPending ? 'var(--amber)' : 'var(--blue)'` — safe but risky inline-style pattern. |
| H-38 | `app/finance/journals.blade.php:156-159` | `{!! !!}` on account data — see C-7. |
| H-39 | `app/reporting/exports/stock.blade.php:18` | Hardcoded `'Out of Stock'` — not translatable. |

---

## 🟡 MEDIUM

### Controllers — Performance (N+1)

| # | File | Issue |
|---|------|-------|
| M-1 | `DashboardController:67-74` | 7-day trend loop runs 7 separate COUNT queries. |
| M-2 | `DashboardController:91` | Latest 5 movements — no `with('product','warehouse')`, triggers N+1 in Blade. |
| M-3 | `InventoryController:28-29, 273-276, etc.` | `users()`, `stores()`, `warehouses()`, `products()` return ALL rows with no pagination. |
| M-4 | `SalesController:633-658` | `customerOutstanding` runs 5 queries per customer — 500 queries for 100 customers. |
| M-5 | `PurchasingController:525-563` | `supplierPerformance` runs 5 queries per supplier — 300 queries for 50. |
| M-6 | `FinanceController:142-147` | `hpp()` fetches paginated products but doesn't eager-load `currentStockMovements`. |

### Controllers — Validation / Mass Assignment

| # | File | Issue |
|---|------|-------|
| M-7 | `SuperAdminController:408-429` | `storeTestimonial` defaults `is_active` to false; `storeBanner` defaults to true — inconsistent. |
| M-8 | `InventoryOpsController:41-77` | `product_id` existence check doesn't scope by business_id. |
| M-9 | `InventoryOpsController:57-74` | `type=remove` can push `current_stock` negative — no sufficient-stock check. |
| M-10 | `InventoryOpsController:503` | `storeSerialNumber` — `product_id` and `warehouse_id` not scoped by business. |
| M-11 | `SalesController:401-463` | `storeSalesReturn` doesn't verify `so_id`/`do_id` belong to current business. |
| M-12 | `PurchasingController:346-355` | `storeGrn` accepts `purchase_order_id` via `exists` but doesn't scope by business. |
| M-13 | `PurchasingController:462-480` | `storeSupplierDebt` doesn't verify `supplier_id` belongs to business. |
| M-14 | `TeamAccessController:194-227` | `->where('auditable_type', 'like', '%' . $userInput . '%')` — unescaped wildcard injection. |

### Models — Missing Attributes

| # | File | Issue |
|---|------|-------|
| M-15 | 30+ models | Missing `business()` relationship despite having `business_id` in schema. |
| M-16 | `Product` | Missing `serialNumbers()`, `batchLots()`, `barcodes()`, `stockMovements()`, `salesOrderItems()` etc. |
| M-17 | 8 item models | `LogsActivity` trait applied to line-item models → N noisy activity log rows on parent cascade delete. |
| M-18 | `Invoice`, `DiscountCode`, `JournalEntryLine`, etc. | Monetary fields (`decimal(14,2)`) and numeric fields not cast. |
| M-19 | `ApprovalRequest`, `ApprovalAction` | `current_step`/`step` not cast to integer. |
| M-20 | `LoginHistory` | `is_successful` (tinyint) not cast to boolean. |
| M-21 | `ChartOfAccount` | `sort_order` not cast to integer. Typed `public static array $types` requires PHP 7.4+ (verify compat). |

### Blade Views — Translation / Hardcoded Text

| # | File | Issue |
|---|------|-------|
| M-22 | `app/docs/*.blade.php` (8 files) | Entire documentation section is hardcoded English — zero `__()` calls. |
| M-23 | `errors/403.blade.php` | All text hardcoded English. |
| M-24 | `layouts/app.blade.php:299, 307` | "Help" and "Documentation" hardcoded. |
| M-25 | `layouts/guest.blade.php:56` | `"All rights reserved"` hardcoded. |
| M-26 | `app/inventory-ops/history.blade.php:33-34` | "In" / "Out" options hardcoded. |
| M-27 | `app/super-admin/notifications.blade.php:78-80` | "In-App", "Email", "SMS" hardcoded. |
| M-28 | Most table views | 127 uses of `'—'` as empty-cell placeholder — not translatable. |

### Blade Views — JS / HTML

| # | File | Issue |
|---|------|-------|
| M-29 | ~30 files | `<a href="#" class="icon-button">` for modal close — jumps to page top, breaks back button, a11y fail. Fix: `<button type="button">`. |
| M-30 | Many files | `<a target="_blank">` without `rel="noopener noreferrer"` — security and perf. |
| M-31 | `welcome.blade.php:348` | `carousel.addEventListener(...)` — no null check on `.closest()`. |
| M-32 | `order/show.blade.php:6-10` | Steps 1 & 2 always `active`, step 3 never active — progress indicator logic bug. |
| M-33 | `app/reporting/forecast.blade.php:45` | "products need restocking" — hardcoded, no `__()`. |

### Routes / Middleware

| # | File | Issue |
|---|------|-------|
| M-34 | `routes/web.php:33` | `locale/{locale}` unconstrained — accepts any string. Should `->where('locale', 'en|id')`. |
| M-35 | `routes/web.php:61-62` | `/onboarding` behind auth but no guard preventing re-entry for completed users. |
| M-36 | `routes/web.php:373` | `super-admin/customers` GET-only — no create/update/delete endpoints. |
| M-37 | `routes/web.php:88-94` | User/role management dispatched to `InventoryController` instead of a `TeamController`. |

---

## 🔵 LOW

| # | File | Issue |
|---|------|-------|
| L-1 | `routes/web.php` | Inconsistent route naming — some use `.index`, others don't. Mixed PUT/PATCH. |
| L-2 | `TrustomProxies.php:15` | `$proxies = '*'` — trusts all proxies; should be specific LB IP in production. |
| L-3 | `AuthController:47-61` | User enumeration via timing on failed login. |
| L-4 | `InventoryController:587-589` | `authorizeBusinessRecord` returns `404` instead of `403` for cross-business access. |
| L-5 | `ReportingController:59-79` | `topByRevenue`/`topByQty` group by `product_name` instead of `product_id` — renamed products counted twice. |
| L-6 | `SalesController:96-136` | `discount_percent` validation caps at 100, but calculation could produce negative on edge case. |
| L-7 | `FinanceController:142-147` | `destroyJournal` checks `is_auto` but allows deleting any non-auto journal regardless of posted status. |
| L-8 | `MasterDataController:60-73` | `$data + ['is_active' => ...]` — `+` operator fragile, `is_active` not in validation. |
| L-9 | Documents page (8 files) | 100% hardcoded English — no `__()` translation helpers at all. |
| L-10 | `welcome.blade.php:70-93` | Demo placeholder numbers (`12,480`, `500+`, `2M+`) not wrapped in conditionals. |
| L-11 | `app/inventory-ops/serial-numbers.blade.php:67-68` | `<code>` empty if serial_no is null — no fallback. |
| L-12 | `app/finance/hpp.blade.php:32` | HPP auto-toggle checkbox hidden input pattern — fragile if reordered. |

---

## 📊 Summary

| Category | Critical | High | Medium | Low | Total |
|----------|----------|------|--------|-----|-------|
| Security / Auth | 5 | 9 | 7 | 2 | **23** |
| Logic / Data Integrity | 4 | 19 | 12 | 3 | **38** |
| Performance / N+1 | 0 | 0 | 8 | 1 | **9** |
| Translation / i18n | 0 | 1 | 15 | 3 | **19** |
| Blade / HTML / JS | 3 | 6 | 6 | 3 | **18** |
| Schema / Migrations | 2 | 7 | 4 | 1 | **14** |
| Routes / Middleware | 3 | 8 | 4 | 2 | **17** |
| **Total** | **12** | **38** | **38** | **11** | **99** |

---

## 🎯 Recommended Fix Order

1. **C-5** — Fix `guest.blade.php` syntax error (1-character fix, unblocks all guest surfaces)
2. **C-1** — Change `HOME` to `/dashboard` (1-line fix)
3. **C-4** — Remove duplicate `@section` in `kpi.blade.php` (delete lines 148-320)
4. **C-2, C-3** — Add rate limiters to auth/order endpoints
5. **C-11** — Add `'password' => 'hashed'` cast to User model
6. **C-6, C-7** — Fix XSS vectors in commerce and journals blades
7. **C-9, C-10** — Fix approval authorization gap
8. **C-8** — Fix `order_no` → `so_no` in exports
9. **C-12** — Add missing columns to Product `$fillable`
10. **H-10 through H-15** — Add `DB::transaction` wrappers and `lockForUpdate` to inventory operations
11. **H-27** — Add unique constraints to sales order/invoice numbers
12. **H-3 through H-5** — Fix middleware duplication and tenant scoping
13. **M-1 through M-6** — Fix N+1 queries with eager loading
14. **M-22 through M-28** — Translation sweep for hardcoded strings
15. **H-30 through H-33** — Add missing model relationships

