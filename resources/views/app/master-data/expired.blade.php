@extends('layouts.app', ['title' => __('messages.expired_products'), 'heading' => __('messages.master_data')])

@section('content')
@include('app.master-data._subnav')

{{-- Already Expired --}}
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.master_data') }}</p>
            <h2><i class="fa-solid fa-triangle-exclamation" style="color:var(--rose)"></i> {{ __('messages.already_expired') }}</h2>
        </div>
        <a href="{{ route('master-data.batches') }}" class="secondary-button">
            <i class="fa-solid fa-layer-group"></i> {{ __('messages.batch_lots') }}
        </a>
        <a href="{{ route('master-data.expired.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.batch_no') }}</th>
                    <th>{{ __('messages.lot_no') }}</th>
                    <th>{{ __('messages.product_name') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.expires_at') }}</th>
                    <th>{{ __('messages.notes') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($expiredBatches as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_no }}</strong></td>
                    <td>{{ $batch->lot_no ?: '—' }}</td>
                    <td>{{ $batch->product?->name ?? '—' }}</td>
                    <td>{{ number_format($batch->quantity) }}</td>
                    <td><span style="color:var(--rose);font-weight:600">{{ $batch->expires_at->format('d M Y') }}</span><br><small class="text-muted">{{ $batch->expires_at->diffForHumans() }}</small></td>
                    <td>{{ $batch->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-cell">{{ __('messages.no_expired') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Expiring Soon --}}
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.master_data') }}</p>
            <h2><i class="fa-solid fa-clock" style="color:var(--amber)"></i> {{ __('messages.expiring_within') }} {{ $threshold }} {{ __('messages.days') }}</h2>
        </div>
        <form method="GET" action="{{ route('master-data.expired') }}" style="display:flex;gap:8px;align-items:center">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px">
                {{ __('messages.expiring_within') }}
                <input name="days" type="number" min="1" max="365" value="{{ $threshold }}" style="width:72px">
                {{ __('messages.days') }}
            </label>
            <button class="secondary-button">{{ __('messages.update') }}</button>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.batch_no') }}</th>
                    <th>{{ __('messages.lot_no') }}</th>
                    <th>{{ __('messages.product_name') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.expires_at') }}</th>
                    <th>{{ __('messages.notes') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($expiringSoon as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_no }}</strong></td>
                    <td>{{ $batch->lot_no ?: '—' }}</td>
                    <td>{{ $batch->product?->name ?? '—' }}</td>
                    <td>{{ number_format($batch->quantity) }}</td>
                    <td><span style="color:var(--amber);font-weight:600">{{ $batch->expires_at->format('d M Y') }}</span><br><small class="text-muted">{{ $batch->expires_at->diffForHumans() }}</small></td>
                    <td>{{ $batch->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-cell">{{ __('messages.no_expired') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
