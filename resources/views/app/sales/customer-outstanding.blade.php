@extends('layouts.app', ['title' => __('messages.customer_outstanding'), 'heading' => __('messages.nav_sales')])

@section('content')
@include('app.sales._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_sales') }}</p>
            <h2><i class="fa-solid fa-circle-exclamation"></i> {{ __('messages.customer_outstanding') }}</h2>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.customer') }}</th>
                    <th class="num">{{ __('messages.total_orders') }}</th>
                    <th class="num">{{ __('messages.total_invoiced') }}</th>
                    <th class="num">{{ __('messages.total_collected') }}</th>
                    <th class="num">{{ __('messages.outstanding') }}</th>
                    <th class="num">{{ __('messages.overdue_count') }}</th>
                    <th class="num">{{ __('messages.total_returns') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($stats->sortByDesc('outstanding') as $row)
                <tr>
                    <td><strong>{{ $row['customer']->name }}</strong>
                        @if($row['customer']->phone) <span style="font-size:11px;color:#888;display:block">{{ $row['customer']->phone }}</span> @endif
                    </td>
                    <td class="num">{{ number_format($row['total_orders']) }}</td>
                    <td class="num">Rp {{ number_format($row['total_invoiced'], 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($row['total_paid'], 0, ',', '.') }}</td>
                    <td class="num" style="{{ $row['outstanding'] > 0 ? 'font-weight:600;color:var(--amber)' : 'color:var(--teal)' }}">
                        Rp {{ number_format($row['outstanding'], 0, ',', '.') }}
                    </td>
                    <td class="num" style="{{ $row['overdue_count'] > 0 ? 'font-weight:700;color:var(--rose)' : '' }}">
                        {{ $row['overdue_count'] ?: '—' }}
                    </td>
                    <td class="num">{{ number_format($row['total_returns']) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-cell">{{ __('messages.no_customer_activity') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
