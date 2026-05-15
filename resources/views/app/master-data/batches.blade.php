@extends('layouts.app', ['title' => __('messages.batch_lots'), 'heading' => __('messages.master_data')])

@section('content')
@include('app.master-data._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.master_data') }}</p>
            <h2><i class="fa-solid fa-layer-group"></i> {{ __('messages.batch_lots') }}</h2>
        </div>
        <a href="#modal-add-batch" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_batch') }}</a>
        <a href="{{ route('master-data.batches.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
        <a href="#modal-import-batches" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.batch_no') }}</th>
                    <th>{{ __('messages.lot_no') }}</th>
                    <th>{{ __('messages.product_name') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.manufactured_at') }}</th>
                    <th>{{ __('messages.expires_at') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($batches as $batch)
            @php
                $isExpired = $batch->expires_at && $batch->expires_at->isPast();
                $expiringSoon = $batch->expires_at && !$isExpired && $batch->expires_at->diffInDays(now()) <= 30;
            @endphp
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $batch->batch_no }}</strong></td>
                    <td>{{ $batch->lot_no ?: '—' }}</td>
                    <td>{{ $batch->product?->name ?? '—' }}</td>
                    <td>{{ number_format($batch->quantity) }}</td>
                    <td>{{ $batch->manufactured_at?->format('d M Y') ?? '—' }}</td>
                    <td>
                        @if ($batch->expires_at)
                            <span style="color:{{ $isExpired ? 'var(--rose)' : ($expiringSoon ? 'var(--amber)' : 'inherit') }};font-weight:{{ ($isExpired || $expiringSoon) ? '600' : '400' }}">
                                {{ $batch->expires_at->format('d M Y') }}
                                @if ($isExpired) <small>({{ __('messages.expired') }})</small>
                                @elseif ($expiringSoon) <small>({{ $batch->expires_at->diffForHumans() }})</small>
                                @endif
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.batches.destroy', $batch) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="7" style="padding:0">
                        <form method="POST" action="{{ route('master-data.batches.update', $batch) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.batch_no') }} <span class="req">*</span></span>
                                    <input name="batch_no" value="{{ $batch->batch_no }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.lot_no') }}</span>
                                    <input name="lot_no" value="{{ $batch->lot_no }}">
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.product_name') }}</span>
                                    <select name="product_id">
                                        <option value="">— {{ __('messages.select_product') }} —</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" {{ $batch->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label><span class="label-cap">{{ __('messages.quantity') }}</span>
                                    <input name="quantity" type="number" min="0" value="{{ $batch->quantity }}">
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.manufactured_at') }}</span>
                                    <input name="manufactured_at" type="date" value="{{ $batch->manufactured_at?->toDateString() }}">
                                </label>
                                <label><span class="label-cap">{{ __('messages.expires_at') }}</span>
                                    <input name="expires_at" type="date" value="{{ $batch->expires_at?->toDateString() }}">
                                </label>
                            </div>
                            <label><span class="label-cap">{{ __('messages.notes') }}</span>
                                <textarea name="notes" rows="2">{{ $batch->notes }}</textarea>
                            </label>
                            <div class="form-row-spread" style="justify-content:flex-end">
                                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="7" class="empty-cell">{{ __('messages.no_batch_lots') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- Add Batch Modal --}}
<div class="modal-overlay" id="modal-add-batch" role="dialog" aria-modal="true">
    <div class="modal-card" style="max-width:600px">
        <header>
            <h3><i class="fa-solid fa-layer-group"></i> {{ __('messages.add_batch') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('master-data.batches.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.batch_no') }} <span class="req">*</span></span>
                    <input name="batch_no" value="{{ old('batch_no') }}" required>
                </label>
                <label><span class="label-cap">{{ __('messages.lot_no') }}</span>
                    <input name="lot_no" value="{{ old('lot_no') }}">
                </label>
            </div>
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.product_name') }}</span>
                    <select name="product_id">
                        <option value="">— {{ __('messages.select_product') }} —</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.quantity') }}</span>
                    <input name="quantity" type="number" min="0" value="{{ old('quantity', 0) }}">
                </label>
            </div>
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.manufactured_at') }}</span>
                    <input name="manufactured_at" type="date" value="{{ old('manufactured_at') }}">
                </label>
                <label><span class="label-cap">{{ __('messages.expires_at') }}</span>
                    <input name="expires_at" type="date" value="{{ old('expires_at') }}">
                </label>
            </div>
            <label><span class="label-cap">{{ __('messages.notes') }}</span>
                <textarea name="notes" rows="2">{{ old('notes') }}</textarea>
            </label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

@include('partials._xl-import-modal', [
    'modalId'     => 'modal-import-batches',
    'title'       => __('messages.import') . ' Batch / Lot',
    'importRoute' => route('master-data.batches.import'),
    'columns'     => 'product, batch_no, lot_no, quantity, manufactured_at, expires_at, notes',
])
@endsection
