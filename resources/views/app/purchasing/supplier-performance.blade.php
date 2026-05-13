@extends('layouts.app', ['title' => __('messages.supplier_performance'), 'heading' => __('messages.nav_purchasing')])

@section('content')
@include('app.purchasing._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_purchasing') }}</p>
            <h2><i class="fa-solid fa-chart-bar"></i> {{ __('messages.supplier_performance') }}</h2>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.supplier') }}</th>
                    <th style="text-align:right">{{ __('messages.total_pos') }}</th>
                    <th style="text-align:right">{{ __('messages.completed_pos') }}</th>
                    <th style="text-align:right">{{ __('messages.on_time_rate') }}</th>
                    <th style="text-align:right">{{ __('messages.total_ordered') }}</th>
                    <th style="text-align:right">{{ __('messages.total_received') }}</th>
                    <th style="text-align:right">{{ __('messages.total_returned') }}</th>
                    <th style="text-align:right">{{ __('messages.return_rate') }}</th>
                    <th style="text-align:right">{{ __('messages.outstanding_debt') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($stats as $row)
                @php
                    $onTimeColor = $row['on_time_rate'] >= 80 ? 'var(--teal)' : ($row['on_time_rate'] >= 50 ? 'var(--amber)' : 'var(--rose)');
                    $returnColor = $row['return_rate'] <= 5 ? 'var(--teal)' : ($row['return_rate'] <= 15 ? 'var(--amber)' : 'var(--rose)');
                @endphp
                <tr>
                    <td><strong>{{ $row['supplier']->name }}</strong></td>
                    <td style="text-align:right">{{ $row['total_pos'] }}</td>
                    <td style="text-align:right">{{ $row['completed_pos'] }}</td>
                    <td style="text-align:right;font-weight:700;color:{{ $onTimeColor }}">{{ $row['on_time_rate'] }}%</td>
                    <td style="text-align:right">{{ number_format($row['total_ordered'], 2) }}</td>
                    <td style="text-align:right">{{ number_format($row['total_received'], 2) }}</td>
                    <td style="text-align:right">{{ number_format($row['total_returned'], 2) }}</td>
                    <td style="text-align:right;font-weight:700;color:{{ $returnColor }}">{{ $row['return_rate'] }}%</td>
                    <td style="text-align:right;{{ $row['outstanding_debt'] > 0 ? 'color:var(--amber);font-weight:600' : '' }}">
                        Rp {{ number_format($row['outstanding_debt'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty-cell">{{ __('messages.no_suppliers') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
