@extends('layouts.app', ['title' => __('messages.serial_number_tracking'), 'heading' => __('messages.nav_inventory')])

@section('content')
@include('app.inventory-ops._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_inventory') }}</p>
            <h2><i class="fa-solid fa-barcode"></i> {{ __('messages.serial_number_tracking') }}</h2>
        </div>
        <a href="#modal-add-serial" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_serial') }}</a>
        <a href="{{ route('inventory.serial-numbers.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
        <a href="#modal-import-serial-numbers" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
    </div>

    @include('partials.errors')

    {{-- Filters --}}
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
        <select name="product_id" onchange="this.form.submit()">
            <option value="">{{ __('messages.all_products') }}</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()">
            <option value="">{{ __('messages.all_statuses') }}</option>
            <option value="in_stock"  {{ request('status') === 'in_stock'  ? 'selected' : '' }}>{{ __('messages.sn_in_stock') }}</option>
            <option value="sold"      {{ request('status') === 'sold'      ? 'selected' : '' }}>{{ __('messages.sn_sold') }}</option>
            <option value="returned"  {{ request('status') === 'returned'  ? 'selected' : '' }}>{{ __('messages.sn_returned') }}</option>
            <option value="damaged"   {{ request('status') === 'damaged'   ? 'selected' : '' }}>{{ __('messages.sn_damaged') }}</option>
        </select>
        @if (request()->hasAny(['product_id','status']))
            <a href="{{ route('inventory.serial-numbers') }}" class="secondary-button" style="padding:7px 14px">{{ __('messages.reset') }}</a>
        @endif
    </form>

    @php
        $statusColors = [
            'in_stock' => 'badge-active',
            'sold'     => 'badge-inactive',
            'returned' => '',
            'damaged'  => '',
        ];
    @endphp

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.serial_no') }}</th>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.warehouse') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.notes') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($serials as $sn)
            <tbody class="row-group">
                <tr>
                    <td><code>{{ $sn->serial_no }}</code></td>
                    <td>{{ $sn->product?->name ?? '—' }}</td>
                    <td>{{ $sn->warehouse?->name ?? '—' }}</td>
                    <td>
                        <span class="badge-status {{ $statusColors[$sn->status] ?? '' }}">
                            {{ __('messages.sn_' . $sn->status) }}
                        </span>
                    </td>
                    <td>{{ $sn->notes ?: '—' }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('inventory.serial-numbers.destroy', $sn) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="6" style="padding:0">
                        <form method="POST" action="{{ route('inventory.serial-numbers.update', $sn) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.serial_no') }} <span class="req">*</span></span>
                                    <input name="serial_no" value="{{ $sn->serial_no }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.product') }} <span class="req">*</span></span>
                                    <select name="product_id" required>
                                        @foreach ($products as $p)
                                            <option value="{{ $p->id }}" {{ $sn->product_id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label><span class="label-cap">{{ __('messages.warehouse') }}</span>
                                    <select name="warehouse_id">
                                        <option value="">—</option>
                                        @foreach ($warehouses as $w)
                                            <option value="{{ $w->id }}" {{ $sn->warehouse_id === $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label><span class="label-cap">{{ __('messages.status') }} <span class="req">*</span></span>
                                    <select name="status" required>
                                        <option value="in_stock"  {{ $sn->status === 'in_stock'  ? 'selected' : '' }}>{{ __('messages.sn_in_stock') }}</option>
                                        <option value="sold"      {{ $sn->status === 'sold'      ? 'selected' : '' }}>{{ __('messages.sn_sold') }}</option>
                                        <option value="returned"  {{ $sn->status === 'returned'  ? 'selected' : '' }}>{{ __('messages.sn_returned') }}</option>
                                        <option value="damaged"   {{ $sn->status === 'damaged'   ? 'selected' : '' }}>{{ __('messages.sn_damaged') }}</option>
                                    </select>
                                </label>
                                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                                    <textarea name="notes" rows="2">{{ $sn->notes }}</textarea>
                                </label>
                            </div>
                            <div style="display:flex;gap:8px">
                                <button class="primary-button">{{ __('messages.save') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="6" class="empty-cell">{{ __('messages.no_serial_numbers') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>

    {{ $serials->links() }}
</section>

{{-- Add Modal --}}
<div class="modal-overlay" id="modal-add-serial">
    <div class="modal">
        <div class="modal-head">
            <h3>{{ __('messages.add_serial') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('inventory.serial-numbers.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.serial_no') }} <span class="req">*</span></span>
                    <input name="serial_no" required>
                </label>
                <label><span class="label-cap">{{ __('messages.product') }} <span class="req">*</span></span>
                    <select name="product_id" required>
                        <option value="">—</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.warehouse') }}</span>
                    <select name="warehouse_id">
                        <option value="">—</option>
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.status') }} <span class="req">*</span></span>
                    <select name="status" required>
                        <option value="in_stock">{{ __('messages.sn_in_stock') }}</option>
                        <option value="sold">{{ __('messages.sn_sold') }}</option>
                        <option value="returned">{{ __('messages.sn_returned') }}</option>
                        <option value="damaged">{{ __('messages.sn_damaged') }}</option>
                    </select>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2"></textarea>
                </label>
            </div>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

@include('partials._xl-import-modal', [
    'modalId'     => 'modal-import-serial-numbers',
    'title'       => __('messages.import') . ' Serial Numbers',
    'importRoute' => route('inventory.serial-numbers.import'),
    'columns'     => 'product, sku, warehouse, serial_no, status, notes',
])
@endsection
