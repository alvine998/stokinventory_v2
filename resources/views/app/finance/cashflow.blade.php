@extends('layouts.app', ['title' => __('messages.cashflow_inventory'), 'heading' => __('messages.nav_finance')])

@section('content')
@include('app.finance._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_finance') }}</p>
            <h2><i class="fa-solid fa-water"></i> {{ __('messages.cashflow_inventory') }}</h2>
        </div>
    </div>

    <div class="table-wrap" style="margin-bottom:28px">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.period') }}</th>
                    <th class="num" style="color:var(--teal)">{{ __('messages.inflow_sales') }}</th>
                    <th class="num" style="color:var(--rose)">{{ __('messages.outflow_purchases') }}</th>
                    <th class="num">{{ __('messages.net_cashflow') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($periods as $period)
            @php
                $in  = (float) ($inflows[$period] ?? 0);
                $out = (float) ($outflows[$period] ?? 0);
                $net = $in - $out;
            @endphp
            <tr>
                <td><strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y') }}</strong></td>
                <td class="num" style="color:var(--teal)">Rp {{ number_format($in, 0, ',', '.') }}</td>
                <td class="num" style="color:var(--rose)">Rp {{ number_format($out, 0, ',', '.') }}</td>
                <td class="num" style="font-weight:700;color:{{ $net >= 0 ? 'var(--teal)' : 'var(--rose)' }}">
                    {{ $net >= 0 ? '+' : '' }}Rp {{ number_format($net, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight:700;border-top:2px solid #e3ecef">
                    <td>{{ __('messages.total') }}</td>
                    <td class="num" style="color:var(--teal)">Rp {{ number_format(collect($periods)->sum(fn($p) => $inflows[$p] ?? 0), 0, ',', '.') }}</td>
                    <td class="num" style="color:var(--rose)">Rp {{ number_format(collect($periods)->sum(fn($p) => $outflows[$p] ?? 0), 0, ',', '.') }}</td>
                    @php $totalNet = collect($periods)->sum(fn($p) => ($inflows[$p] ?? 0) - ($outflows[$p] ?? 0)); @endphp
                    <td class="num" style="color:{{ $totalNet >= 0 ? 'var(--teal)' : 'var(--rose)' }}">
                        {{ $totalNet >= 0 ? '+' : '' }}Rp {{ number_format($totalNet, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <p style="font-size:12px;color:#aaa"><i class="fa-solid fa-circle-info"></i> {{ __('messages.cashflow_note') }}</p>
</section>
@endsection
