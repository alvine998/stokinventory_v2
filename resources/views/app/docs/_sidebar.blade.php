<aside class="docs-sidebar">
    <p class="docs-sidebar-title">Documentation</p>
    <a class="{{ request()->routeIs('docs.index') ? 'active' : '' }}" href="{{ route('docs.index') }}">
        <i class="fa-solid fa-house"></i> Overview
    </a>
    <a class="{{ request()->routeIs('docs.getting-started') ? 'active' : '' }}" href="{{ route('docs.getting-started') }}">
        <i class="fa-solid fa-rocket"></i> Getting Started
    </a>
    <a class="{{ request()->routeIs('docs.master-data') ? 'active' : '' }}" href="{{ route('docs.master-data') }}">
        <i class="fa-solid fa-database"></i> Master Data
    </a>
    <a class="{{ request()->routeIs('docs.products') ? 'active' : '' }}" href="{{ route('docs.products') }}">
        <i class="fa-solid fa-boxes-stacked"></i> Products & Stock
    </a>
    <a class="{{ request()->routeIs('docs.inventory') ? 'active' : '' }}" href="{{ route('docs.inventory') }}">
        <i class="fa-solid fa-sliders"></i> Inventory Operations
    </a>
    <a class="{{ request()->routeIs('docs.purchasing') ? 'active' : '' }}" href="{{ route('docs.purchasing') }}">
        <i class="fa-solid fa-cart-shopping"></i> Purchasing
    </a>
    <a class="{{ request()->routeIs('docs.sales') ? 'active' : '' }}" href="{{ route('docs.sales') }}">
        <i class="fa-solid fa-bag-shopping"></i> Sales & Distribution
    </a>
    <a class="{{ request()->routeIs('docs.finance') ? 'active' : '' }}" href="{{ route('docs.finance') }}">
        <i class="fa-solid fa-landmark"></i> Finance & Accounting
    </a>
    <a class="{{ request()->routeIs('docs.reporting') ? 'active' : '' }}" href="{{ route('docs.reporting') }}">
        <i class="fa-solid fa-chart-bar"></i> Reporting & Analytics
    </a>
    <a class="{{ request()->routeIs('docs.team-access') ? 'active' : '' }}" href="{{ route('docs.team-access') }}">
        <i class="fa-solid fa-user-lock"></i> Team & Access
    </a>
</aside>
