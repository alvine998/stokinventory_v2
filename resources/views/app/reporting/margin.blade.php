@extends('layouts.app', ['title' => __('messages.nav_report_margin'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-tags"></i> {{ __('messages.nav_report_margin') }}</h2>
        </div>
        <div class="export-group">
            <a href="{{ route('reporting.margin.export', array_merge(request()->query(), ['format'=>'excel'])) }}" class="secondary-button btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
            <a href="{{ route('reporting.margin.export', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="secondary-button btn-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('reporting.margin') }}" style="display:contents">
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
                <span class="label-cap">{{ __('messages.sort') }}</span>
                <select name="sort">
                    <option value="desc" {{ $sort === 'desc' ? 'selected' : '' }}>{{ __('messages.highest_margin') }}</option>
                    <option value="asc"  {{ $sort === 'asc'  ? 'selected' : '' }}>{{ __('messages.lowest_margin') }}</option>
                </select>
            </label>
            <button class="primary-button">{{ __('messages.apply') }}</button>
        </form>
    </div>

    <div class="report-avg-bar">
        <i class="fa-solid fa-chart-pie" style="color:var(--teal)"></i>
        {{ __('messages.avg_margin') }}:
        <strong style="color:{{ $avgMargin >= 20 ? 'var(--teal)' : ($avgMargin >= 0 ? 'var(--amber)' : 'var(--rose)') }};font-size:16px">
            {{ $avgMargin }}%
        </strong>
        <span style="color:#aaa;font-size:12px;margin-left:4px">across {{ $products->count() }} products</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th class="num">{{ __('messages.selling_price') }}</th>
                    <th class="num">{{ __('messages.cost_price') }}</th>
                    <th class="num">{{ __('messages.margin_amount') }}</th>
                    <th class="num" style="min-width:120px">{{ __('messages.margin_pct') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $row)
            @php
                $mColor = $row['margin_pct'] >= 20 ? 'var(--teal)' : ($row['margin_pct'] >= 0 ? 'var(--amber)' : 'var(--rose)');
                $mBg    = $row['margin_pct'] >= 20 ? '#f0fdfa'     : ($row['margin_pct'] >= 0 ? '#fffbeb'       : '#fff0f3');
            @endphp
            <tr>
                <td><strong>{{ $row['product']->name }}</strong></td>
                <td><code>{{ $row['product']->sku ?? '—' }}</code></td>
                <td>{{ $row['product']->category ?? '—' }}</td>
                <td class="num">Rp {{ number_format($row['price'], 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                <td class="num" style="color:{{ $mColor }};font-weight:600">Rp {{ number_format($row['margin'], 0, ',', '.') }}</td>
                <td class="num">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-weight:700;color:{{ $mColor }};min-width:42px">{{ $row['margin_pct'] }}%</span>
                        <div style="flex:1;height:6px;background:#eef2f4;border-radius:3px;overflow:hidden">
                            <div style="width:{{ min(abs($row['margin_pct']), 100) }}%;height:100%;background:{{ $mColor }};border-radius:3px"></div>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty-cell">{{ __('messages.no_products') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
