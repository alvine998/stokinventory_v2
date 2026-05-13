@extends('layouts.app', ['title' => __('messages.nav_report_stock'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-warehouse"></i> {{ __('messages.nav_report_stock') }}</h2>
        </div>
        <div class="export-group">
            <a href="{{ route('reporting.stock.export', array_merge(request()->query(), ['format'=>'excel'])) }}" class="secondary-button btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
            <a href="{{ route('reporting.stock.export', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="secondary-button btn-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        </div>
    </div>

    {{-- Summary --}}
    <div class="stat-row" style="margin-bottom:20px">
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.total_sku') }}</span>
            <span class="stat-value">{{ $summary['total'] }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label" style="color:var(--rose)"><i class="fa-solid fa-circle-xmark"></i> {{ __('messages.stock_out') }}</span>
            <span class="stat-value" style="color:var(--rose)">{{ $summary['out'] }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label" style="color:var(--amber)"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('messages.stock_low') }}</span>
            <span class="stat-value" style="color:var(--amber)">{{ $summary['low'] }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label" style="color:var(--teal)"><i class="fa-solid fa-circle-check"></i> {{ __('messages.stock_over') }}</span>
            <span class="stat-value" style="color:var(--teal)">{{ $summary['over'] }}</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('reporting.stock') }}" style="display:contents">
            <label>
                <span class="label-cap">{{ __('messages.search') }}</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('messages.product_or_sku') }}" style="min-width:200px">
            </label>
            <label>
                <span class="label-cap">{{ __('messages.category') }}</span>
                <select name="category">
                    <option value="">— {{ __('messages.all') }} —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="label-cap">{{ __('messages.status') }}</span>
                <select name="status">
                    @foreach (['all' => __('messages.all'), 'out' => __('messages.stock_out'), 'low' => __('messages.stock_low'), 'over' => __('messages.stock_over'), 'normal' => __('messages.stock_normal')] as $val => $lbl)
                        <option value="{{ $val }}" {{ $status == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </label>
            <button class="primary-button">{{ __('messages.apply') }}</button>
            @if($search || $category || $status !== 'all')
                <a href="{{ route('reporting.stock') }}" class="secondary-button" style="font-weight:500"><i class="fa-solid fa-xmark"></i> Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th class="num">{{ __('messages.current_stock') }}</th>
                    <th class="num">{{ __('messages.minimum_stock') }}</th>
                    <th class="num">{{ __('messages.stock_value') }}</th>
                    <th>{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $p)
            @php
                if ($p->current_stock <= 0)                                                            { [$stKey, $stLabel, $stBg, $stColor] = ['out',    __('messages.stock_out'),    '#fff0f3', 'var(--rose)']; }
                elseif ($p->minimum_stock > 0 && $p->current_stock <= $p->minimum_stock)               { [$stKey, $stLabel, $stBg, $stColor] = ['low',    __('messages.stock_low'),    '#fffbeb', 'var(--amber)']; }
                elseif ($p->minimum_stock > 0 && $p->current_stock > $p->minimum_stock * 2)            { [$stKey, $stLabel, $stBg, $stColor] = ['over',   __('messages.stock_over'),   '#f0fdfa', 'var(--teal)']; }
                else                                                                                   { [$stKey, $stLabel, $stBg, $stColor] = ['normal', __('messages.stock_normal'), '#f8fafb', '#4a6070']; }
            @endphp
            <tr>
                <td><strong>{{ $p->name }}</strong></td>
                <td><code>{{ $p->sku ?? '—' }}</code></td>
                <td>{{ $p->category ?? '—' }}</td>
                <td class="num">{{ number_format($p->current_stock, 2) }} {{ $p->unit }}</td>
                <td class="num">{{ $p->minimum_stock > 0 ? number_format($p->minimum_stock, 2) : '—' }}</td>
                <td class="num">Rp {{ number_format($p->current_stock * $p->cost_price, 0, ',', '.') }}</td>
                <td>
                    <span class="badge-status" style="background:{{ $stBg }};color:{{ $stColor }}">{{ $stLabel }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty-cell">{{ __('messages.no_products') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</section>
@endsection
