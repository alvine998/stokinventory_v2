@extends('app.reporting.exports._layout')
@section('title', 'Stock Aging Report')
@section('subtitle', 'Based on last inbound movement date')

@section('content')
<table>
    <thead>
        <tr>
            <th>Product</th><th>SKU</th>
            <th class="num">Current Stock</th><th>Last Stock In</th>
            <th class="num">Age (days)</th><th>Bucket</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $row)
    @php
        $badge = match($row['bucket']) {
            'fresh'    => 'badge-teal',
            'aging_30' => 'badge-blue',
            'aging_60' => 'badge-amber',
            'aged'     => 'badge-rose',
            default    => '',
        };
        $label = match($row['bucket']) {
            'fresh'    => 'Fresh (<30d)',
            'aging_30' => 'Aging (30-60d)',
            'aging_60' => 'Aging (60-90d)',
            'aged'     => 'Aged (>90d)',
            default    => 'Unknown',
        };
    @endphp
    <tr>
        <td>{{ $row['product']->name }}</td><td>{{ $row['product']->sku }}</td>
        <td class="num">{{ $row['product']->current_stock }}</td>
        <td>{{ $row['last_in_at'] ? \Carbon\Carbon::parse($row['last_in_at'])->format('d M Y') : '—' }}</td>
        <td class="num">{{ $row['age_days'] ?? '—' }}</td>
        <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
    </tr>
    @endforeach
    </tbody>
</table>
@endsection
