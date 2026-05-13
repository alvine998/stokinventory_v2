@extends('app.reporting.exports._layout')
@section('title', 'Purchase vs Sales — Last 12 Months')

@section('content')
@php
    $totalSales = 0; $totalPurchases = 0;
    foreach ($periods as $p) {
        $totalSales     += (float)($salesRaw[$p] ?? 0);
        $totalPurchases += (float)($purchasesRaw[$p] ?? 0);
    }
@endphp
<div class="stat-grid">
    <div class="stat-card"><p class="label">Total Sales (12 months)</p><p class="value">Rp {{ number_format($totalSales, 0, ',', '.') }}</p></div>
    <div class="stat-card"><p class="label">Total Purchases (12 months)</p><p class="value">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</p></div>
    <div class="stat-card" style="border-top-color:{{ $totalSales >= $totalPurchases ? '#0d9488' : '#e11d48' }}">
        <p class="label">Net Difference</p>
        <p class="value" style="color:{{ $totalSales >= $totalPurchases ? '#0d9488' : '#e11d48' }}">Rp {{ number_format($totalSales - $totalPurchases, 0, ',', '.') }}</p>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Period</th>
            <th class="num">Sales (Rp)</th>
            <th class="num">Purchases (Rp)</th>
            <th class="num">Difference (Rp)</th>
        </tr>
    </thead>
    <tbody>
    @foreach($periods as $period)
    @php
        $s   = (float)($salesRaw[$period] ?? 0);
        $b   = (float)($purchasesRaw[$period] ?? 0);
        $diff = $s - $b;
    @endphp
    <tr>
        <td>{{ \Carbon\Carbon::parse($period . '-01')->format('M Y') }}</td>
        <td class="num">{{ number_format($s, 0, ',', '.') }}</td>
        <td class="num">{{ number_format($b, 0, ',', '.') }}</td>
        <td class="num" style="color:{{ $diff >= 0 ? '#0f766e' : '#be123c' }}">{{ number_format($diff, 0, ',', '.') }}</td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight:700;background:#f0fdfa">
            <td>Total</td>
            <td class="num">{{ number_format($totalSales, 0, ',', '.') }}</td>
            <td class="num">{{ number_format($totalPurchases, 0, ',', '.') }}</td>
            <td class="num" style="color:{{ $totalSales >= $totalPurchases ? '#0f766e' : '#be123c' }}">{{ number_format($totalSales - $totalPurchases, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
@endsection
