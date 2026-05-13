@extends('app.reporting.exports._layout')
@section('title', 'KPI Dashboard — ' . $label)

@section('content')
<div class="stat-grid">
    <div class="stat-card"><p class="label">Revenue</p><p class="value">Rp {{ number_format($revenue, 0, ',', '.') }}</p></div>
    <div class="stat-card"><p class="label">Total Orders</p><p class="value">{{ number_format($totalOrders) }}</p></div>
    <div class="stat-card"><p class="label">Avg Order Value</p><p class="value">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p></div>
    <div class="stat-card"><p class="label">Gross Profit</p><p class="value">Rp {{ number_format($grossProfit, 0, ',', '.') }}</p></div>
    <div class="stat-card"><p class="label">Inventory Value</p><p class="value">Rp {{ number_format($inventoryValue, 0, ',', '.') }}</p></div>
</div>

<p class="section-title">Top 5 Products by Revenue</p>
<table>
    <thead><tr><th>#</th><th>Product</th><th class="num">Revenue (Rp)</th></tr></thead>
    <tbody>
    @foreach($topByRevenue as $i => $row)
    <tr><td>{{ $i+1 }}</td><td>{{ $row->product_name }}</td><td class="num">{{ number_format($row->total_revenue, 0, ',', '.') }}</td></tr>
    @endforeach
    </tbody>
</table>
@endsection
