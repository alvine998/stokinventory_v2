@extends('layouts.app', ['title' => __('messages.nav_report_kpi'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-gauge-high"></i> {{ __('messages.nav_report_kpi') }}</h2>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <a href="#modal-set-target" class="secondary-button"><i class="fa-solid fa-bullseye"></i> {{ __('messages.set_target') }}</a>
            <div class="export-group">
                <a href="{{ route('reporting.kpi.export', array_merge(request()->query(), ['format'=>'excel'])) }}" class="secondary-button btn-excel" title="Export Excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
                <a href="{{ route('reporting.kpi.export', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="secondary-button btn-pdf" title="Export PDF"><i class="fa-solid fa-file-pdf"></i> PDF</a>
            </div>
        </div>
    </div>

    {{-- Period selector --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('reporting.kpi') }}" style="display:contents">
            <label>
                <span class="label-cap">{{ __('messages.year') }}</span>
                <select name="year">
                    @for ($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </label>
            <label>
                <span class="label-cap">{{ __('messages.month') }}</span>
                <select name="month">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                    @endfor
                </select>
            </label>
            <button class="primary-button">{{ __('messages.apply') }}</button>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="stat-row" style="margin-bottom:28px">
        @php
            $kpis = [
                ['revenue',         __('messages.revenue'),               'Rp '.number_format($revenue,0,',','.'),        $targets['revenue'] ?? null,          'fa-money-bill-wave', 'var(--teal)'],
                ['orders',          __('messages.total_orders'),          number_format($totalOrders),                    $targets['orders'] ?? null,           'fa-cart-shopping',   'var(--blue)'],
                ['avg_order_value', __('messages.avg_order_value'),       'Rp '.number_format($avgOrderValue,0,',','.'),  null,                                 'fa-receipt',         'var(--violet)'],
                ['gross_profit',    __('messages.gross_profit'),          'Rp '.number_format($grossProfit,0,',','.'),    $targets['gross_profit'] ?? null,     'fa-chart-line',      $grossProfit >= 0 ? 'var(--teal)' : 'var(--rose)'],
                ['inventory_value', __('messages.total_inventory_value'), 'Rp '.number_format($inventoryValue,0,',','.'), $targets['inventory_value'] ?? null,  'fa-warehouse',       'var(--amber)'],
            ];
        @endphp
        @foreach ($kpis as [$key, $label, $value, $target, $icon, $color])
        <div class="stat-card">
            <span class="stat-label"><i class="fa-solid {{ $icon }}" style="color:{{ $color }}"></i> {{ $label }}</span>
            <span class="stat-value" style="color:{{ $color }}">{{ $value }}</span>
            @if ($target)
            @php
                $rawNum   = (float) preg_replace('/[^0-9]/', '', $value);
                $actual   = $key === 'orders' ? $totalOrders : ($key === 'avg_order_value' ? $avgOrderValue : $rawNum);
                $pct      = $target->target_value > 0 ? round($actual / $target->target_value * 100) : 0;
                $pctColor = $pct >= 100 ? 'var(--teal)' : ($pct >= 70 ? 'var(--amber)' : 'var(--rose)');
            @endphp
            <div style="margin-top:8px">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#8fa4ae;margin-bottom:4px">
                    <span>Target: Rp {{ number_format($target->target_value,0,',','.') }}</span>
                    <span style="font-weight:700;color:{{ $pctColor }}">{{ $pct }}%</span>
                </div>
                <div style="height:6px;background:#e8f0f2;border-radius:3px;overflow:hidden">
                    <div style="width:{{ min($pct,100) }}%;height:100%;background:{{ $pctColor }};border-radius:3px"></div>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Revenue Trend --}}
    <div class="chart-card">
        <h4><i class="fa-solid fa-chart-bar" style="color:var(--teal)"></i> {{ __('messages.revenue_trend_6m') }}</h4>
        <canvas id="revenueTrend" height="90"></canvas>
    </div>

    {{-- Top Products --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="chart-card" style="margin-bottom:0">
            <h4><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> {{ __('messages.top_by_revenue') }}</h4>
            @forelse ($topByRevenue as $i => $p)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #eef2f4;font-size:13px">
                <div style="display:flex;align-items:center;gap:9px">
                    <span style="width:22px;height:22px;border-radius:50%;background:{{ $i===0?'var(--amber)':($i===1?'#9ca3af':($i===2?'#c68642':'#e8f0f2')) }};color:{{ $i<3?'#fff':'#888' }};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $i+1 }}</span>
                    <span>{{ $p->product_name }}</span>
                </div>
                <span style="font-weight:700;color:var(--teal);white-space:nowrap;margin-left:8px">Rp {{ number_format($p->total_revenue,0,',','.') }}</span>
            </div>
            @empty<p style="color:#aaa;font-size:13px">{{ __('messages.no_data') }}</p>@endforelse
        </div>
        <div class="chart-card" style="margin-bottom:0">
            <h4><i class="fa-solid fa-box" style="color:var(--blue)"></i> {{ __('messages.top_by_qty') }}</h4>
            @forelse ($topByQty as $i => $p)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #eef2f4;font-size:13px">
                <div style="display:flex;align-items:center;gap:9px">
                    <span style="width:22px;height:22px;border-radius:50%;background:{{ $i===0?'var(--blue)':($i===1?'#9ca3af':'#e8f0f2') }};color:{{ $i<2?'#fff':'#888' }};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $i+1 }}</span>
                    <span>{{ $p->product_name }}</span>
                </div>
                <span style="font-weight:700;color:var(--blue);white-space:nowrap;margin-left:8px">{{ number_format($p->total_qty,2) }} pcs</span>
            </div>
            @empty<p style="color:#aaa;font-size:13px">{{ __('messages.no_data') }}</p>@endforelse
        </div>
    </div>
</section>

{{-- Set Target Modal --}}
<div class="modal-overlay" id="modal-set-target">
    <div class="modal" style="max-width:440px">
        <div class="modal-head">
            <h3>{{ __('messages.set_target') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('reporting.kpi.target') }}" class="modal-body">
            @csrf
            <input type="hidden" name="year"  value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="form-grid two">
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.metric') }}</span>
                    <select name="metric" required>
                        <option value="revenue">{{ __('messages.revenue') }}</option>
                        <option value="orders">{{ __('messages.total_orders') }}</option>
                        <option value="gross_profit">{{ __('messages.gross_profit') }}</option>
                        <option value="inventory_value">{{ __('messages.total_inventory_value') }}</option>
                    </select>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.target_value') }} <span class="req">*</span></span>
                    <input type="number" name="target_value" min="0" step="1" required>
                </label>
            </div>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('revenueTrend'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($trendLabels) !!},
        datasets: [{
            label: '{{ __('messages.revenue') }}',
            data: {!! json_encode($trendRevenue) !!},
            backgroundColor: 'rgba(13,148,136,.75)',
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
        },
        scales: {
            x: { grid: { display: false } },
            y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') }, grid: { color: '#f0f4f6' } }
        }
    }
});
</script>
@endpush
        <label>
            <span class="label-cap">{{ __('messages.year') }}</span>
            <select name="year">
                @for ($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </label>
        <label>
            <span class="label-cap">{{ __('messages.month') }}</span>
            <select name="month">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                @endfor
            </select>
        </label>
        <button class="primary-button">{{ __('messages.apply') }}</button>
    </form>

    {{-- KPI Cards --}}
    <div class="stat-row" style="margin-bottom:28px">
        @php
            $kpis = [
                ['revenue',         __('messages.revenue'),          'Rp '.number_format($revenue,0,',','.'), $targets['revenue'] ?? null, 'fa-money-bill-wave', 'var(--teal)'],
                ['orders',          __('messages.total_orders'),     number_format($totalOrders),             $targets['orders'] ?? null,  'fa-cart-shopping',   'var(--blue)'],
                ['avg_order_value', __('messages.avg_order_value'),  'Rp '.number_format($avgOrderValue,0,',','.'), null,                'fa-receipt',         'var(--violet)'],
                ['gross_profit',    __('messages.gross_profit'),     'Rp '.number_format($grossProfit,0,',','.'), $targets['gross_profit'] ?? null, 'fa-chart-line', $grossProfit >= 0 ? 'var(--teal)' : 'var(--rose)'],
                ['inventory_value', __('messages.total_inventory_value'), 'Rp '.number_format($inventoryValue,0,',','.'), $targets['inventory_value'] ?? null, 'fa-warehouse', 'var(--amber)'],
            ];
        @endphp
        @foreach ($kpis as [$key, $label, $value, $target, $icon, $color])
        <div class="stat-card">
            <span class="stat-label"><i class="fa-solid {{ $icon }}" style="color:{{ $color }}"></i> {{ $label }}</span>
            <span class="stat-value" style="color:{{ $color }}">{{ $value }}</span>
            @if ($target)
            @php
                $actual  = in_array($key, ['revenue','gross_profit','inventory_value']) ? (float) str_replace(['Rp ','.'], '', $value) : (int) $value;
                $pct     = $target->target_value > 0 ? round($actual / $target->target_value * 100) : 0;
            @endphp
            <div style="margin-top:6px">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;margin-bottom:3px">
                    <span>{{ __('messages.target') }}: Rp {{ number_format($target->target_value,0,',','.') }}</span>
                    <span style="color:{{ $pct>=100?'var(--teal)':'var(--amber)' }}">{{ $pct }}%</span>
                </div>
                <div style="height:5px;background:#e8f4f2;border-radius:3px">
                    <div style="width:{{ min($pct,100) }}%;height:100%;background:{{ $pct>=100?'var(--teal)':'var(--amber)' }};border-radius:3px"></div>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Chart + Top Products --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">
        {{-- Revenue Trend --}}
        <div style="background:#f6fafc;border-radius:12px;padding:20px">
            <p style="font-weight:700;font-size:13px;margin-bottom:12px">{{ __('messages.revenue_trend_6m') }}</p>
            <canvas id="revenueTrend" height="180"></canvas>
        </div>

        {{-- Top Products --}}
        <div style="display:flex;flex-direction:column;gap:16px">
            <div style="background:#f6fafc;border-radius:12px;padding:20px">
                <p style="font-weight:700;font-size:13px;margin-bottom:10px">{{ __('messages.top_by_revenue') }}</p>
                @forelse ($topByRevenue as $p)
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #e8f0f2;font-size:13px">
                    <span>{{ $p->product_name }}</span>
                    <span style="font-weight:600;color:var(--teal)">Rp {{ number_format($p->total_revenue,0,',','.') }}</span>
                </div>
                @empty
                <p style="color:#aaa;font-size:13px">{{ __('messages.no_data') }}</p>
                @endforelse
            </div>
            <div style="background:#f6fafc;border-radius:12px;padding:20px">
                <p style="font-weight:700;font-size:13px;margin-bottom:10px">{{ __('messages.top_by_qty') }}</p>
                @forelse ($topByQty as $p)
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #e8f0f2;font-size:13px">
                    <span>{{ $p->product_name }}</span>
                    <span style="font-weight:600;color:var(--blue)">{{ number_format($p->total_qty,2) }}</span>
                </div>
                @empty
                <p style="color:#aaa;font-size:13px">{{ __('messages.no_data') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- Set Target Modal --}}
<div class="modal-overlay" id="modal-set-target">
    <div class="modal" style="max-width:440px">
        <div class="modal-head">
            <h3>{{ __('messages.set_target') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('reporting.kpi.target') }}" class="modal-body">
            @csrf
            <input type="hidden" name="year"  value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="form-grid two">
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.metric') }}</span>
                    <select name="metric" required>
                        <option value="revenue">{{ __('messages.revenue') }}</option>
                        <option value="orders">{{ __('messages.total_orders') }}</option>
                        <option value="gross_profit">{{ __('messages.gross_profit') }}</option>
                        <option value="inventory_value">{{ __('messages.total_inventory_value') }}</option>
                    </select>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.target_value') }} <span class="req">*</span></span>
                    <input type="number" name="target_value" min="0" step="1" required>
                </label>
            </div>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('revenueTrend'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($trendLabels) !!},
        datasets: [{
            label: '{{ __('messages.revenue') }}',
            data: {!! json_encode($trendRevenue) !!},
            backgroundColor: 'rgba(13,148,136,.7)',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } }
        }
    }
});
</script>
@endpush
