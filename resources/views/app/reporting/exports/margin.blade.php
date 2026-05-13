@extends('app.reporting.exports._layout')
@section('title', 'Margin Report')
@section('subtitle', 'Average Margin: ' . $avgMargin . '%')

@section('content')
<table>
    <thead>
        <tr>
            <th>Product</th><th>SKU</th><th>Category</th>
            <th class="num">Selling Price (Rp)</th><th class="num">Cost Price (Rp)</th>
            <th class="num">Margin (Rp)</th><th class="num">Margin %</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $r)
    @php $badge = $r['margin_pct'] >= 30 ? 'badge-teal' : ($r['margin_pct'] >= 10 ? 'badge-blue' : 'badge-rose'); @endphp
    <tr>
        <td>{{ $r['product']->name }}</td><td>{{ $r['product']->sku }}</td><td>{{ $r['product']->category }}</td>
        <td class="num">{{ number_format($r['price'], 0, ',', '.') }}</td>
        <td class="num">{{ number_format($r['cost_price'], 0, ',', '.') }}</td>
        <td class="num">{{ number_format($r['margin'], 0, ',', '.') }}</td>
        <td class="num"><span class="badge {{ $badge }}">{{ $r['margin_pct'] }}%</span></td>
    </tr>
    @endforeach
    </tbody>
</table>
@endsection
