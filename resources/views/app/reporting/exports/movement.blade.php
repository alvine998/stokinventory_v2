@extends('app.reporting.exports._layout')
@section('title', 'Fast / Slow Moving Report')
@section('subtitle', $startDate . ' to ' . $endDate)

@section('content')
<table>
    <thead>
        <tr>
            <th>#</th><th>Product</th><th>SKU</th>
            <th class="num">Current Stock</th><th class="num">Qty Out</th>
            <th class="num">Movements</th><th>Level</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $i => $p)
    @php
        if ($p->total_out >= 50)      { $level = 'Fast Moving';   $badge = 'badge-teal'; }
        elseif ($p->total_out >= 10)  { $level = 'Medium Moving'; $badge = 'badge-blue'; }
        elseif ($p->total_out > 0)    { $level = 'Slow Moving';   $badge = 'badge-amber'; }
        else                          { $level = 'No Movement';   $badge = 'badge-rose'; }
    @endphp
    <tr>
        <td>{{ $i+1 }}</td><td>{{ $p->name }}</td><td>{{ $p->sku }}</td>
        <td class="num">{{ $p->current_stock }}</td>
        <td class="num">{{ $p->total_out }}</td>
        <td class="num">{{ $p->movement_count }}</td>
        <td><span class="badge {{ $badge }}">{{ $level }}</span></td>
    </tr>
    @endforeach
    </tbody>
</table>
@endsection
