@extends('layouts.app', ['title' => __('messages.nav_report_pvs'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-arrows-left-right"></i> {{ __('messages.nav_report_pvs') }}</h2>
        </div>
        <div class="export-group">
            <a href="{{ route('reporting.purchase-vs-sales.export', ['format'=>'excel']) }}" class="secondary-button btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
            <a href="{{ route('reporting.purchase-vs-sales.export', ['format'=>'pdf']) }}" class="secondary-button btn-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        </div>
    </div>

    @php
        $totalSales = collect($periods)->sum(fn($p) => (float)($salesRaw[$p] ?? 0));
        $totalPurch = collect($periods)->sum(fn($p) => (float)($purchasesRaw[$p] ?? 0));
        $totalDiff  = $totalSales - $totalPurch;
    @endphp

    <div class="stat-row" style="margin-bottom:20px">
        <div class="stat-card">
            <span class="stat-label" style="color:var(--teal)"><i class="fa-solid fa-arrow-up-right-from-square"></i> Total Sales (12 mo)</span>
            <span class="stat-value" style="color:var(--teal)">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label" style="color:var(--blue)"><i class="fa-solid fa-cart-flatbed"></i> Total Purchases (12 mo)</span>
            <span class="stat-value" style="color:var(--blue)">Rp {{ number_format($totalPurch, 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label" style="color:{{ $totalDiff >= 0 ? 'var(--teal)' : 'var(--rose)' }}"><i class="fa-solid fa-scale-balanced"></i> Net Difference</span>
            <span class="stat-value" style="color:{{ $totalDiff >= 0 ? 'var(--teal)' : 'var(--rose)' }}">
                {{ $totalDiff >= 0 ? '+' : '' }}Rp {{ number_format($totalDiff, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <div class="chart-card">
        <h4><i class="fa-solid fa-chart-bar" style="color:var(--teal)"></i> {{ __('messages.nav_report_pvs') }} — Last 12 Months</h4>
        <canvas id="pvsChart" height="90"></canvas>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.period') }}</th>
                    <th class="num" style="color:var(--teal)">{{ __('messages.sales_value') }}</th>
                    <th class="num" style="color:var(--blue)">{{ __('messages.purchase_value') }}</th>
                    <th class="num">{{ __('messages.difference') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($periods as $p)
            @php
                $sales     = (float) ($salesRaw[$p] ?? 0);
                $purchases = (float) ($purchasesRaw[$p] ?? 0);
                $diff      = $sales - $purchases;
            @endphp
            <tr>
                <td><strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $p)->translatedFormat('F Y') }}</strong></td>
                <td class="num" style="color:var(--teal)">Rp {{ number_format($sales, 0, ',', '.') }}</td>
                <td class="num" style="color:var(--blue)">Rp {{ number_format($purchases, 0, ',', '.') }}</td>
                <td class="num" style="font-weight:700;color:{{ $diff >= 0 ? 'var(--teal)' : 'var(--rose)' }}">
                    {{ $diff >= 0 ? '+' : '' }}Rp {{ number_format($diff, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr style="font-weight:700;border-top:2px solid #e3ecef">
                <td>{{ __('messages.total') }}</td>
                <td class="num" style="color:var(--teal)">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                <td class="num" style="color:var(--blue)">Rp {{ number_format($totalPurch, 0, ',', '.') }}</td>
                <td class="num" style="color:{{ $totalDiff >= 0 ? 'var(--teal)' : 'var(--rose)' }}">
                    {{ $totalDiff >= 0 ? '+' : '' }}Rp {{ number_format($totalDiff, 0, ',', '.') }}
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('pvsChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(collect($periods)->map(fn($p) => \Carbon\Carbon::createFromFormat('Y-m', $p)->format('M Y'))->values()) !!},
        datasets: [
            {
                label: '{{ __('messages.sales_value') }}',
                data: {!! json_encode(collect($periods)->map(fn($p) => (float)($salesRaw[$p] ?? 0))->values()) !!},
                backgroundColor: 'rgba(13,148,136,.75)',
                borderRadius: 5,
            },
            {
                label: '{{ __('messages.purchase_value') }}',
                data: {!! json_encode(collect($periods)->map(fn($p) => (float)($purchasesRaw[$p] ?? 0))->values()) !!},
                backgroundColor: 'rgba(37,99,235,.6)',
                borderRadius: 5,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
        },
        scales: {
            x: { grid: { display: false } },
            y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') }, grid: { color: '#f0f4f6' } }
        }
    }
});
</script>
@endpush
