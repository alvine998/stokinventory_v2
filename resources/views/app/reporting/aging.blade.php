@extends('layouts.app', ['title' => __('messages.nav_report_aging'), 'heading' => __('messages.nav_reporting')])

@section('content')
@include('app.reporting._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_reporting') }}</p>
            <h2><i class="fa-solid fa-hourglass-half"></i> {{ __('messages.nav_report_aging') }}</h2>
        </div>
        <div class="export-group">
            <a href="{{ route('reporting.aging.export', ['format'=>'excel']) }}" class="secondary-button btn-excel"><i class="fa-solid fa-file-excel"></i> Excel</a>
            <a href="{{ route('reporting.aging.export', ['format'=>'pdf']) }}" class="secondary-button btn-pdf"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        </div>
    </div>

    {{-- Bucket summary --}}
    @php
        $buckets = [
            'fresh'    => ['< 30 '.__('messages.days'), 'var(--teal)',   '#f0fdfa', 'fa-seedling'],
            'aging_30' => ['30–60 '.__('messages.days'),'var(--blue)',   '#eaf1ff', 'fa-clock'],
            'aging_60' => ['60–90 '.__('messages.days'),'var(--amber)',  '#fffbeb', 'fa-hourglass-half'],
            'aged'     => ['> 90 '.__('messages.days'), 'var(--rose)',   '#fff0f3', 'fa-skull'],
            'unknown'  => [__('messages.no_in_movement'),'#6b7280',     '#f3f4f6', 'fa-question-circle'],
        ];
    @endphp
    <div class="stat-row" style="margin-bottom:24px">
        @foreach ($buckets as $key => [$blabel, $bcolor, $bbg, $bicon])
        <div class="stat-card" style="border-top:3px solid {{ $bcolor }}">
            <span class="stat-label" style="color:{{ $bcolor }}">
                <i class="fa-solid {{ $bicon }}"></i> {{ $blabel }}
            </span>
            <span class="stat-value" style="color:{{ $bcolor }}">{{ $bucketCounts[$key] ?? 0 }}</span>
        </div>
        @endforeach
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th class="num">{{ __('messages.current_stock') }}</th>
                    <th>{{ __('messages.last_stock_in') }}</th>
                    <th class="num">{{ __('messages.age_days') }}</th>
                    <th>{{ __('messages.aging_bucket') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $row)
            @php [$blabel, $bcolor, $bbg, $bicon] = $buckets[$row['bucket']]; @endphp
            <tr>
                <td><strong>{{ $row['product']->name }}</strong></td>
                <td><code>{{ $row['product']->sku ?? '—' }}</code></td>
                <td class="num">{{ number_format($row['product']->current_stock, 2) }} {{ $row['product']->unit }}</td>
                <td>{{ $row['last_in_at'] ? \Carbon\Carbon::parse($row['last_in_at'])->format('d M Y') : '—' }}</td>
                <td class="num" style="font-weight:600;color:{{ $bcolor }}">{{ $row['age_days'] ?? '—' }}</td>
                <td>
                    <span class="badge-status" style="background:{{ $bbg }};color:{{ $bcolor }}">
                        <i class="fa-solid {{ $bicon }}"></i> {{ $blabel }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="empty-cell">{{ __('messages.no_stock') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="report-note"><i class="fa-solid fa-circle-info"></i> {{ __('messages.aging_note') }}</p>
</section>
@endsection
