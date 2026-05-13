@extends('app.reporting.exports._layout')
@section('title', 'Demand Forecast')
@section('subtitle', 'Based on last ' . $monthsBack . ' months · Forecast for ' . $forecastMonths . ' months')

@section('content')
@php $reorderCount = $products->where('needs_reorder', true)->count(); @endphp
@if($reorderCount)
<div style="background:#fff5f5;border:1px solid #fca5a5;border-radius:6px;padding:8px 12px;margin-bottom:12px;color:#be123c;font-size:10px">
    <strong>⚠ {{ $reorderCount }} product(s) need restock based on forecast.</strong>
</div>
@endif

<table>
    <thead>
        <tr>
            <th>Product</th><th>SKU</th>
            <th class="num">Current Stock</th>
            <th class="num">Avg Monthly Out</th>
            <th class="num">Forecasted Need</th>
            <th class="num">Surplus / Deficit</th>
            <th class="num">Reorder Qty</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $r)
    <tr style="{{ $r['needs_reorder'] ? 'background:#fff5f5' : '' }}">
        <td>{{ $r['product']->name }}</td><td>{{ $r['product']->sku }}</td>
        <td class="num">{{ $r['current_stock'] }}</td>
        <td class="num">{{ $r['avg_monthly'] }}</td>
        <td class="num">{{ $r['forecasted_need'] }}</td>
        <td class="num" style="color:{{ $r['surplus'] >= 0 ? '#0f766e' : '#be123c' }}">
            {{ $r['surplus'] >= 0 ? '+' : '' }}{{ $r['surplus'] }}
        </td>
        <td class="num">{{ $r['reorder_qty'] > 0 ? $r['reorder_qty'] : '—' }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endsection
