<nav class="md-subnav">
    @php
        $tabs = [
            ['purchasing.pr',                  'purchase_request',       'fa-file-pen'],
            ['purchasing.po',                  'purchase_order',         'fa-file-invoice'],
            ['purchasing.po-approvals',        'po_approval',            'fa-circle-check'],
            ['purchasing.grn',                 'goods_receive_note',     'fa-truck-ramp-box'],
            ['purchasing.returns',             'purchase_return',        'fa-rotate-left'],
            ['purchasing.supplier-debts',      'supplier_debt',          'fa-hand-holding-dollar'],
            ['purchasing.supplier-performance','supplier_performance',   'fa-chart-bar'],
        ];
    @endphp
    @foreach ($tabs as [$route, $label, $icon])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}">
            <i class="fa-solid {{ $icon }}"></i> {{ __('messages.' . $label) }}
        </a>
    @endforeach
</nav>
