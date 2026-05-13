@extends('app.reporting.exports._layout')
@section('title', 'Stock Report')
@section('subtitle', 'Generated: ' . now()->format('d M Y'))

@section('content')
<table>
    <thead>
        <tr>
            <th>Product</th><th>SKU</th><th>Category</th>
            <th class="num">Current Stock</th><th class="num">Min. Stock</th>
            <th class="num">Stock Value (Rp)</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $p)
    @php
        $stockVal = (float)$p->current_stock * (float)$p->cost_price;
        if ($p->current_stock <= 0) { $status = 'Out of Stock'; $badge = 'badge-rose'; }
        elseif ($p->current_stock <= $p->minimum_stock && $p->minimum_stock > 0) { $status = 'Low Stock'; $badge = 'badge-amber'; }
        elseif ($p->minimum_stock > 0 && $p->current_stock > $p->minimum_stock * 2) { $status = 'Over Stock'; $badge = 'badge-blue'; }
        else { $status = 'Normal'; $badge = 'badge-teal'; }
    @endphp
    <tr>
        <td>{{ $p->name }}</td><td>{{ $p->sku }}</td><td>{{ $p->category }}</td>
        <td class="num">{{ $p->current_stock }}</td><td class="num">{{ $p->minimum_stock }}</td>
        <td class="num">{{ number_format($stockVal, 0, ',', '.') }}</td>
        <td><span class="badge {{ $badge }}">{{ $status }}</span></td>
    </tr>
    @endforeach
    </tbody>
</table>
@endsection
