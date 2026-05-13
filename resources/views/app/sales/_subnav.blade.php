<nav class="md-subnav">
    @php
        $tabs = [
            ['sales.orders',              'sales_order',            'fa-file-contract'],
            ['sales.delivery-orders',     'delivery_order',         'fa-truck'],
            ['sales.invoices',            'sales_invoice',          'fa-file-invoice-dollar'],
            ['sales.returns',             'sales_return',           'fa-rotate-left'],
            ['sales.shipment-tracking',   'shipment_tracking',      'fa-location-dot'],
            ['sales.expeditions',         'expedition',             'fa-plane-departure'],
            ['sales.price-levels',        'price_level',            'fa-tags'],
            ['sales.customer-outstanding','customer_outstanding',   'fa-circle-exclamation'],
        ];
    @endphp
    @foreach ($tabs as [$route, $label, $icon])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}">
            <i class="fa-solid {{ $icon }}"></i> {{ __('messages.' . $label) }}
        </a>
    @endforeach
</nav>
