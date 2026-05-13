<nav class="md-subnav">
    @php
        $tabs = [
            ['master-data.categories',  'categories',        'fa-tag'],
            ['master-data.brands',      'brands',            'fa-certificate'],
            ['master-data.suppliers',   'suppliers',         'fa-truck'],
            ['master-data.customers',   'inventory_customers','fa-address-book'],
            ['master-data.units',       'units',             'fa-ruler'],
            ['master-data.barcodes',    'barcodes',          'fa-barcode'],
            ['master-data.batches',     'batch_lots',        'fa-layer-group'],
            ['master-data.expired',     'expired_products',  'fa-triangle-exclamation'],
            ['master-data.bin-locations','bin_locations',    'fa-location-dot'],
        ];
    @endphp
    @foreach ($tabs as [$route, $label, $icon])
        <a class="{{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">
            <i class="fa-solid {{ $icon }}"></i>
            <span>{{ __('messages.' . $label) }}</span>
        </a>
    @endforeach
</nav>
