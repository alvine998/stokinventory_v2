@extends('layouts.app', ['title' => __('messages.nav_report_forecast'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-wand-magic-sparkles"></i> {{ __('messages.nav_report_forecast') }}</h2>
        </div>
        <div class="export-group">
            <a href="{{ route('reporting.forecast.export', array_merge(request()->query(), ['format'=>'excel'])) }}" class="secondary-button btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
            <a href="{{ route('reporting.forecast.export', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="secondary-button btn-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('reporting.forecast') }}" style="display:contents">
            <label>
                <span class="label-cap">{{ __('messages.history_months') }}</span>
                <select name="months_back">
                    @foreach ([3 => '3 '.__('messages.months'), 6 => '6 '.__('messages.months'), 12 => '12 '.__('messages.months')] as $v => $l)
                        <option value="{{ $v }}" {{ $monthsBack == $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="label-cap">{{ __('messages.forecast_horizon') }}</span>
                <select name="forecast_months">
                    @foreach ([1 => '1 '.__('messages.month'), 3 => '3 '.__('messages.months'), 6 => '6 '.__('messages.months')] as $v => $l)
                        <option value="{{ $v }}" {{ $forecastMonths == $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </label>
            <button class="primary-button">{{ __('messages.apply') }}</button>
        </form>
    </div>

    @php $reorderCount = $products->where('needs_reorder', true)->count(); @endphp
    @if($reorderCount)
    <div class="report-alert-warn">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:18px"></i>
        <div>
            <strong>{{ $reorderCount }} products need restocking</strong><br>
            <span style="font-weight:400;font-size:12px">{{ __('messages.forecast_reorder_alert', ['count' => $reorderCount]) }}</span>
        </div>
    </div>
    @else
    <div class="report-alert-ok">
        <i class="fa-solid fa-circle-check"></i> All products have sufficient stock for the forecast period.
    </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th class="num">{{ __('messages.current_stock') }}</th>
                    <th class="num">{{ __('messages.avg_monthly_out') }}</th>
                    <th class="num">{{ __('messages.forecasted_need') }}</th>
                    <th class="num">{{ __('messages.surplus_deficit') }}</th>
                    <th class="num">{{ __('messages.reorder_qty') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $row)
            <tr style="{{ $row['needs_reorder'] ? 'background:#fff8f8' : '' }}">
                <td>
                    <strong>{{ $row['product']->name }}</strong>
                    @if($row['needs_reorder'])
                        <span class="badge-status" style="background:#fff0f3;color:var(--rose);font-size:10px;margin-left:4px">
                            <i class="fa-solid fa-arrow-down"></i> Reorder
                        </span>
                    @endif
                </td>
                <td><code>{{ $row['product']->sku ?? '—' }}</code></td>
                <td class="num">{{ number_format($row['current_stock'], 2) }} {{ $row['product']->unit }}</td>
                <td class="num">{{ number_format($row['avg_monthly'], 2) }}</td>
                <td class="num">{{ number_format($row['forecasted_need'], 2) }}</td>
                <td class="num" style="font-weight:700;color:{{ $row['surplus'] >= 0 ? 'var(--teal)' : 'var(--rose)' }}">
                    {{ $row['surplus'] >= 0 ? '+' : '' }}{{ number_format($row['surplus'], 2) }}
                </td>
                <td class="num">
                    @if($row['reorder_qty'] > 0)
                        <span style="font-weight:700;color:var(--rose)">{{ number_format($row['reorder_qty'], 2) }}</span>
                    @else
                        <span style="color:#aaa">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty-cell">{{ __('messages.no_products') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="report-note">
        <i class="fa-solid fa-circle-info"></i>
        {{ __('messages.forecast_note', ['months_back' => $monthsBack, 'forecast_months' => $forecastMonths]) }}
    </p>
</section>
@endsection
