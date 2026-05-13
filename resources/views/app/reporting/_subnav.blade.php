<nav class="md-subnav">
    @php
        $tabs = [
            ['reporting.kpi',               'nav_report_kpi',          'fa-gauge-high'],
            ['reporting.stock',             'nav_report_stock',        'fa-warehouse'],
            ['reporting.movement',          'nav_report_movement',     'fa-bolt'],
            ['reporting.dead-stock',        'nav_report_dead_stock',   'fa-skull'],
            ['reporting.aging',             'nav_report_aging',        'fa-hourglass-half'],
            ['reporting.margin',            'nav_report_margin',       'fa-tags'],
            ['reporting.purchase-vs-sales', 'nav_report_pvs',          'fa-arrows-left-right'],
            ['reporting.forecast',          'nav_report_forecast',     'fa-wand-magic-sparkles'],
            ['reporting.inventory-value',   'nav_report_inv_value',    'fa-coins'],
        ];
    @endphp
    @foreach ($tabs as [$route, $label, $icon])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}">
            <i class="fa-solid {{ $icon }}"></i> {{ __('messages.' . $label) }}
        </a>
    @endforeach
</nav>
