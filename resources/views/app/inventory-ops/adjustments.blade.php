@extends('layouts.app', ['title' => __('messages.stock_adjustment'), 'heading' => __('messages.nav_inventory')])

@section('content')
@include('app.inventory-ops._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_inventory') }}</p>
            <h2><i class="fa-solid fa-sliders"></i> {{ __('messages.stock_adjustment') }}</h2>
        </div>
        <a href="#modal-add-adjustment" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_adjustment') }}</a>
        <a href="{{ route('inventory.adjustments.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.warehouse') }}</th>
                    <th>{{ __('messages.type') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.reason') }}</th>
                    <th>{{ __('messages.reference_no') }}</th>
                    <th>{{ __('messages.by') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($adjustments as $adj)
                <tr>
                    <td>{{ $adj->adjusted_at->format('d M Y H:i') }}</td>
                    <td>{{ $adj->product?->name ?? '—' }}</td>
                    <td>{{ $adj->warehouse?->name ?? '—' }}</td>
                    <td>
                        <span class="badge-status {{ $adj->type === 'add' ? 'badge-active' : 'badge-inactive' }}">
                            {{ $adj->type === 'add' ? '+' . __('messages.add') : '−' . __('messages.remove') }}
                        </span>
                    </td>
                    <td>{{ number_format($adj->quantity) }}</td>
                    <td>{{ $adj->reason ?: '—' }}</td>
                    <td>{{ $adj->reference_no ?: '—' }}</td>
                    <td>{{ $adj->adjustedBy?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty-cell">{{ __('messages.no_adjustments') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $adjustments->links() }}
</section>

{{-- Add Modal --}}
<div class="modal-overlay" id="modal-add-adjustment">
    <div class="modal">
        <div class="modal-head">
            <h3>{{ __('messages.add_adjustment') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('inventory.adjustments.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.product') }} <span class="req">*</span></span>
                    <select name="product_id" required>
                        <option value="">—</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.warehouse') }} <span class="req">*</span></span>
                    <select name="warehouse_id" required>
                        <option value="">—</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.type') }} <span class="req">*</span></span>
                    <select name="type" required>
                        <option value="add">+ {{ __('messages.add') }}</option>
                        <option value="remove">− {{ __('messages.remove') }}</option>
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.quantity') }} <span class="req">*</span></span>
                    <input type="number" name="quantity" min="1" required>
                </label>
                <label><span class="label-cap">{{ __('messages.reason') }}</span>
                    <input name="reason" maxlength="255">
                </label>
                <label><span class="label-cap">{{ __('messages.reference_no') }}</span>
                    <input name="reference_no" maxlength="100">
                </label>
                <label><span class="label-cap">{{ __('messages.date') }}</span>
                    <input type="datetime-local" name="adjusted_at">
                </label>
            </div>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
