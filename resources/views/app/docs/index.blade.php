@extends('layouts.app', ['title' => 'Documentation', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Help Center</p>
            <h2><i class="fa-solid fa-book-open"></i> Documentation</h2>
            <p>Step-by-step guides and requirements for every feature in StokInventory. Choose a module below to get started.</p>
        </div>

        <div class="docs-index-grid">
            <a href="{{ route('docs.getting-started') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--teal) 12%,transparent);color:var(--teal)">
                    <i class="fa-solid fa-rocket"></i>
                </div>
                <h3>Getting Started</h3>
                <p>Initial setup, account registration, onboarding, and understanding the permission system.</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>

            <a href="{{ route('docs.master-data') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--violet) 12%,transparent);color:var(--violet)">
                    <i class="fa-solid fa-database"></i>
                </div>
                <h3>Master Data</h3>
                <p>Categories, brands, suppliers, customers, units, barcodes, batch lots, and bin locations.</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>

            <a href="{{ route('docs.products') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--blue) 12%,transparent);color:var(--blue)">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <h3>Products & Stock</h3>
                <p>Product catalog, stock movements, and physical stock count (opname).</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>

            <a href="{{ route('docs.inventory') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--cyan) 12%,transparent);color:var(--cyan)">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <h3>Inventory Operations</h3>
                <p>Adjustments, warehouse transfers, reorder points, safety stock, costing, and serial numbers.</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>

            <a href="{{ route('docs.purchasing') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--amber) 12%,transparent);color:#b45309">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <h3>Purchasing</h3>
                <p>Purchase requests, orders, approvals, goods receiving, supplier returns, and debt tracking.</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>

            <a href="{{ route('docs.sales') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--rose) 12%,transparent);color:var(--rose)">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <h3>Sales & Distribution</h3>
                <p>Sales orders, delivery, invoicing, returns, shipment tracking, couriers, and price levels.</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>

            <a href="{{ route('docs.finance') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--teal) 12%,transparent);color:var(--teal)">
                    <i class="fa-solid fa-landmark"></i>
                </div>
                <h3>Finance & Accounting</h3>
                <p>HPP/COGS, journal entries, chart of accounts, cashflow, valuation, P&L, and tax management.</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>

            <a href="{{ route('docs.reporting') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--deep) 12%,transparent);color:var(--deep)">
                    <i class="fa-solid fa-chart-bar"></i>
                </div>
                <h3>Reporting & Analytics</h3>
                <p>KPI dashboard, stock reports, margin, forecasting, and export to PDF or Excel.</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>

            <a href="{{ route('docs.team-access') }}" class="docs-index-card">
                <div class="card-icon" style="background:color-mix(in srgb,var(--violet) 12%,transparent);color:var(--violet)">
                    <i class="fa-solid fa-user-lock"></i>
                </div>
                <h3>Team & Access</h3>
                <p>Approval workflows, audit log, login history, and activity tracking.</p>
                <span class="card-arrow"><i class="fa-solid fa-arrow-right"></i> Read guide</span>
            </a>
        </div>
    </div>
</div>
@endsection
