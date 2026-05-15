@extends('layouts.app', ['title' => __('messages.inventory_valuation'), 'heading' => __('messages.nav_finance')])

@section('content')
@include('app.finance._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_finance') }}</p>
            <h2><i class="fa-solid fa-scale-balanced"></i> {{ __('messages.inventory_valuation') }}</h2>
        </div>
        <div class="head-actions">
            <a href="{{ route('finance.valuation.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
        </div>
    </div>

    {{-- Summary --}}
    <div class="stat-row" style="margin-bottom:20px">
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.total_sku') }}</span>
            <span class="stat-value">{{ $products->count() }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.total_inventory_value') }}</span>
            <span class="stat-value" style="color:var(--teal)">Rp {{ number_format($totalValue, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th class="num">{{ __('messages.current_stock') }}</th>
                    <th class="num">{{ __('messages.cost_price') }}</th>
                    <th class="num">{{ __('messages.stock_value') }}</th>
                    <th class="num">{{ __('messages.value_pct') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products->sortByDesc('value') as $row)
            @php $pct = $totalValue > 0 ? round($row['value'] / $totalValue * 100, 1) : 0; @endphp
            <tr>
                <td><strong>{{ $row['product']->name }}</strong></td>
                <td><code>{{ $row['product']->sku ?? '—' }}</code></td>
                <td class="num">{{ number_format($row['current_stock'], 2) }} {{ $row['product']->unit }}</td>
                <td class="num">Rp {{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                <td class="num" style="font-weight:600">Rp {{ number_format($row['value'], 0, ',', '.') }}</td>
                <td class="num">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="flex:1;height:6px;background:#e8f4f2;border-radius:3px">
                            <div style="width:{{ min($pct,100) }}%;height:100%;background:var(--teal);border-radius:3px"></div>
                        </div>
                        <span style="min-width:36px;font-size:12px">{{ $pct }}%</span>
                    </div>
                </td>
            </tr>
            @empty
                <tr><td colspan="6" class="empty-cell">{{ __('messages.no_products') }}</td></tr>
            @endforelse
            </tbody>
            <tfoot>
                <tr style="font-weight:700;border-top:2px solid #e3ecef">
                    <td colspan="4">{{ __('messages.total') }}</td>
                    <td class="num" style="color:var(--teal)">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                    <td class="num">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</section>
@endsection
