@extends('layouts.app', ['title' => __('messages.profit_loss'), 'heading' => __('messages.nav_finance')])

@section('content')
@include('app.finance._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_finance') }}</p>
            <h2><i class="fa-solid fa-chart-line"></i> {{ __('messages.profit_loss') }}</h2>
        </div>
    </div>

    {{-- Date filter --}}
    <form method="GET" action="{{ route('finance.profit-loss') }}" style="display:flex;gap:12px;align-items:flex-end;margin-bottom:24px;flex-wrap:wrap">
        <label>
            <span class="label-cap">{{ __('messages.start_date') }}</span>
            <input type="date" name="start_date" value="{{ $startDate }}">
        </label>
        <label>
            <span class="label-cap">{{ __('messages.end_date') }}</span>
            <input type="date" name="end_date" value="{{ $endDate }}">
        </label>
        <button class="primary-button">{{ __('messages.apply') }}</button>
    </form>

    {{-- P&L Statement --}}
    <div style="max-width:600px">
        <div style="background:#f6fafc;border-radius:12px;padding:24px;font-size:14px">
            <p style="font-weight:700;font-size:16px;margin-bottom:16px">
                {{ __('messages.profit_loss') }}
                <span style="font-weight:400;color:#888;font-size:12px;margin-left:8px">
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            </p>

            {{-- Revenue --}}
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #e3ecef">
                <span style="color:#555">{{ __('messages.revenue') }}</span>
                <span style="font-weight:600;color:var(--teal)">Rp {{ number_format($revenue, 0, ',', '.') }}</span>
            </div>

            {{-- COGS --}}
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #e3ecef">
                <span style="color:#555">{{ __('messages.cogs') }}</span>
                <span style="font-weight:600;color:var(--rose)">− Rp {{ number_format($cogs, 0, ',', '.') }}</span>
            </div>

            {{-- Gross Profit --}}
            <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:2px solid #c8dde8;margin-bottom:4px">
                <span style="font-weight:700">{{ __('messages.gross_profit') }}</span>
                <span style="font-weight:700;color:{{ $grossProfit >= 0 ? 'var(--teal)' : 'var(--rose)' }}">
                    Rp {{ number_format($grossProfit, 0, ',', '.') }}
                </span>
            </div>

            {{-- Expenses --}}
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #e3ecef">
                <span style="color:#555">{{ __('messages.operating_expenses') }}</span>
                <span style="font-weight:600;color:var(--rose)">− Rp {{ number_format($expenses, 0, ',', '.') }}</span>
            </div>

            {{-- Net Profit --}}
            <div style="display:flex;justify-content:space-between;padding:14px 0 0;margin-top:4px">
                <span style="font-weight:700;font-size:16px">{{ __('messages.net_profit') }}</span>
                <span style="font-weight:700;font-size:18px;color:{{ $netProfit >= 0 ? 'var(--teal)' : 'var(--rose)' }}">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </span>
            </div>

            @php $margin = $revenue > 0 ? round($netProfit / $revenue * 100, 1) : 0; @endphp
            <p style="font-size:12px;color:#888;margin-top:8px">
                {{ __('messages.net_margin') }}: <strong style="color:{{ $margin >= 0 ? 'var(--teal)' : 'var(--rose)' }}">{{ $margin }}%</strong>
            </p>
        </div>

        <p style="font-size:12px;color:#aaa;margin-top:16px"><i class="fa-solid fa-circle-info"></i> {{ __('messages.pl_note') }}</p>
    </div>
</section>
@endsection
