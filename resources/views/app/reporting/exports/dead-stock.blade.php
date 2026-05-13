@extends('app.reporting.exports._layout')
@section('title', 'Dead Stock Report')
@section('subtitle', 'No outbound movement in the last ' . $days . ' days')

@section('content')
<div class="stat-grid">
    <div class="stat-card" style="border-top-color:#e11d48"><p class="label">Dead Stock Items</p><p class="value" style="color:#e11d48">{{ $products->count() }}</p></div>
    <div class="stat-card" style="border-top-color:#d97706"><p class="label">Locked Capital</p><p class="value" style="color:#d97706">Rp {{ number_format($totalValue, 0, ',', '.') }}</p></div>
</div>

<table>
    <thead>
        <tr>
            <th>Product</th><th>SKU</th><th>Category</th>
            <th class="num">Current Stock</th><th class="num">Cost Price (Rp)</th><th class="num">Stock Value (Rp)</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $p)
    <tr style="background:#fff5f5">
        <td>{{ $p->name }}</td><td>{{ $p->sku }}</td><td>{{ $p->category }}</td>
        <td class="num">{{ $p->current_stock }}</td>
        <td class="num">{{ number_format((float)$p->cost_price, 0, ',', '.') }}</td>
        <td class="num">{{ number_format((float)$p->stock_value, 0, ',', '.') }}</td>
    </tr>
    @endforeach
    <tr style="font-weight:700">
        <td colspan="5" style="text-align:right">Total Locked Value</td>
        <td class="num">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
    </tr>
    </tbody>
</table>
@endsection
