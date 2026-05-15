@extends('layouts.app', ['title' => __('messages.sales_order'), 'heading' => __('messages.nav_sales')])

@section('content')
@include('app.sales._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_sales') }}</p>
            <h2><i class="fa-solid fa-file-contract"></i> {{ __('messages.sales_order') }}</h2>
        </div>
        <div class="head-actions">
            <a href="{{ route('sales.orders.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
            <a href="#modal-add-so" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_so') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.so_no') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>{{ __('messages.price_level') }}</th>
                    <th>{{ __('messages.items') }}</th>
                    <th>{{ __('messages.total_amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($orders as $so)
            @php
                $soColors = ['draft'=>'','confirmed'=>'','processing'=>'','partially_delivered'=>'','delivered'=>'badge-active','cancelled'=>'badge-inactive'];
            @endphp
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $so->so_no }}</strong></td>
                    <td>{{ $so->customer?->name ?? '—' }}</td>
                    <td>{{ $so->priceLevel?->name ?? '—' }}</td>
                    <td>{{ $so->items->count() }}</td>
                    <td>Rp {{ number_format($so->totalAmount(), 0, ',', '.') }}</td>
                    <td><span class="badge-status {{ $soColors[$so->status] ?? '' }}">{{ __('messages.so_status_'.$so->status) }}</span></td>
                    <td>{{ $so->ordered_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.view_items') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-chevron-down':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            @if(!in_array($so->status, ['processing','partially_delivered','delivered']))
                            <a href="#modal-status-{{ $so->id }}" class="icon-button" title="{{ __('messages.update_status') }}"><i class="fa-solid fa-pen"></i></a>
                            <form method="POST" action="{{ route('sales.orders.destroy', $so) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)"><i class="fa-solid fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0">
                        <div style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <table style="width:100%;font-size:13px">
                                <thead><tr style="color:#888"><th style="text-align:left;padding:4px 8px">{{ __('messages.product') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.qty') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.unit_price') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.discount') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.subtotal') }}</th></tr></thead>
                                <tbody>
                                @foreach ($so->items as $item)
                                <tr>
                                    <td style="padding:4px 8px">{{ $item->product_name }}</td>
                                    <td style="padding:4px 8px;text-align:right">{{ number_format($item->quantity, 2) }}</td>
                                    <td style="padding:4px 8px;text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td style="padding:4px 8px;text-align:right">{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '—' }}</td>
                                    <td style="padding:4px 8px;text-align:right;font-weight:600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @if($so->notes)<p style="margin:12px 8px 0;font-size:13px;color:#555"><strong>{{ __('messages.notes') }}:</strong> {{ $so->notes }}</p>@endif
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="8" class="empty-cell">{{ __('messages.no_sos') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $orders->links() }}
</section>

{{-- Status modals --}}
@foreach ($orders as $so)
<div class="modal-overlay" id="modal-status-{{ $so->id }}">
    <div class="modal" style="max-width:360px">
        <div class="modal-head">
            <h3>{{ __('messages.update_status') }} — {{ $so->so_no }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.orders.status', $so) }}" class="modal-body">
            @csrf @method('PATCH')
            <label><span class="label-cap">{{ __('messages.status') }}</span>
                <select name="status">
                    @foreach (['draft','confirmed','processing','partially_delivered','delivered','cancelled'] as $s)
                        <option value="{{ $s }}" {{ $so->status === $s ? 'selected' : '' }}>{{ __('messages.so_status_'.$s) }}</option>
                    @endforeach
                </select>
            </label>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Add SO modal --}}
<div class="modal-overlay" id="modal-add-so">
    <div class="modal" style="max-width:720px">
        <div class="modal-head">
            <h3>{{ __('messages.new_so') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.orders.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.customer') }}</span>
                    <select name="customer_id">
                        <option value="">—</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.price_level') }}</span>
                    <select name="price_level_id">
                        <option value="">— {{ __('messages.standard') }} —</option>
                        @foreach ($priceLevels as $pl)
                            <option value="{{ $pl->id }}">{{ $pl->name }} ({{ $pl->discount_percent > 0 ? '-'.$pl->discount_percent.'%' : __('messages.no_discount') }})</option>
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
                <label><span class="label-cap">{{ __('messages.date') }}</span>
                    <input type="datetime-local" name="ordered_at">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2"></textarea>
                </label>
            </div>
            <p class="label-cap" style="margin:16px 0 8px">{{ __('messages.items') }} <span class="req">*</span></p>
            <div class="item-rows-header" style="display:grid;grid-template-columns:1fr 100px 130px 90px 24px;gap:8px;padding:0 0 4px;font-size:11px;font-weight:700;text-transform:uppercase;color:#888;letter-spacing:.04em">
                <span>{{ __('messages.product') }}</span><span>{{ __('messages.qty') }}</span><span>{{ __('messages.unit_price') }}</span><span>{{ __('messages.discount') }} %</span><span></span>
            </div>
            <div class="item-rows" id="so-modal-items">
                <div class="item-row" style="display:grid;grid-template-columns:1fr 100px 130px 90px 24px;gap:8px;margin-bottom:6px;align-items:center">
                    <select name="items[0][product_id]" required>
                        <option value="">— {{ __('messages.select_product') }} —</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}{{ $p->sku ? ' ('.$p->sku.')' : '' }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="items[0][quantity]" min="0.01" step="0.01" placeholder="1" required>
                    <input type="number" name="items[0][unit_price]" min="0" step="0.01" placeholder="0">
                    <input type="number" name="items[0][discount_percent]" min="0" max="100" step="0.1" placeholder="0">
                    <button type="button" class="icon-button remove-item-row" style="color:var(--rose)"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <button type="button" class="secondary-button" style="font-size:12px;padding:5px 12px;margin-top:4px" data-add-items="so-modal-items">
                <i class="fa-solid fa-plus"></i> {{ __('messages.add_item') }}
            </button>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('app.purchasing._item-rows-js')
@endpush
