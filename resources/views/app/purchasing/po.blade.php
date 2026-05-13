@extends('layouts.app', ['title' => __('messages.purchase_order'), 'heading' => __('messages.nav_purchasing')])

@section('content')
@include('app.purchasing._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_purchasing') }}</p>
            <h2><i class="fa-solid fa-file-invoice"></i> {{ __('messages.purchase_order') }}</h2>
        </div>
        <a href="#modal-add-po" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_po') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.po_no') }}</th>
                    <th>{{ __('messages.pr_no') }}</th>
                    <th>{{ __('messages.supplier') }}</th>
                    <th>{{ __('messages.total_amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.expected_at') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($pos as $po)
            <tbody class="row-group">
                @php
                    $poColors = ['draft'=>'','pending_approval'=>'','approved'=>'badge-active','rejected'=>'badge-inactive','partial'=>'','completed'=>'badge-active','cancelled'=>'badge-inactive'];
                @endphp
                <tr>
                    <td><strong>{{ $po->po_no }}</strong></td>
                    <td>{{ $po->purchaseRequest?->pr_no ?? '—' }}</td>
                    <td>{{ $po->supplier?->name ?? '—' }}</td>
                    <td>Rp {{ number_format($po->totalAmount(), 0, ',', '.') }}</td>
                    <td><span class="badge-status {{ $poColors[$po->status] ?? '' }}">{{ __('messages.po_status_'.$po->status) }}</span></td>
                    <td>{{ $po->expected_at?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $po->ordered_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.view_items') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-chevron-down':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            @if(!in_array($po->status, ['approved','partial','completed']))
                            <form method="POST" action="{{ route('purchasing.po.destroy', $po) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0">
                        <div style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <table style="width:100%;font-size:13px">
                                <thead><tr style="color:#888"><th style="text-align:left;padding:4px 8px">{{ __('messages.product') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.qty') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.received_qty') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.unit_price') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.subtotal') }}</th></tr></thead>
                                <tbody>
                                @foreach ($po->items as $item)
                                    <tr>
                                        <td style="padding:4px 8px">{{ $item->product_name }}</td>
                                        <td style="padding:4px 8px;text-align:right">{{ number_format($item->quantity, 2) }}</td>
                                        <td style="padding:4px 8px;text-align:right;color:{{ $item->received_qty > 0 ? 'var(--teal)' : '#888' }}">{{ number_format($item->received_qty, 2) }}</td>
                                        <td style="padding:4px 8px;text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td style="padding:4px 8px;text-align:right;font-weight:600">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @if($po->notes)<p style="margin:12px 8px 0;font-size:13px;color:#555"><strong>{{ __('messages.notes') }}:</strong> {{ $po->notes }}</p>@endif
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="8" class="empty-cell">{{ __('messages.no_pos') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $pos->links() }}
</section>

{{-- Add Modal --}}
<div class="modal-overlay" id="modal-add-po">
    <div class="modal" style="max-width:700px">
        <div class="modal-head">
            <h3>{{ __('messages.new_po') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('purchasing.po.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.pr_reference') }}</span>
                    <select name="purchase_request_id">
                        <option value="">— {{ __('messages.none') }} —</option>
                        @foreach ($prs as $pr)
                            <option value="{{ $pr->id }}">{{ $pr->pr_no }}{{ $pr->supplier ? ' – '.$pr->supplier->name : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.supplier') }}</span>
                    <select name="supplier_id">
                        <option value="">—</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
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
                <label><span class="label-cap">{{ __('messages.expected_at') }}</span>
                    <input type="date" name="expected_at">
                </label>
                <label><span class="label-cap">{{ __('messages.date') }}</span>
                    <input type="datetime-local" name="ordered_at">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2"></textarea>
                </label>
            </div>
            <p class="label-cap" style="margin:16px 0 8px">{{ __('messages.items') }} <span class="req">*</span></p>
            @include('app.purchasing._item-rows', ['products' => $products, 'prefix' => 'modal-add-po'])
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
