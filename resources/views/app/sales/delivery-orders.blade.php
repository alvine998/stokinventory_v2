@extends('layouts.app', ['title' => __('messages.delivery_order'), 'heading' => __('messages.nav_sales')])

@section('content')
@include('app.sales._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_sales') }}</p>
            <h2><i class="fa-solid fa-truck"></i> {{ __('messages.delivery_order') }}</h2>
        </div>
        <div class="head-actions">
            <a href="{{ route('sales.delivery-orders.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
            <a href="#modal-add-do" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_do') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.do_no') }}</th>
                    <th>{{ __('messages.so_no') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>{{ __('messages.expedition') }}</th>
                    <th>{{ __('messages.tracking_no') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.shipped_at') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($dos as $do)
            @php
                $doColors = ['draft'=>'','shipped'=>'','in_transit'=>'','delivered'=>'badge-active','failed'=>'badge-inactive','returned'=>'badge-inactive'];
            @endphp
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $do->do_no }}</strong></td>
                    <td>{{ $do->salesOrder?->so_no ?? '—' }}</td>
                    <td>{{ $do->customer?->name ?? '—' }}</td>
                    <td>{{ $do->expedition?->name ?? '—' }}</td>
                    <td>
                        @if($do->tracking_no && $do->trackingUrl())
                            <a href="{{ $do->trackingUrl() }}" target="_blank" rel="noopener" style="color:var(--teal)">{{ $do->tracking_no }} <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px"></i></a>
                        @else
                            {{ $do->tracking_no ?? '—' }}
                        @endif
                    </td>
                    <td><span class="badge-status {{ $doColors[$do->status] ?? '' }}">{{ __('messages.do_status_'.$do->status) }}</span></td>
                    <td>{{ $do->shipped_at?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.view_items') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-chevron-down':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <a href="#modal-do-status-{{ $do->id }}" class="icon-button" title="{{ __('messages.update_status') }}"><i class="fa-solid fa-map-pin"></i></a>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0">
                        <div style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                                <div>
                                    <p style="font-size:12px;font-weight:700;margin:0 0 8px;color:#555">{{ __('messages.items') }}</p>
                                    <table style="width:100%;font-size:13px">
                                        <thead><tr style="color:#888"><th style="text-align:left;padding:4px 0">{{ __('messages.product') }}</th><th style="text-align:right;padding:4px 0">{{ __('messages.qty') }}</th></tr></thead>
                                        <tbody>
                                        @foreach ($do->items as $item)
                                            <tr><td style="padding:3px 0">{{ $item->product_name }}</td><td style="text-align:right;padding:3px 0">{{ number_format($item->quantity, 2) }}</td></tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <p style="font-size:12px;font-weight:700;margin:0 0 8px;color:#555">{{ __('messages.tracking_history') }}</p>
                                    @forelse ($do->trackings as $t)
                                        <div style="font-size:12px;padding:4px 0;border-bottom:1px solid #eee">
                                            <span class="badge-status" style="font-size:10px">{{ $t->status }}</span>
                                            @if($t->location) <span style="color:#888">{{ $t->location }}</span> @endif<br>
                                            <span style="color:#555">{{ $t->description }}</span>
                                            <span style="float:right;color:#aaa">{{ $t->tracked_at->format('d M H:i') }}</span>
                                        </div>
                                    @empty
                                        <p style="font-size:12px;color:#aaa">—</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="8" class="empty-cell">{{ __('messages.no_dos') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $dos->links() }}
</section>

{{-- Update status modals --}}
@foreach ($dos as $do)
<div class="modal-overlay" id="modal-do-status-{{ $do->id }}">
    <div class="modal" style="max-width:400px">
        <div class="modal-head">
            <h3>{{ __('messages.update_status') }} — {{ $do->do_no }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.delivery-orders.status', $do) }}" class="modal-body">
            @csrf @method('PATCH')
            <div class="form-grid two">
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.status') }}</span>
                    <select name="status">
                        @foreach (['shipped','in_transit','delivered','failed','returned'] as $s)
                            <option value="{{ $s }}" {{ $do->status === $s ? 'selected' : '' }}>{{ __('messages.do_status_'.$s) }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.location') }}</span>
                    <input type="text" name="location" placeholder="Bandung sorting hub">
                </label>
                <label><span class="label-cap">{{ __('messages.date') }}</span>
                    <input type="datetime-local" name="tracked_at">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.description') }}</span>
                    <input type="text" name="description" placeholder="{{ __('messages.tracking_note_placeholder') }}">
                </label>
            </div>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Add DO modal --}}
<div class="modal-overlay" id="modal-add-do">
    <div class="modal" style="max-width:720px">
        <div class="modal-head">
            <h3>{{ __('messages.new_do') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.delivery-orders.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.so_reference') }}</span>
                    <select name="so_id">
                        <option value="">—</option>
                        @foreach ($sos as $so)
                            <option value="{{ $so->id }}">{{ $so->so_no }}{{ $so->customer ? ' – '.$so->customer->name : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.customer') }}</span>
                    <select name="customer_id">
                        <option value="">—</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
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
                <label><span class="label-cap">{{ __('messages.expedition') }}</span>
                    <select name="expedition_id">
                        <option value="">—</option>
                        @foreach ($expeditions as $e)
                            <option value="{{ $e->id }}">{{ $e->name }}{{ $e->code ? ' ('.$e->code.')' : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.tracking_no') }}</span>
                    <input type="text" name="tracking_no" maxlength="100" placeholder="{{ __('messages.tracking_no') }}">
                </label>
                <label><span class="label-cap">{{ __('messages.shipped_at') }}</span>
                    <input type="datetime-local" name="shipped_at">
                </label>
                <label><span class="label-cap">{{ __('messages.estimated_delivery_at') }}</span>
                    <input type="date" name="estimated_delivery_at">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.shipping_address') }}</span>
                    <textarea name="shipping_address" rows="2"></textarea>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2"></textarea>
                </label>
            </div>
            <p class="label-cap" style="margin:16px 0 8px">{{ __('messages.items') }} <span class="req">*</span></p>
            @include('app.purchasing._item-rows', ['products' => $products, 'prefix' => 'do-modal'])
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
