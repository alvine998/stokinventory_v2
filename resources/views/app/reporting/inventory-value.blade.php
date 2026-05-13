@extends('layouts.app', ['title' => __('messages.nav_report_inv_value'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-coins"></i> {{ __('messages.nav_report_inv_value') }}</h2>
        </div>
        <div class="export-group">
            <a href="{{ route('reporting.inventory-value.export', array_merge(request()->query(), ['format'=>'excel'])) }}" class="secondary-button btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
            <a href="{{ route('reporting.inventory-value.export', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="secondary-button btn-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('reporting.inventory-value') }}" style="display:contents">
            <label>
                <span class="label-cap">{{ __('messages.category') }}</span>
                <select name="category">
                    <option value="">— {{ __('messages.all') }} —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </label>
            <button class="primary-button">{{ __('messages.apply') }}</button>
        </form>
    </div>

    <div class="stat-row" style="margin-bottom:24px">
        <div class="stat-card">
            <span class="stat-label"><i class="fa-solid fa-layer-group" style="color:var(--blue)"></i> {{ __('messages.total_categories') }}</span>
            <span class="stat-value">{{ $grouped->count() }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label" style="color:var(--teal)"><i class="fa-solid fa-coins"></i> {{ __('messages.grand_total_value') }}</span>
            <span class="stat-value" style="color:var(--teal)">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>
    </div>

    @foreach ($grouped as $group)
    @php $pct = $grandTotal > 0 ? round($group['total_value'] / $grandTotal * 100, 1) : 0; @endphp
    <div style="margin-bottom:28px;border:1px solid #e5eaef;border-radius:12px;overflow:hidden">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:#f8fafb;border-bottom:1px solid #e5eaef">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-folder" style="color:var(--teal)"></i>
                <strong style="font-size:14px">{{ $group['category'] }}</strong>
                <span style="font-size:12px;color:#8fa4ae">{{ $group['products']->count() }} products</span>
            </div>
            <div style="display:flex;align-items:center;gap:16px">
                <span style="font-size:12px;color:#8fa4ae">{{ number_format($group['total_units'], 2) }} units</span>
                <strong style="font-size:14px;color:var(--teal)">Rp {{ number_format($group['total_value'], 0, ',', '.') }}</strong>
                <span style="font-size:12px;font-weight:700;color:var(--blue)">{{ $pct }}%</span>
            </div>
        </div>
        <div style="height:5px;background:#e8f4f2">
            <div style="width:{{ $pct }}%;height:100%;background:var(--teal)"></div>
        </div>
        <div class="table-wrap" style="margin:0;border:none">
            <table style="border-radius:0">
                <thead>
                    <tr>
                        <th>{{ __('messages.product') }}</th>
                        <th>{{ __('messages.sku') }}</th>
                        <th class="num">{{ __('messages.current_stock') }}</th>
                        <th class="num">{{ __('messages.cost_price') }}</th>
                        <th class="num">{{ __('messages.stock_value') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($group['products'] as $p)
                @php $val = (float)$p->current_stock * (float)$p->cost_price; @endphp
                <tr>
                    <td>{{ $p->name }}</td>
                    <td><code>{{ $p->sku ?? '—' }}</code></td>
                    <td class="num">{{ number_format($p->current_stock, 2) }} {{ $p->unit }}</td>
                    <td class="num">Rp {{ number_format($p->cost_price, 0, ',', '.') }}</td>
                    <td class="num" style="font-weight:600">Rp {{ number_format($val, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</section>
@endsection
