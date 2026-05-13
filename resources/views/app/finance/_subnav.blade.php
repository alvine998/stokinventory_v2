<nav class="md-subnav">
    @php
        $tabs = [
            ['finance.hpp',         'hpp_auto',            'fa-calculator'],
            ['finance.journals',    'journal_auto',        'fa-book-open'],
            ['finance.accounts',    'chart_of_accounts',   'fa-sitemap'],
            ['finance.integration', 'accounting_integration', 'fa-plug'],
            ['finance.cashflow',    'cashflow_inventory',  'fa-water'],
            ['finance.valuation',   'inventory_valuation', 'fa-scale-balanced'],
            ['finance.profit-loss', 'profit_loss',         'fa-chart-line'],
            ['finance.tax',         'tax_ppn',             'fa-percent'],
        ];
    @endphp
    @foreach ($tabs as [$route, $label, $icon])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}">
            <i class="fa-solid {{ $icon }}"></i> {{ __('messages.' . $label) }}
        </a>
    @endforeach
</nav>
