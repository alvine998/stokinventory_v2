<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'StokInventory' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
</head>
<body>
@include('partials.toasts')
@php
    $masterDataNav = [
        ['master-data.categories',   'categories',          'fa-tag'],
        ['master-data.brands',       'brands',              'fa-certificate'],
        ['master-data.suppliers',    'suppliers',           'fa-truck'],
        ['master-data.customers',    'inventory_customers', 'fa-address-book'],
        ['master-data.units',        'units',               'fa-ruler'],
        ['master-data.barcodes',     'barcodes',            'fa-barcode'],
        ['master-data.batches',      'batch_lots',          'fa-layer-group'],
        ['master-data.expired',      'expired_products',    'fa-triangle-exclamation'],
        ['master-data.bin-locations','bin_locations',       'fa-location-dot'],
    ];
@endphp

<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark"><i class="fa-solid fa-layer-group"></i></span>
            <span>StokInventory</span>
        </a>
        <nav class="nav-list">

            {{-- Overview --}}
            <p class="nav-section-label">{{ __('messages.nav_overview') }}</p>
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fa-solid fa-chart-line"></i><span>{{ __('messages.dashboard') }}</span>
            </a>

            {{-- Master Data --}}
            <p class="nav-section-label">{{ __('messages.nav_master_data') }}</p>
            @foreach ($masterDataNav as [$mdRoute, $mdLabel, $mdIcon])
                <a class="{{ request()->routeIs($mdRoute) ? 'active' : '' }}" href="{{ route($mdRoute) }}">
                    <i class="fa-solid {{ $mdIcon }}"></i>
                    <span>{{ __('messages.' . $mdLabel) }}</span>
                </a>
            @endforeach

            {{-- Inventory --}}
            <p class="nav-section-label">{{ __('messages.nav_inventory') }}</p>
            <a class="{{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                <i class="fa-solid fa-boxes-stacked"></i><span>{{ __('messages.products') }}</span>
            </a>
            <a class="{{ request()->routeIs('stock-movements.index') ? 'active' : '' }}" href="{{ route('stock-movements.index') }}">
                <i class="fa-solid fa-right-left"></i><span>{{ __('messages.stock_movements') }}</span>
            </a>
            <a class="{{ request()->routeIs('stock-opname.index') ? 'active' : '' }}" href="{{ route('stock-opname.index') }}">
                <i class="fa-solid fa-clipboard-check"></i><span>{{ __('messages.stock_opname') }}</span>
            </a>
            <a class="{{ request()->routeIs('stores.*') ? 'active' : '' }}" href="{{ route('stores.index') }}">
                <i class="fa-solid fa-store"></i><span>{{ __('messages.stores') }}</span>
            </a>
            <a class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}" href="{{ route('warehouses.index') }}">
                <i class="fa-solid fa-warehouse"></i><span>{{ __('messages.warehouses') }}</span>
            </a>
            <a class="{{ request()->routeIs('inventory.adjustments') ? 'active' : '' }}" href="{{ route('inventory.adjustments') }}">
                <i class="fa-solid fa-sliders"></i><span>{{ __('messages.stock_adjustment') }}</span>
            </a>
            <a class="{{ request()->routeIs('inventory.transfers') ? 'active' : '' }}" href="{{ route('inventory.transfers') }}">
                <i class="fa-solid fa-arrow-right-arrow-left"></i><span>{{ __('messages.transfer_warehouse') }}</span>
            </a>
            <a class="{{ request()->routeIs('inventory.history') ? 'active' : '' }}" href="{{ route('inventory.history') }}">
                <i class="fa-solid fa-clock-rotate-left"></i><span>{{ __('messages.stock_history') }}</span>
            </a>
            <a class="{{ request()->routeIs('inventory.min-stock') ? 'active' : '' }}" href="{{ route('inventory.min-stock') }}">
                <i class="fa-solid fa-triangle-exclamation"></i><span>{{ __('messages.min_stock_alert') }}</span>
            </a>
            <a class="{{ request()->routeIs('inventory.reorder-point') ? 'active' : '' }}" href="{{ route('inventory.reorder-point') }}">
                <i class="fa-solid fa-rotate"></i><span>{{ __('messages.reorder_point') }}</span>
            </a>
            <a class="{{ request()->routeIs('inventory.safety-stock') ? 'active' : '' }}" href="{{ route('inventory.safety-stock') }}">
                <i class="fa-solid fa-shield-halved"></i><span>{{ __('messages.safety_stock') }}</span>
            </a>
            <a class="{{ request()->routeIs('inventory.costing-method') ? 'active' : '' }}" href="{{ route('inventory.costing-method') }}">
                <i class="fa-solid fa-calculator"></i><span>{{ __('messages.costing_method') }}</span>
            </a>
            <a class="{{ request()->routeIs('inventory.serial-numbers') ? 'active' : '' }}" href="{{ route('inventory.serial-numbers') }}">
                <i class="fa-solid fa-barcode"></i><span>{{ __('messages.serial_number_tracking') }}</span>
            </a>

            {{-- Purchasing --}}
            <p class="nav-section-label">{{ __('messages.nav_purchasing') }}</p>
            <a class="{{ request()->routeIs('purchasing.pr') ? 'active' : '' }}" href="{{ route('purchasing.pr') }}">
                <i class="fa-solid fa-file-pen"></i><span>{{ __('messages.purchase_request') }}</span>
            </a>
            <a class="{{ request()->routeIs('purchasing.po') ? 'active' : '' }}" href="{{ route('purchasing.po') }}">
                <i class="fa-solid fa-file-invoice"></i><span>{{ __('messages.purchase_order') }}</span>
            </a>
            <a class="{{ request()->routeIs('purchasing.po-approvals') ? 'active' : '' }}" href="{{ route('purchasing.po-approvals') }}">
                <i class="fa-solid fa-circle-check"></i><span>{{ __('messages.po_approval') }}</span>
            </a>
            <a class="{{ request()->routeIs('purchasing.grn') ? 'active' : '' }}" href="{{ route('purchasing.grn') }}">
                <i class="fa-solid fa-truck-ramp-box"></i><span>{{ __('messages.goods_receive_note') }}</span>
            </a>
            <a class="{{ request()->routeIs('purchasing.returns') ? 'active' : '' }}" href="{{ route('purchasing.returns') }}">
                <i class="fa-solid fa-rotate-left"></i><span>{{ __('messages.purchase_return') }}</span>
            </a>
            <a class="{{ request()->routeIs('purchasing.supplier-debts') ? 'active' : '' }}" href="{{ route('purchasing.supplier-debts') }}">
                <i class="fa-solid fa-hand-holding-dollar"></i><span>{{ __('messages.supplier_debt') }}</span>
            </a>
            <a class="{{ request()->routeIs('purchasing.supplier-performance') ? 'active' : '' }}" href="{{ route('purchasing.supplier-performance') }}">
                <i class="fa-solid fa-chart-bar"></i><span>{{ __('messages.supplier_performance') }}</span>
            </a>

            {{-- Sales & Distribution --}}
            @if(Auth::user()->hasPermission('sales.manage'))
            <p class="nav-section-label">{{ __('messages.nav_sales') }}</p>
            <a class="{{ request()->routeIs('sales.orders') ? 'active' : '' }}" href="{{ route('sales.orders') }}">
                <i class="fa-solid fa-file-contract"></i><span>{{ __('messages.sales_order') }}</span>
            </a>
            <a class="{{ request()->routeIs('sales.delivery-orders') ? 'active' : '' }}" href="{{ route('sales.delivery-orders') }}">
                <i class="fa-solid fa-truck"></i><span>{{ __('messages.delivery_order') }}</span>
            </a>
            <a class="{{ request()->routeIs('sales.invoices') ? 'active' : '' }}" href="{{ route('sales.invoices') }}">
                <i class="fa-solid fa-file-invoice-dollar"></i><span>{{ __('messages.sales_invoice') }}</span>
            </a>
            <a class="{{ request()->routeIs('sales.returns') ? 'active' : '' }}" href="{{ route('sales.returns') }}">
                <i class="fa-solid fa-rotate-left"></i><span>{{ __('messages.sales_return') }}</span>
            </a>
            <a class="{{ request()->routeIs('sales.shipment-tracking') ? 'active' : '' }}" href="{{ route('sales.shipment-tracking') }}">
                <i class="fa-solid fa-location-dot"></i><span>{{ __('messages.shipment_tracking') }}</span>
            </a>
            <a class="{{ request()->routeIs('sales.expeditions') ? 'active' : '' }}" href="{{ route('sales.expeditions') }}">
                <i class="fa-solid fa-plane-departure"></i><span>{{ __('messages.expedition') }}</span>
            </a>
            <a class="{{ request()->routeIs('sales.price-levels') ? 'active' : '' }}" href="{{ route('sales.price-levels') }}">
                <i class="fa-solid fa-tags"></i><span>{{ __('messages.price_levels') }}</span>
            </a>
            <a class="{{ request()->routeIs('sales.customer-outstanding') ? 'active' : '' }}" href="{{ route('sales.customer-outstanding') }}">
                <i class="fa-solid fa-circle-exclamation"></i><span>{{ __('messages.customer_outstanding') }}</span>
            </a>
            @endif

            {{-- Team & Access --}}
            <p class="nav-section-label">{{ __('messages.nav_team') }}</p>
            <a class="{{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <i class="fa-solid fa-users"></i><span>{{ __('messages.users') }}</span>
            </a>
            <a class="{{ request()->routeIs('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                <i class="fa-solid fa-user-shield"></i><span>{{ __('messages.roles') }}</span>
            </a>
            <a class="{{ request()->routeIs('user-roles.index') ? 'active' : '' }}" href="{{ route('user-roles.index') }}">
                <i class="fa-solid fa-id-badge"></i><span>{{ __('messages.user_roles') }}</span>
            </a>
            @if(Auth::user()->hasPermission('team.manage'))
            <a class="{{ request()->routeIs('team-access.approval-workflows') ? 'active' : '' }}" href="{{ route('team-access.approval-workflows') }}">
                <i class="fa-solid fa-code-branch"></i><span>{{ __('messages.approval_workflow') }}</span>
            </a>
            <a class="{{ request()->routeIs('team-access.approval-requests') ? 'active' : '' }}" href="{{ route('team-access.approval-requests') }}">
                <i class="fa-solid fa-inbox"></i><span>{{ __('messages.approval_requests') }}</span>
            </a>
            <a class="{{ request()->routeIs('team-access.audit-log') ? 'active' : '' }}" href="{{ route('team-access.audit-log') }}">
                <i class="fa-solid fa-shield-halved"></i><span>{{ __('messages.audit_log') }}</span>
            </a>
            <a class="{{ request()->routeIs('team-access.login-history') ? 'active' : '' }}" href="{{ route('team-access.login-history') }}">
                <i class="fa-solid fa-right-to-bracket"></i><span>{{ __('messages.login_history') }}</span>
            </a>
            <a class="{{ request()->routeIs('team-access.activity-log') ? 'active' : '' }}" href="{{ route('team-access.activity-log') }}">
                <i class="fa-solid fa-chart-line"></i><span>{{ __('messages.activity_tracking') }}</span>
            </a>
            @endif

            {{-- Reporting & Analytics --}}
            @if(Auth::user()->hasPermission('reporting.view'))
            <p class="nav-section-label">{{ __('messages.nav_reporting') }}</p>
            <a class="{{ request()->routeIs('reporting.kpi') ? 'active' : '' }}" href="{{ route('reporting.kpi') }}">
                <i class="fa-solid fa-gauge-high"></i><span>{{ __('messages.nav_report_kpi') }}</span>
            </a>
            <a class="{{ request()->routeIs('reporting.stock') ? 'active' : '' }}" href="{{ route('reporting.stock') }}">
                <i class="fa-solid fa-warehouse"></i><span>{{ __('messages.nav_report_stock') }}</span>
            </a>
            <a class="{{ request()->routeIs('reporting.movement') ? 'active' : '' }}" href="{{ route('reporting.movement') }}">
                <i class="fa-solid fa-bolt"></i><span>{{ __('messages.nav_report_movement') }}</span>
            </a>
            <a class="{{ request()->routeIs('reporting.dead-stock') ? 'active' : '' }}" href="{{ route('reporting.dead-stock') }}">
                <i class="fa-solid fa-skull"></i><span>{{ __('messages.nav_report_dead_stock') }}</span>
            </a>
            <a class="{{ request()->routeIs('reporting.aging') ? 'active' : '' }}" href="{{ route('reporting.aging') }}">
                <i class="fa-solid fa-hourglass-half"></i><span>{{ __('messages.nav_report_aging') }}</span>
            </a>
            <a class="{{ request()->routeIs('reporting.margin') ? 'active' : '' }}" href="{{ route('reporting.margin') }}">
                <i class="fa-solid fa-tags"></i><span>{{ __('messages.nav_report_margin') }}</span>
            </a>
            <a class="{{ request()->routeIs('reporting.purchase-vs-sales') ? 'active' : '' }}" href="{{ route('reporting.purchase-vs-sales') }}">
                <i class="fa-solid fa-arrows-left-right"></i><span>{{ __('messages.nav_report_pvs') }}</span>
            </a>
            <a class="{{ request()->routeIs('reporting.forecast') ? 'active' : '' }}" href="{{ route('reporting.forecast') }}">
                <i class="fa-solid fa-wand-magic-sparkles"></i><span>{{ __('messages.nav_report_forecast') }}</span>
            </a>
            <a class="{{ request()->routeIs('reporting.inventory-value') ? 'active' : '' }}" href="{{ route('reporting.inventory-value') }}">
                <i class="fa-solid fa-coins"></i><span>{{ __('messages.nav_report_inv_value') }}</span>
            </a>
            @endif

            {{-- Finance --}}
            @if(Auth::user()->hasPermission('finance.manage'))
            <p class="nav-section-label">{{ __('messages.nav_finance') }}</p>
            <a class="{{ request()->routeIs('finance.hpp') ? 'active' : '' }}" href="{{ route('finance.hpp') }}">
                <i class="fa-solid fa-calculator"></i><span>{{ __('messages.hpp_auto') }}</span>
            </a>
            <a class="{{ request()->routeIs('finance.journals') ? 'active' : '' }}" href="{{ route('finance.journals') }}">
                <i class="fa-solid fa-book-open"></i><span>{{ __('messages.journal_auto') }}</span>
            </a>
            <a class="{{ request()->routeIs('finance.accounts') ? 'active' : '' }}" href="{{ route('finance.accounts') }}">
                <i class="fa-solid fa-sitemap"></i><span>{{ __('messages.chart_of_accounts') }}</span>
            </a>
            <a class="{{ request()->routeIs('finance.integration') ? 'active' : '' }}" href="{{ route('finance.integration') }}">
                <i class="fa-solid fa-plug"></i><span>{{ __('messages.accounting_integration') }}</span>
            </a>
            <a class="{{ request()->routeIs('finance.cashflow') ? 'active' : '' }}" href="{{ route('finance.cashflow') }}">
                <i class="fa-solid fa-water"></i><span>{{ __('messages.cashflow_inventory') }}</span>
            </a>
            <a class="{{ request()->routeIs('finance.valuation') ? 'active' : '' }}" href="{{ route('finance.valuation') }}">
                <i class="fa-solid fa-scale-balanced"></i><span>{{ __('messages.inventory_valuation') }}</span>
            </a>
            <a class="{{ request()->routeIs('finance.profit-loss') ? 'active' : '' }}" href="{{ route('finance.profit-loss') }}">
                <i class="fa-solid fa-chart-line"></i><span>{{ __('messages.profit_loss') }}</span>
            </a>
            <a class="{{ request()->routeIs('finance.tax') ? 'active' : '' }}" href="{{ route('finance.tax') }}">
                <i class="fa-solid fa-percent"></i><span>{{ __('messages.tax_ppn') }}</span>
            </a>
            @endif

            {{-- Business --}}
            <p class="nav-section-label">{{ __('messages.nav_business') }}</p>
            <a class="{{ request()->routeIs('packages.index') ? 'active' : '' }}" href="{{ route('packages.index') }}">
                <i class="fa-solid fa-cubes"></i><span>{{ __('messages.packages') }}</span>
            </a>
            <a class="{{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="fa-solid fa-file-lines"></i><span>{{ __('messages.reports') }}</span>
            </a>
            <a class="{{ request()->routeIs('company.edit') ? 'active' : '' }}" href="{{ route('company.edit') }}">
                <i class="fa-solid fa-building"></i><span>{{ __('messages.company') }}</span>
            </a>
            <a class="{{ request()->routeIs('billing.index') ? 'active' : '' }}" href="{{ route('billing.index') }}">
                <i class="fa-solid fa-file-invoice-dollar"></i><span>{{ __('messages.billing') }}</span>
            </a>

            {{-- Help --}}
            <p class="nav-section-label">Help</p>
            <a class="{{ request()->routeIs('docs.*') ? 'active' : '' }}" href="{{ route('docs.index') }}">
                <i class="fa-solid fa-book-open"></i><span>Documentation</span>
            </a>

        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <p class="eyebrow">{{ __('messages.inventory_management') }}</p>
                <h1>{{ $heading ?? 'StokInventory' }}</h1>
            </div>
            <div class="topbar-actions">
                <span class="trial-pill">
                    <i class="fa-solid fa-gift"></i>
                    {{ auth()->user()->business?->trialDaysLeft() ?? 30 }} {{ __('messages.days_left') }}
                </span>
                <a class="ghost-link" href="{{ route('locale.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}">
                    {{ strtoupper(app()->getLocale() === 'id' ? 'en' : 'id') }}
                </a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="icon-button" title="{{ __('messages.logout') }}"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
                    </form>
                @endauth
            </div>
        </header>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</div>
@stack('scripts')
<script>
(function () {
    var sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    // Restore saved scroll position
    var saved = sessionStorage.getItem('sidebarScroll');
    if (saved !== null) {
        sidebar.scrollTop = parseInt(saved, 10);
    } else {
        // First load: scroll active link into view
        var active = sidebar.querySelector('a.active');
        if (active) active.scrollIntoView({ block: 'nearest' });
    }

    // Save scroll position before any nav link is followed
    sidebar.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
            sessionStorage.setItem('sidebarScroll', sidebar.scrollTop);
        });
    });
})();
</script>
</body>
</html>
