<?php

use App\Http\Controllers\App\ExpiredController;
use App\Http\Controllers\App\CompanySettingsController;
use App\Http\Controllers\App\BillingController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\InventoryController;
use App\Http\Controllers\App\InventoryOpsController;
use App\Http\Controllers\App\MasterDataController;
use App\Http\Controllers\App\PurchasingController;
use App\Http\Controllers\App\SalesController;
use App\Http\Controllers\App\FinanceController;
use App\Http\Controllers\App\ReportingController;
use App\Http\Controllers\App\DocsController;
use App\Http\Controllers\App\TeamAccessController;
use App\Http\Controllers\App\OnboardingController;
use App\Http\Controllers\App\OrderController;
use App\Http\Controllers\App\SuperAdminController;
use App\Http\Controllers\App\SupportController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'id'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');

Route::get('/', [DashboardController::class, 'landing'])->name('landing');
Route::middleware('throttle:order')->group(function () {
    Route::get('/order/{package:slug}', [OrderController::class, 'show'])->name('order.show');
    Route::post('/order/{package:slug}', [OrderController::class, 'store'])->name('order.store');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    // Forgot / Reset password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/expired', [ExpiredController::class, 'show'])->middleware('auth')->name('expired');

Route::middleware(['auth', 'subscription.active'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Support tickets (customer)
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/',                              [SupportController::class, 'index'])->name('index');
        Route::post('/',                             [SupportController::class, 'store'])->name('store');
        Route::get('/{room}',                        [SupportController::class, 'show'])->name('show');
        Route::post('/{room}/reply',                 [SupportController::class, 'reply'])->name('reply');
        Route::post('/{room}/close',                 [SupportController::class, 'close'])->name('close');
        Route::post('/{room}/reopen',                [SupportController::class, 'reopen'])->name('reopen');
    });

    Route::prefix('docs')->name('docs.')->group(function () {
        Route::get('/',                [DocsController::class, 'index'])->name('index');
        Route::get('/getting-started', [DocsController::class, 'gettingStarted'])->name('getting-started');
        Route::get('/master-data',     [DocsController::class, 'masterData'])->name('master-data');
        Route::get('/products',        [DocsController::class, 'products'])->name('products');
        Route::get('/inventory',       [DocsController::class, 'inventory'])->name('inventory');
        Route::get('/purchasing',      [DocsController::class, 'purchasing'])->name('purchasing');
        Route::get('/sales',           [DocsController::class, 'sales'])->name('sales');
        Route::get('/finance',         [DocsController::class, 'finance'])->name('finance');
        Route::get('/reporting',       [DocsController::class, 'reporting'])->name('reporting');
        Route::get('/team-access',     [DocsController::class, 'teamAccess'])->name('team-access');
    });

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/users', [InventoryController::class, 'users'])->middleware('permission:users.manage')->name('users.index');
    Route::post('/users', [InventoryController::class, 'storeUser'])->middleware('permission:users.manage')->name('users.store');
    Route::put('/users/{user}', [InventoryController::class, 'updateUser'])->middleware('permission:users.manage')->name('users.update');
    Route::get('/roles', [InventoryController::class, 'roles'])->middleware('permission:roles.manage')->name('roles.index');
    Route::post('/roles', [InventoryController::class, 'storeRole'])->middleware('permission:roles.manage')->name('roles.store');
    Route::get('/user-roles', [InventoryController::class, 'userRoles'])->middleware('permission:roles.manage')->name('user-roles.index');
    Route::post('/user-roles', [InventoryController::class, 'storeUserRole'])->middleware('permission:roles.manage')->name('user-roles.store');
    Route::get('/reports', [InventoryController::class, 'reports'])->middleware('permission:reports.view')->name('reports.index');
    Route::get('/stock-opname', [InventoryController::class, 'stockOpname'])->middleware('permission:stock.manage')->name('stock-opname.index');
    Route::post('/stock-opname', [InventoryController::class, 'storeStockOpname'])->middleware('permission:stock.manage')->name('stock-opname.store');
    Route::put('/stock-opname/{stockOpname}', [InventoryController::class, 'updateStockOpname'])->middleware('permission:stock.manage')->name('stock-opname.update');
    Route::get('/stores', [InventoryController::class, 'stores'])->middleware('permission:stores.manage')->name('stores.index');
    Route::get('/stores/create', [InventoryController::class, 'createStore'])->middleware('permission:stores.manage')->name('stores.create');
    Route::post('/stores', [InventoryController::class, 'storeStore'])->middleware('permission:stores.manage')->name('stores.store');
    Route::get('/stores/{store}/edit', [InventoryController::class, 'editStore'])->middleware('permission:stores.manage')->name('stores.edit');
    Route::put('/stores/{store}', [InventoryController::class, 'updateStore'])->middleware('permission:stores.manage')->name('stores.update');
    Route::get('/warehouses', [InventoryController::class, 'warehouses'])->middleware('permission:warehouses.manage')->name('warehouses.index');
    Route::get('/warehouses/create', [InventoryController::class, 'createWarehouse'])->middleware('permission:warehouses.manage')->name('warehouses.create');
    Route::post('/warehouses', [InventoryController::class, 'storeWarehouse'])->middleware('permission:warehouses.manage')->name('warehouses.store');
    Route::get('/warehouses/{warehouse}/edit', [InventoryController::class, 'editWarehouse'])->middleware('permission:warehouses.manage')->name('warehouses.edit');
    Route::put('/warehouses/{warehouse}', [InventoryController::class, 'updateWarehouse'])->middleware('permission:warehouses.manage')->name('warehouses.update');
    Route::get('/products', [InventoryController::class, 'products'])->middleware('permission:products.manage')->name('products.index');
    Route::get('/products/create', [InventoryController::class, 'createProduct'])->middleware('permission:products.manage')->name('products.create');
    Route::post('/products', [InventoryController::class, 'storeProduct'])->middleware('permission:products.manage')->name('products.store');
    Route::get('/products/{product}/edit', [InventoryController::class, 'editProduct'])->middleware('permission:products.manage')->name('products.edit');
    Route::put('/products/{product}', [InventoryController::class, 'updateProduct'])->middleware('permission:products.manage')->name('products.update');
    Route::get('/products/export', [InventoryController::class, 'exportProducts'])->middleware('permission:products.manage')->name('products.export');
    Route::post('/products/import', [InventoryController::class, 'importProducts'])->middleware('permission:products.manage')->name('products.import');
    Route::get('/stock-movements', [InventoryController::class, 'stockMovements'])->middleware('permission:stock.manage')->name('stock-movements.index');
    Route::post('/stock-movements', [InventoryController::class, 'storeStockMovement'])->middleware('permission:stock.manage')->name('stock-movements.store');
    Route::put('/stock-movements/{stockMovement}', [InventoryController::class, 'updateStockMovement'])->middleware('permission:stock.manage')->name('stock-movements.update');
    Route::get('/packages', [InventoryController::class, 'packages'])->middleware('permission:packages.manage')->name('packages.index');
    Route::post('/packages', [InventoryController::class, 'storePackage'])->middleware('permission:packages.manage')->name('packages.store');
    Route::put('/packages/{package}', [InventoryController::class, 'updatePackage'])->middleware('permission:packages.manage')->name('packages.update');
    Route::get('/settings/company', [CompanySettingsController::class, 'edit'])->middleware('permission:company.manage')->name('company.edit');
    Route::post('/settings/company', [CompanySettingsController::class, 'update'])->middleware('permission:company.manage')->name('company.update');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');

    Route::middleware('permission:master_data.manage')->prefix('master-data')->name('master-data.')->group(function () {
        Route::get('/categories', [MasterDataController::class, 'categories'])->name('categories');
        Route::post('/categories', [MasterDataController::class, 'storeCategory'])->name('categories.store');
        Route::patch('/categories/{category}', [MasterDataController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [MasterDataController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('/categories/export', [MasterDataController::class, 'exportCategories'])->name('categories.export');
        Route::post('/categories/import', [MasterDataController::class, 'importCategories'])->name('categories.import');

        Route::get('/brands', [MasterDataController::class, 'brands'])->name('brands');
        Route::post('/brands', [MasterDataController::class, 'storeBrand'])->name('brands.store');
        Route::patch('/brands/{brand}', [MasterDataController::class, 'updateBrand'])->name('brands.update');
        Route::delete('/brands/{brand}', [MasterDataController::class, 'destroyBrand'])->name('brands.destroy');
        Route::get('/brands/export', [MasterDataController::class, 'exportBrands'])->name('brands.export');
        Route::post('/brands/import', [MasterDataController::class, 'importBrands'])->name('brands.import');

        Route::get('/suppliers', [MasterDataController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers', [MasterDataController::class, 'storeSupplier'])->name('suppliers.store');
        Route::patch('/suppliers/{supplier}', [MasterDataController::class, 'updateSupplier'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [MasterDataController::class, 'destroySupplier'])->name('suppliers.destroy');
        Route::get('/suppliers/export', [MasterDataController::class, 'exportSuppliers'])->name('suppliers.export');
        Route::post('/suppliers/import', [MasterDataController::class, 'importSuppliers'])->name('suppliers.import');

        Route::get('/customers', [MasterDataController::class, 'inventoryCustomers'])->name('customers');
        Route::post('/customers', [MasterDataController::class, 'storeCustomer'])->name('customers.store');
        Route::patch('/customers/{customer}', [MasterDataController::class, 'updateCustomer'])->name('customers.update');
        Route::delete('/customers/{customer}', [MasterDataController::class, 'destroyCustomer'])->name('customers.destroy');
        Route::get('/customers/export', [MasterDataController::class, 'exportCustomers'])->name('customers.export');
        Route::post('/customers/import', [MasterDataController::class, 'importCustomers'])->name('customers.import');

        Route::get('/units', [MasterDataController::class, 'units'])->name('units');
        Route::post('/units', [MasterDataController::class, 'storeUnit'])->name('units.store');
        Route::patch('/units/{unit}', [MasterDataController::class, 'updateUnit'])->name('units.update');
        Route::delete('/units/{unit}', [MasterDataController::class, 'destroyUnit'])->name('units.destroy');
        Route::get('/units/export', [MasterDataController::class, 'exportUnits'])->name('units.export');
        Route::post('/units/import', [MasterDataController::class, 'importUnits'])->name('units.import');

        Route::get('/barcodes', [MasterDataController::class, 'barcodes'])->name('barcodes');
        Route::post('/barcodes', [MasterDataController::class, 'storeBarcode'])->name('barcodes.store');
        Route::patch('/barcodes/{productBarcode}', [MasterDataController::class, 'updateBarcode'])->name('barcodes.update');
        Route::delete('/barcodes/{productBarcode}', [MasterDataController::class, 'destroyBarcode'])->name('barcodes.destroy');
        Route::get('/barcodes/export', [MasterDataController::class, 'exportBarcodes'])->name('barcodes.export');

        Route::get('/batches', [MasterDataController::class, 'batches'])->name('batches');
        Route::post('/batches', [MasterDataController::class, 'storeBatch'])->name('batches.store');
        Route::patch('/batches/{batchLot}', [MasterDataController::class, 'updateBatch'])->name('batches.update');
        Route::delete('/batches/{batchLot}', [MasterDataController::class, 'destroyBatch'])->name('batches.destroy');
        Route::get('/batches/export', [MasterDataController::class, 'exportBatches'])->name('batches.export');
        Route::post('/batches/import', [MasterDataController::class, 'importBatches'])->name('batches.import');

        Route::get('/expired', [MasterDataController::class, 'expired'])->name('expired');
        Route::get('/expired/export', [MasterDataController::class, 'exportExpired'])->name('expired.export');

        Route::get('/bin-locations', [MasterDataController::class, 'binLocations'])->name('bin-locations');
        Route::post('/bin-locations', [MasterDataController::class, 'storeBinLocation'])->name('bin-locations.store');
        Route::patch('/bin-locations/{binLocation}', [MasterDataController::class, 'updateBinLocation'])->name('bin-locations.update');
        Route::delete('/bin-locations/{binLocation}', [MasterDataController::class, 'destroyBinLocation'])->name('bin-locations.destroy');
        Route::get('/bin-locations/export', [MasterDataController::class, 'exportBinLocations'])->name('bin-locations.export');
        Route::post('/bin-locations/import', [MasterDataController::class, 'importBinLocations'])->name('bin-locations.import');
    });

    Route::middleware('permission:inventory_ops.manage')->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/adjustments', [InventoryOpsController::class, 'adjustments'])->name('adjustments');
        Route::post('/adjustments', [InventoryOpsController::class, 'storeAdjustment'])->name('adjustments.store');
        Route::get('/adjustments/export', [InventoryOpsController::class, 'exportAdjustments'])->name('adjustments.export');

        Route::get('/transfers', [InventoryOpsController::class, 'transfers'])->name('transfers');
        Route::post('/transfers', [InventoryOpsController::class, 'storeTransfer'])->name('transfers.store');
        Route::get('/transfers/export', [InventoryOpsController::class, 'exportTransfers'])->name('transfers.export');

        Route::get('/history', [InventoryOpsController::class, 'history'])->name('history');
        Route::get('/history/export', [InventoryOpsController::class, 'exportHistory'])->name('history.export');

        Route::get('/min-stock', [InventoryOpsController::class, 'minStockAlert'])->name('min-stock');
        Route::get('/min-stock/export', [InventoryOpsController::class, 'exportMinStock'])->name('min-stock.export');

        Route::get('/reorder-point', [InventoryOpsController::class, 'reorderPoint'])->name('reorder-point');
        Route::patch('/reorder-point/{product}', [InventoryOpsController::class, 'updateReorderPoint'])->name('reorder-point.update');
        Route::get('/reorder-point/export', [InventoryOpsController::class, 'exportReorderPoint'])->name('reorder-point.export');
        Route::post('/reorder-point/import', [InventoryOpsController::class, 'importReorderPoint'])->name('reorder-point.import');

        Route::get('/safety-stock', [InventoryOpsController::class, 'safetyStock'])->name('safety-stock');
        Route::patch('/safety-stock/{product}', [InventoryOpsController::class, 'updateSafetyStock'])->name('safety-stock.update');
        Route::get('/safety-stock/export', [InventoryOpsController::class, 'exportSafetyStock'])->name('safety-stock.export');
        Route::post('/safety-stock/import', [InventoryOpsController::class, 'importSafetyStock'])->name('safety-stock.import');

        Route::get('/costing-method', [InventoryOpsController::class, 'costingMethod'])->name('costing-method');
        Route::patch('/costing-method/{product}', [InventoryOpsController::class, 'updateCostingMethod'])->name('costing-method.update');
        Route::get('/costing-method/export', [InventoryOpsController::class, 'exportCostingMethod'])->name('costing-method.export');
        Route::post('/costing-method/import', [InventoryOpsController::class, 'importCostingMethod'])->name('costing-method.import');

        Route::get('/serial-numbers', [InventoryOpsController::class, 'serialNumbers'])->name('serial-numbers');
        Route::post('/serial-numbers', [InventoryOpsController::class, 'storeSerialNumber'])->name('serial-numbers.store');
        Route::patch('/serial-numbers/{serialNumber}', [InventoryOpsController::class, 'updateSerialNumber'])->name('serial-numbers.update');
        Route::delete('/serial-numbers/{serialNumber}', [InventoryOpsController::class, 'destroySerialNumber'])->name('serial-numbers.destroy');
        Route::get('/serial-numbers/export', [InventoryOpsController::class, 'exportSerialNumbers'])->name('serial-numbers.export');
        Route::post('/serial-numbers/import', [InventoryOpsController::class, 'importSerialNumbers'])->name('serial-numbers.import');
    });

    Route::middleware('permission:purchasing.manage')->prefix('purchasing')->name('purchasing.')->group(function () {
        Route::get('/pr', [PurchasingController::class, 'pr'])->name('pr');
        Route::post('/pr', [PurchasingController::class, 'storePr'])->name('pr.store');
        Route::patch('/pr/{purchaseRequest}/status', [PurchasingController::class, 'updatePrStatus'])->name('pr.status');
        Route::delete('/pr/{purchaseRequest}', [PurchasingController::class, 'destroyPr'])->name('pr.destroy');
        Route::get('/pr/export', [PurchasingController::class, 'exportPr'])->name('pr.export');

        Route::get('/po', [PurchasingController::class, 'po'])->name('po');
        Route::post('/po', [PurchasingController::class, 'storePo'])->name('po.store');
        Route::delete('/po/{purchaseOrder}', [PurchasingController::class, 'destroyPo'])->name('po.destroy');
        Route::get('/po/export', [PurchasingController::class, 'exportPo'])->name('po.export');

        Route::get('/po-approvals', [PurchasingController::class, 'poApprovals'])->name('po-approvals');
        Route::post('/po-approvals/{purchaseOrder}/approve', [PurchasingController::class, 'approvePo'])->name('po-approvals.approve');
        Route::post('/po-approvals/{purchaseOrder}/reject', [PurchasingController::class, 'rejectPo'])->name('po-approvals.reject');

        Route::get('/grn', [PurchasingController::class, 'grn'])->name('grn');
        Route::post('/grn', [PurchasingController::class, 'storeGrn'])->name('grn.store');
        Route::get('/grn/export', [PurchasingController::class, 'exportGrn'])->name('grn.export');

        Route::get('/returns', [PurchasingController::class, 'purchaseReturns'])->name('returns');
        Route::post('/returns', [PurchasingController::class, 'storePurchaseReturn'])->name('returns.store');
        Route::get('/returns/export', [PurchasingController::class, 'exportPurchaseReturns'])->name('returns.export');

        Route::get('/supplier-debts', [PurchasingController::class, 'supplierDebts'])->name('supplier-debts');
        Route::post('/supplier-debts', [PurchasingController::class, 'storeSupplierDebt'])->name('supplier-debts.store');
        Route::patch('/supplier-debts/{supplierDebt}', [PurchasingController::class, 'updateSupplierDebt'])->name('supplier-debts.update');
        Route::get('/supplier-debts/export', [PurchasingController::class, 'exportSupplierDebts'])->name('supplier-debts.export');

        Route::get('/supplier-performance', [PurchasingController::class, 'supplierPerformance'])->name('supplier-performance');
        Route::get('/supplier-performance/export', [PurchasingController::class, 'exportSupplierPerformance'])->name('supplier-performance.export');
    });

    Route::middleware('permission:sales.manage')->prefix('sales')->name('sales.')->group(function () {
        Route::get('/orders', [SalesController::class, 'salesOrders'])->name('orders');
        Route::post('/orders', [SalesController::class, 'storeSalesOrder'])->name('orders.store');
        Route::patch('/orders/{salesOrder}/status', [SalesController::class, 'updateSoStatus'])->name('orders.status');
        Route::delete('/orders/{salesOrder}', [SalesController::class, 'destroySo'])->name('orders.destroy');
        Route::get('/orders/export', [SalesController::class, 'exportSalesOrders'])->name('orders.export');

        Route::get('/delivery-orders', [SalesController::class, 'deliveryOrders'])->name('delivery-orders');
        Route::post('/delivery-orders', [SalesController::class, 'storeDo'])->name('delivery-orders.store');
        Route::patch('/delivery-orders/{deliveryOrder}/status', [SalesController::class, 'updateDoStatus'])->name('delivery-orders.status');
        Route::get('/delivery-orders/export', [SalesController::class, 'exportDeliveryOrders'])->name('delivery-orders.export');

        Route::get('/invoices', [SalesController::class, 'salesInvoices'])->name('invoices');
        Route::post('/invoices', [SalesController::class, 'storeSalesInvoice'])->name('invoices.store');
        Route::patch('/invoices/{salesInvoice}', [SalesController::class, 'updateSalesInvoice'])->name('invoices.update');
        Route::get('/invoices/export', [SalesController::class, 'exportInvoices'])->name('invoices.export');

        Route::get('/returns', [SalesController::class, 'salesReturns'])->name('returns');
        Route::post('/returns', [SalesController::class, 'storeSalesReturn'])->name('returns.store');
        Route::get('/returns/export', [SalesController::class, 'exportSalesReturns'])->name('returns.export');

        Route::get('/shipment-tracking', [SalesController::class, 'shipmentTracking'])->name('shipment-tracking');
        Route::post('/shipment-tracking/{deliveryOrder}', [SalesController::class, 'storeTracking'])->name('shipment-tracking.store');
        Route::get('/shipment-tracking/export', [SalesController::class, 'exportShipmentTracking'])->name('shipment-tracking.export');

        Route::get('/expeditions', [SalesController::class, 'expeditions'])->name('expeditions');
        Route::post('/expeditions', [SalesController::class, 'storeExpedition'])->name('expeditions.store');
        Route::patch('/expeditions/{expedition}', [SalesController::class, 'updateExpedition'])->name('expeditions.update');
        Route::delete('/expeditions/{expedition}', [SalesController::class, 'destroyExpedition'])->name('expeditions.destroy');
        Route::get('/expeditions/export', [SalesController::class, 'exportExpeditions'])->name('expeditions.export');
        Route::post('/expeditions/import', [SalesController::class, 'importExpeditions'])->name('expeditions.import');

        Route::get('/price-levels', [SalesController::class, 'priceLevels'])->name('price-levels');
        Route::post('/price-levels', [SalesController::class, 'storePriceLevel'])->name('price-levels.store');
        Route::patch('/price-levels/{priceLevel}', [SalesController::class, 'updatePriceLevel'])->name('price-levels.update');
        Route::delete('/price-levels/{priceLevel}', [SalesController::class, 'destroyPriceLevel'])->name('price-levels.destroy');
        Route::get('/price-levels/export', [SalesController::class, 'exportPriceLevels'])->name('price-levels.export');
        Route::post('/price-levels/import', [SalesController::class, 'importPriceLevels'])->name('price-levels.import');

        Route::get('/customer-outstanding', [SalesController::class, 'customerOutstanding'])->name('customer-outstanding');
        Route::get('/customer-outstanding/export', [SalesController::class, 'exportCustomerOutstanding'])->name('customer-outstanding.export');
    });

    Route::middleware('permission:finance.manage')->prefix('finance')->name('finance.')->group(function () {
        Route::get('/hpp', [FinanceController::class, 'hpp'])->name('hpp');
        Route::post('/hpp', [FinanceController::class, 'updateHpp'])->name('hpp.update');
        Route::patch('/hpp/product/{product}', [FinanceController::class, 'updateProductCost'])->name('hpp.product');

        Route::get('/journals', [FinanceController::class, 'journals'])->name('journals');
        Route::post('/journals', [FinanceController::class, 'storeJournal'])->name('journals.store');
        Route::delete('/journals/{journalEntry}', [FinanceController::class, 'destroyJournal'])->name('journals.destroy');
        Route::get('/journals/export', [FinanceController::class, 'exportJournals'])->name('journals.export');

        Route::get('/accounts', [FinanceController::class, 'accounts'])->name('accounts');
        Route::post('/accounts', [FinanceController::class, 'storeAccount'])->name('accounts.store');
        Route::patch('/accounts/{chartOfAccount}', [FinanceController::class, 'updateAccount'])->name('accounts.update');
        Route::delete('/accounts/{chartOfAccount}', [FinanceController::class, 'destroyAccount'])->name('accounts.destroy');
        Route::get('/accounts/export', [FinanceController::class, 'exportAccounts'])->name('accounts.export');
        Route::post('/accounts/import', [FinanceController::class, 'importAccounts'])->name('accounts.import');

        Route::get('/integration', [FinanceController::class, 'integration'])->name('integration');
        Route::post('/integration', [FinanceController::class, 'saveIntegration'])->name('integration.save');

        Route::get('/cashflow', [FinanceController::class, 'cashflow'])->name('cashflow');

        Route::get('/valuation', [FinanceController::class, 'valuation'])->name('valuation');
        Route::get('/valuation/export', [FinanceController::class, 'exportValuation'])->name('valuation.export');

        Route::get('/profit-loss', [FinanceController::class, 'profitLoss'])->name('profit-loss');

        Route::get('/tax', [FinanceController::class, 'tax'])->name('tax');
        Route::post('/tax', [FinanceController::class, 'storeTax'])->name('tax.store');
        Route::patch('/tax/{taxConfig}', [FinanceController::class, 'updateTax'])->name('tax.update');
        Route::delete('/tax/{taxConfig}', [FinanceController::class, 'destroyTax'])->name('tax.destroy');
        Route::get('/tax/export', [FinanceController::class, 'exportTax'])->name('tax.export');
        Route::post('/tax/import', [FinanceController::class, 'importTax'])->name('tax.import');
    });

    Route::middleware('permission:reporting.view')->prefix('reporting')->name('reporting.')->group(function () {
        Route::get('/kpi',                        [ReportingController::class, 'kpi'])->name('kpi');
        Route::post('/kpi/target',                [ReportingController::class, 'storeKpiTarget'])->name('kpi.target');
        Route::get('/kpi/export',                 [ReportingController::class, 'kpiExport'])->name('kpi.export');
        Route::get('/stock',                      [ReportingController::class, 'stockReport'])->name('stock');
        Route::get('/stock/export',               [ReportingController::class, 'stockExport'])->name('stock.export');
        Route::get('/movement',                   [ReportingController::class, 'movement'])->name('movement');
        Route::get('/movement/export',            [ReportingController::class, 'movementExport'])->name('movement.export');
        Route::get('/dead-stock',                 [ReportingController::class, 'deadStock'])->name('dead-stock');
        Route::get('/dead-stock/export',          [ReportingController::class, 'deadStockExport'])->name('dead-stock.export');
        Route::get('/aging',                      [ReportingController::class, 'stockAging'])->name('aging');
        Route::get('/aging/export',               [ReportingController::class, 'agingExport'])->name('aging.export');
        Route::get('/margin',                     [ReportingController::class, 'marginReport'])->name('margin');
        Route::get('/margin/export',              [ReportingController::class, 'marginExport'])->name('margin.export');
        Route::get('/purchase-vs-sales',          [ReportingController::class, 'purchaseVsSales'])->name('purchase-vs-sales');
        Route::get('/purchase-vs-sales/export',   [ReportingController::class, 'purchaseVsSalesExport'])->name('purchase-vs-sales.export');
        Route::get('/forecast',                   [ReportingController::class, 'forecast'])->name('forecast');
        Route::get('/forecast/export',            [ReportingController::class, 'forecastExport'])->name('forecast.export');
        Route::get('/inventory-value',            [ReportingController::class, 'inventoryValue'])->name('inventory-value');
        Route::get('/inventory-value/export',     [ReportingController::class, 'inventoryValueExport'])->name('inventory-value.export');
    });

    Route::middleware('permission:team.manage')->prefix('team-access')->name('team-access.')->group(function () {
        Route::get('/approval-workflows',                  [TeamAccessController::class, 'approvalWorkflows'])->name('approval-workflows');
        Route::post('/approval-workflows',                 [TeamAccessController::class, 'storeWorkflow'])->name('approval-workflows.store');
        Route::put('/approval-workflows/{approvalWorkflow}', [TeamAccessController::class, 'updateWorkflow'])->name('approval-workflows.update');
        Route::delete('/approval-workflows/{approvalWorkflow}', [TeamAccessController::class, 'destroyWorkflow'])->name('approval-workflows.destroy');
        Route::get('/approval-requests',                   [TeamAccessController::class, 'approvalRequests'])->name('approval-requests');
        Route::post('/approval-requests/{approvalRequest}/approve', [TeamAccessController::class, 'approveRequest'])->name('approval-requests.approve');
        Route::post('/approval-requests/{approvalRequest}/reject',  [TeamAccessController::class, 'rejectRequest'])->name('approval-requests.reject');
        Route::post('/approval-requests/{approvalRequest}/cancel',  [TeamAccessController::class, 'cancelRequest'])->name('approval-requests.cancel');
        Route::get('/audit-log',                           [TeamAccessController::class, 'auditLog'])->name('audit-log');
        Route::get('/login-history',                       [TeamAccessController::class, 'loginHistory'])->name('login-history');
        Route::get('/activity-log',                        [TeamAccessController::class, 'activityLog'])->name('activity-log');
    });

    Route::middleware('platform:super_admin,platform_admin,customer_service')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/customers', [SuperAdminController::class, 'customers'])->middleware('platform:super_admin,platform_admin')->name('customers');
        Route::get('/cms', [SuperAdminController::class, 'cms'])->middleware('platform:super_admin,platform_admin')->name('cms');
        Route::post('/cms', [SuperAdminController::class, 'storeCms'])->middleware('platform:super_admin,platform_admin')->name('cms.store');
        Route::patch('/cms/{page}', [SuperAdminController::class, 'updateCms'])->middleware('platform:super_admin,platform_admin')->name('cms.update');
        Route::get('/notifications', [SuperAdminController::class, 'notifications'])->middleware('platform:super_admin,platform_admin')->name('notifications');
        Route::post('/notifications', [SuperAdminController::class, 'storeNotification'])->middleware('platform:super_admin,platform_admin')->name('notifications.store');
        Route::get('/billing-payments', [SuperAdminController::class, 'billingPayments'])->middleware('platform:super_admin,platform_admin')->name('billing-payments');
        Route::patch('/billing-payments/{invoice}', [SuperAdminController::class, 'updateBillingPayment'])->middleware('platform:super_admin,platform_admin')->name('billing-payments.update');
        Route::get('/bank-accounts', [SuperAdminController::class, 'bankAccounts'])->middleware('platform:super_admin,platform_admin')->name('bank-accounts');
        Route::post('/bank-accounts', [SuperAdminController::class, 'storeBankAccount'])->middleware('platform:super_admin,platform_admin')->name('bank-accounts.store');
        Route::patch('/bank-accounts/{bankAccount}/toggle', [SuperAdminController::class, 'toggleBankAccount'])->middleware('platform:super_admin,platform_admin')->name('bank-accounts.toggle');
        Route::get('/support-rooms', [SuperAdminController::class, 'supportRooms'])->name('support-rooms');
        Route::post('/support-rooms', [SuperAdminController::class, 'storeSupportRoom'])->middleware('platform:super_admin,platform_admin')->name('support-rooms.store');
        Route::post('/support-rooms/{room}/reply', [SuperAdminController::class, 'replySupportRoom'])->name('support-rooms.reply');
        Route::post('/support-rooms/{room}/close', [SuperAdminController::class, 'closeSupportRoom'])->name('support-rooms.close');
        Route::post('/support-rooms/{room}/reopen', [SuperAdminController::class, 'reopenSupportRoom'])->name('support-rooms.reopen');
        Route::get('/commerce', [SuperAdminController::class, 'commerce'])->middleware('platform:super_admin,platform_admin')->name('commerce');
        Route::post('/packages', [SuperAdminController::class, 'storePackage'])->middleware('platform:super_admin,platform_admin')->name('packages.store');
        Route::patch('/packages/{package}', [SuperAdminController::class, 'updatePackage'])->middleware('platform:super_admin,platform_admin')->name('packages.update');
        Route::patch('/packages/{package}/toggle', [SuperAdminController::class, 'togglePackage'])->middleware('platform:super_admin,platform_admin')->name('packages.toggle');
        Route::delete('/packages/{package}', [SuperAdminController::class, 'destroyPackage'])->middleware('platform:super_admin,platform_admin')->name('packages.destroy');
        Route::post('/promo-banners', [SuperAdminController::class, 'storeBanner'])->middleware('platform:super_admin,platform_admin')->name('promo-banners.store');
        Route::patch('/promo-banners/{banner}', [SuperAdminController::class, 'updateBanner'])->middleware('platform:super_admin,platform_admin')->name('promo-banners.update');
        Route::patch('/promo-banners/{banner}/toggle', [SuperAdminController::class, 'toggleBanner'])->middleware('platform:super_admin,platform_admin')->name('promo-banners.toggle');
        Route::delete('/promo-banners/{banner}', [SuperAdminController::class, 'destroyBanner'])->middleware('platform:super_admin,platform_admin')->name('promo-banners.destroy');
        Route::get('/testimonials', [SuperAdminController::class, 'testimonials'])->middleware('platform:super_admin,platform_admin')->name('testimonials');
        Route::post('/testimonials', [SuperAdminController::class, 'storeTestimonial'])->middleware('platform:super_admin,platform_admin')->name('testimonials.store');
        Route::patch('/testimonials/{testimonial}', [SuperAdminController::class, 'updateTestimonial'])->middleware('platform:super_admin,platform_admin')->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [SuperAdminController::class, 'destroyTestimonial'])->middleware('platform:super_admin,platform_admin')->name('testimonials.destroy');
    });
});
