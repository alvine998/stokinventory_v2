@extends('app.reporting.exports._layout')
@section('title', 'Inventory Value Report')
@section('subtitle', 'Total Value: Rp ' . number_format($grandTotal, 0, ',', '.'))

@section('content')
@foreach($grouped as $group)
<p class="section-title">{{ $group['category'] }} — {{ $group['products']->count() }} items · Rp {{ number_format($group['total_value'], 0, ',', '.') }}</p>
<table style="margin-bottom:10px">
    <thead>
        <tr>
            <th>Product</th><th>SKU</th>
            <th class="num">Stock</th><th class="num">Cost Price (Rp)</th><th class="num">Value (Rp)</th>
        </tr>
    </thead>
    <tbody>
    @foreach($group['products'] as $p)
    @php $val = (float)$p->current_stock * (float)$p->cost_price; @endphp
    <tr>
        <td>{{ $p->name }}</td><td>{{ $p->sku }}</td>
        <td class="num">{{ $p->current_stock }}</td>
        <td class="num">{{ number_format((float)$p->cost_price, 0, ',', '.') }}</td>
        <td class="num">{{ number_format($val, 0, ',', '.') }}</td>
    </tr>
    @endforeach
    <tr style="font-weight:700;background:#f0fdfa">
        <td colspan="2">Subtotal</td>
        <td class="num">{{ number_format($group['total_units'], 0, ',', '.') }}</td>
        <td></td>
        <td class="num">{{ number_format($group['total_value'], 0, ',', '.') }}</td>
    </tr>
    </tbody>
</table>
@endforeach

<table>
    <tfoot>
        <tr style="font-weight:700;font-size:12px;background:#0d9488;color:#fff">
            <td colspan="4">Grand Total</td>
            <td class="num">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
@endsection
