@extends('layouts.app', ['title' => __('messages.nav_report_dead_stock'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-skull"></i> {{ __('messages.nav_report_dead_stock') }}</h2>
        </div>
        <div class="export-group">
            <a href="{{ route('reporting.dead-stock.export', array_merge(request()->query(), ['format'=>'excel'])) }}" class="secondary-button btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
            <a href="{{ route('reporting.dead-stock.export', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="secondary-button btn-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('reporting.dead-stock') }}" style="display:contents">
            <label>
                <span class="label-cap">{{ __('messages.no_movement_since') }}</span>
                <select name="days">
                    @foreach ([30 => '30 '.__('messages.days'), 60 => '60 '.__('messages.days'), 90 => '90 '.__('messages.days'), 180 => '180 '.__('messages.days')] as $d => $dlabel)
                        <option value="{{ $d }}" {{ $days == $d ? 'selected' : '' }}>{{ $dlabel }}</option>
                    @endforeach
                </select>
            </label>
            <button class="primary-button">{{ __('messages.apply') }}</button>
        </form>
    </div>

    @if($products->count())
    <div class="stat-row" style="margin-bottom:20px">
        <div class="stat-card">
            <span class="stat-label" style="color:var(--rose)"><i class="fa-solid fa-box-open"></i> {{ __('messages.dead_stock_count') }}</span>
            <span class="stat-value" style="color:var(--rose)">{{ $products->count() }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label" style="color:var(--amber)"><i class="fa-solid fa-lock"></i> {{ __('messages.dead_stock_value') }}</span>
            <span class="stat-value" style="color:var(--amber)">Rp {{ number_format($totalValue, 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="report-alert-warn" style="margin-bottom:20px">
        <i class="fa-solid fa-triangle-exclamation"></i>
        {{ $products->count() }} {{ __('messages.dead_stock_count') }} with Rp {{ number_format($totalValue, 0, ',', '.') }} locked capital — no outbound movement in {{ $days }} days.
    </div>
    @else
    <div class="report-alert-ok" style="margin-bottom:20px">
        <i class="fa-solid fa-circle-check"></i> {{ __('messages.no_dead_stock') }}
    </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th class="num">{{ __('messages.current_stock') }}</th>
                    <th class="num">{{ __('messages.cost_price') }}</th>
                    <th class="num">{{ __('messages.stock_value') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $p)
            <tr>
                <td><strong>{{ $p->name }}</strong></td>
                <td><code>{{ $p->sku ?? '—' }}</code></td>
                <td>{{ $p->category ?? '—' }}</td>
                <td class="num">{{ number_format($p->current_stock, 2) }} {{ $p->unit }}</td>
                <td class="num">Rp {{ number_format($p->cost_price, 0, ',', '.') }}</td>
                <td class="num" style="font-weight:700;color:var(--rose)">Rp {{ number_format($p->stock_value, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="empty-cell" style="color:var(--teal)">
                <i class="fa-solid fa-circle-check"></i> {{ __('messages.no_dead_stock') }}
            </td></tr>
            @endforelse
            </tbody>
            @if($products->count())
            <tfoot>
                <tr style="font-weight:700;border-top:2px solid #e3ecef">
                    <td colspan="5">{{ __('messages.total') }}</td>
                    <td class="num" style="color:var(--rose)">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <p class="report-note"><i class="fa-solid fa-circle-info"></i> {{ __('messages.dead_stock_note', ['days' => $days]) }}</p>
</section>
@endsection
