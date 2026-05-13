@extends('layouts.app', ['title' => __('messages.sales_return'), 'heading' => __('messages.nav_sales')])

@section('content')
@include('app.sales._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_sales') }}</p>
            <h2><i class="fa-solid fa-rotate-left"></i> {{ __('messages.sales_return') }}</h2>
        </div>
        <a href="#modal-add-return" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_sales_return') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.return_no') }}</th>
                    <th>{{ __('messages.so_no') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>{{ __('messages.reason') }}</th>
                    <th>{{ __('messages.items') }}</th>
                    <th>{{ __('messages.total_amount') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.returned_by') }}</th>
                    <th></th>
                </tr>
            </thead>
            @forelse ($returns as $ret)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $ret->return_no }}</strong></td>
                    <td>{{ $ret->salesOrder?->so_no ?? '—' }}</td>
                    <td>{{ $ret->customer?->name ?? '—' }}</td>
                    <td>{{ Str::limit($ret->reason ?? '—', 40) }}</td>
                    <td>{{ $ret->items->count() }}</td>
                    <td>Rp {{ number_format($ret->totalAmount(), 0, ',', '.') }}</td>
                    <td>{{ $ret->returned_at->format('d M Y') }}</td>
                    <td>{{ $ret->returnedBy?->name ?? '—' }}</td>
                    <td>
                        <button type="button" class="icon-button" title="{{ __('messages.view_items') }}"
                            onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-chevron-down':'fa-solid fa-chevron-up'">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="9" style="padding:0">
                        <div style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <table style="width:100%;font-size:13px">
                                <thead><tr style="color:#888"><th style="text-align:left;padding:4px 8px">{{ __('messages.product') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.qty') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.unit_price') }}</th></tr></thead>
                                <tbody>
                                @foreach ($ret->items as $item)
                                <tr>
                                    <td style="padding:4px 8px">{{ $item->product_name }}</td>
                                    <td style="padding:4px 8px;text-align:right">{{ number_format($item->quantity, 2) }}</td>
                                    <td style="padding:4px 8px;text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @if($ret->notes)<p style="margin:12px 8px 0;font-size:13px;color:#555"><strong>{{ __('messages.notes') }}:</strong> {{ $ret->notes }}</p>@endif
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="9" class="empty-cell">{{ __('messages.no_sales_returns') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $returns->links() }}
</section>

{{-- Add Return modal --}}
<div class="modal-overlay" id="modal-add-return">
    <div class="modal" style="max-width:720px">
        <div class="modal-head">
            <h3>{{ __('messages.new_sales_return') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.returns.store') }}" class="modal-body">
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
                <label><span class="label-cap">{{ __('messages.do_reference') }}</span>
                    <select name="do_id">
                        <option value="">—</option>
                        @foreach ($dos as $do)
                            <option value="{{ $do->id }}">{{ $do->do_no }}</option>
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
                <label><span class="label-cap">{{ __('messages.date') }}</span>
                    <input type="datetime-local" name="returned_at">
                </label>
                <label><span class="label-cap">{{ __('messages.reason') }}</span>
                    <input type="text" name="reason" maxlength="255">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2"></textarea>
                </label>
            </div>
            <p class="label-cap" style="margin:16px 0 8px">{{ __('messages.items') }} <span class="req">*</span></p>
            <div class="item-rows-header" style="display:grid;grid-template-columns:1fr 100px 130px 24px;gap:8px;padding:0 0 4px;font-size:11px;font-weight:700;text-transform:uppercase;color:#888;letter-spacing:.04em">
                <span>{{ __('messages.product') }}</span><span>{{ __('messages.qty') }}</span><span>{{ __('messages.unit_price') }}</span><span></span>
            </div>
            <div class="item-rows" id="ret-modal-items">
                <div class="item-row" style="display:grid;grid-template-columns:1fr 100px 130px 24px;gap:8px;margin-bottom:6px;align-items:center">
                    <select name="items[0][product_id]" required>
                        <option value="">— {{ __('messages.select_product') }} —</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}{{ $p->sku ? ' ('.$p->sku.')' : '' }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="items[0][quantity]" min="0.01" step="0.01" placeholder="1" required>
                    <input type="number" name="items[0][unit_price]" min="0" step="0.01" placeholder="0">
                    <button type="button" class="icon-button remove-item-row" style="color:var(--rose)"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <button type="button" class="secondary-button" style="font-size:12px;padding:5px 12px;margin-top:4px" data-add-items="ret-modal-items">
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
