@extends('layouts.app', ['title' => __('messages.transfer_warehouse'), 'heading' => __('messages.nav_inventory')])

@section('content')
@include('app.inventory-ops._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_inventory') }}</p>
            <h2><i class="fa-solid fa-arrow-right-arrow-left"></i> {{ __('messages.transfer_warehouse') }}</h2>
        </div>
        <a href="#modal-add-transfer" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_transfer') }}</a>
        <a href="{{ route('inventory.transfers.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.from_warehouse') }}</th>
                    <th>{{ __('messages.to_warehouse') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.reference_no') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.by') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($transfers as $tr)
                <tr>
                    <td>{{ $tr->transferred_at->format('d M Y H:i') }}</td>
                    <td>{{ $tr->product?->name ?? '—' }}</td>
                    <td>{{ $tr->fromWarehouse?->name ?? '—' }}</td>
                    <td>{{ $tr->toWarehouse?->name ?? '—' }}</td>
                    <td>{{ number_format($tr->quantity) }}</td>
                    <td>{{ $tr->reference_no ?: '—' }}</td>
                    <td><span class="badge-status badge-active">{{ ucfirst($tr->status) }}</span></td>
                    <td>{{ $tr->transferredBy?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty-cell">{{ __('messages.no_transfers') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $transfers->links() }}
</section>

{{-- Add Modal --}}
<div class="modal-overlay" id="modal-add-transfer">
    <div class="modal">
        <div class="modal-head">
            <h3>{{ __('messages.add_transfer') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('inventory.transfers.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.product') }} <span class="req">*</span></span>
                    <select name="product_id" required>
                        <option value="">—</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.from_warehouse') }} <span class="req">*</span></span>
                    <select name="from_warehouse_id" required>
                        <option value="">—</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.to_warehouse') }} <span class="req">*</span></span>
                    <select name="to_warehouse_id" required>
                        <option value="">—</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.quantity') }} <span class="req">*</span></span>
                    <input type="number" name="quantity" min="1" required>
                </label>
                <label><span class="label-cap">{{ __('messages.reference_no') }}</span>
                    <input name="reference_no" maxlength="100">
                </label>
                <label><span class="label-cap">{{ __('messages.date') }}</span>
                    <input type="datetime-local" name="transferred_at">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2" maxlength="500"></textarea>
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
