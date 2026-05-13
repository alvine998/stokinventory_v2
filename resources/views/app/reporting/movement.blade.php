@extends('layouts.app', ['title' => __('messages.nav_report_movement'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-bolt"></i> {{ __('messages.nav_report_movement') }}</h2>
        </div>
        <div class="export-group">
            <a href="{{ route('reporting.movement.export', array_merge(request()->query(), ['format'=>'excel'])) }}" class="secondary-button btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
            <a href="{{ route('reporting.movement.export', array_merge(request()->query(), ['format'=>'pdf'])) }}" class="secondary-button btn-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        </div>
    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('reporting.movement') }}" style="display:contents">
            <label>
                <span class="label-cap">{{ __('messages.start_date') }}</span>
                <input type="date" name="start_date" value="{{ $startDate }}">
            </label>
            <label>
                <span class="label-cap">{{ __('messages.end_date') }}</span>
                <input type="date" name="end_date" value="{{ $endDate }}">
            </label>
            <label>
                <span class="label-cap">{{ __('messages.sort') }}</span>
                <select name="sort">
                    <option value="desc" {{ $sort === 'desc' ? 'selected' : '' }}>{{ __('messages.fast_moving') }}</option>
                    <option value="asc"  {{ $sort === 'asc'  ? 'selected' : '' }}>{{ __('messages.slow_moving') }}</option>
                </select>
            </label>
            <label>
                <span class="label-cap">{{ __('messages.show') }}</span>
                <select name="limit">
                    @foreach ([10, 20, 50, 100] as $l)
                        <option value="{{ $l }}" {{ $limit == $l ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </label>
            <button class="primary-button">{{ __('messages.apply') }}</button>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th class="num">{{ __('messages.current_stock') }}</th>
                    <th class="num">{{ __('messages.qty_out_period') }}</th>
                    <th class="num">{{ __('messages.movement_count') }}</th>
                    <th>{{ __('messages.movement_level') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $i => $p)
            @php
                if ($p->total_out >= 50)      { [$lvLabel, $lvBg, $lvColor] = [__('messages.fast_moving'),   '#f0fdfa', 'var(--teal)']; }
                elseif ($p->total_out >= 10)  { [$lvLabel, $lvBg, $lvColor] = [__('messages.medium_moving'), '#eaf1ff', 'var(--blue)']; }
                elseif ($p->total_out >= 1)   { [$lvLabel, $lvBg, $lvColor] = [__('messages.slow_moving'),   '#fffbeb', 'var(--amber)']; }
                else                          { [$lvLabel, $lvBg, $lvColor] = [__('messages.no_movement'),   '#fff0f3', 'var(--rose)']; }
            @endphp
            <tr>
                <td style="color:#aaa;font-size:12px">{{ $i + 1 }}</td>
                <td><strong>{{ $p->name }}</strong></td>
                <td><code>{{ $p->sku ?? '—' }}</code></td>
                <td class="num">{{ number_format($p->current_stock, 2) }} {{ $p->unit }}</td>
                <td class="num" style="font-weight:700;color:{{ $lvColor }}">{{ number_format($p->total_out, 2) }}</td>
                <td class="num">{{ $p->movement_count }}</td>
                <td><span class="badge-status" style="background:{{ $lvBg }};color:{{ $lvColor }}">{{ $lvLabel }}</span></td>
            </tr>
            @empty
            <tr><td colspan="7" class="empty-cell">{{ __('messages.no_data') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="report-note"><i class="fa-solid fa-circle-info"></i> {{ __('messages.movement_note') }}</p>
</section>
@endsection
