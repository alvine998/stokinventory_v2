<nav class="md-subnav">
    @php
        $tabs = [
            ['inventory.adjustments',   'stock_adjustment',        'fa-sliders'],
            ['inventory.transfers',     'transfer_warehouse',      'fa-arrow-right-arrow-left'],
            ['inventory.history',       'stock_history',           'fa-clock-rotate-left'],
            ['inventory.min-stock',     'min_stock_alert',         'fa-triangle-exclamation'],
            ['inventory.reorder-point', 'reorder_point',           'fa-rotate'],
            ['inventory.safety-stock',  'safety_stock',            'fa-shield-halved'],
            ['inventory.costing-method','costing_method',          'fa-calculator'],
            ['inventory.serial-numbers','serial_number_tracking',  'fa-barcode'],
        ];
    @endphp
    @foreach ($tabs as [$route, $label, $icon])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}">
            <i class="fa-solid {{ $icon }}"></i> {{ __('messages.' . $label) }}
        </a>
    @endforeach
</nav>
