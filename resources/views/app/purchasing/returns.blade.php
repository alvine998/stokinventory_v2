@extends('layouts.app', ['title' => __('messages.purchase_return'), 'heading' => __('messages.nav_purchasing')])

@section('content')
@include('app.purchasing._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_purchasing') }}</p>
            <h2><i class="fa-solid fa-rotate-left"></i> {{ __('messages.purchase_return') }}</h2>
        </div>
        <a href="#modal-add-return" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_return') }}</a>
        <a href="{{ route('purchasing.returns.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.return_no') }}</th>
                    <th>{{ __('messages.grn_no') }}</th>
                    <th>{{ __('messages.supplier') }}</th>
                    <th>{{ __('messages.reason') }}</th>
                    <th>{{ __('messages.items') }}</th>
                    <th>{{ __('messages.total_amount') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.by') }}</th>
                </tr>
            </thead>
            @forelse ($returns as $ret)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $ret->return_no }}</strong></td>
                    <td>{{ $ret->grn?->grn_no ?? '—' }}</td>
                    <td>{{ $ret->supplier?->name ?? '—' }}</td>
                    <td>{{ $ret->reason ? \Str::limit($ret->reason, 40) : '—' }}</td>
                    <td>{{ $ret->items->count() }}</td>
                    <td>Rp {{ number_format($ret->totalAmount(), 0, ',', '.') }}</td>
                    <td>{{ $ret->returned_at->format('d M Y') }}</td>
                    <td>{{ $ret->returnedBy?->name ?? '—' }}</td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0">
                        <div style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <table style="width:100%;font-size:13px">
                                <thead><tr style="color:#888"><th style="text-align:left;padding:4px 8px">{{ __('messages.product') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.qty') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.unit_price') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.subtotal') }}</th></tr></thead>
                                <tbody>
                                @foreach ($ret->items as $item)
                                    <tr>
                                        <td style="padding:4px 8px">{{ $item->product_name }}</td>
                                        <td style="padding:4px 8px;text-align:right">{{ number_format($item->quantity, 2) }}</td>
                                        <td style="padding:4px 8px;text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td style="padding:4px 8px;text-align:right;font-weight:600">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
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
                <tbody><tr><td colspan="8" class="empty-cell">{{ __('messages.no_returns') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $returns->links() }}
</section>

{{-- Add Modal --}}
<div class="modal-overlay" id="modal-add-return">
    <div class="modal" style="max-width:700px">
        <div class="modal-head">
            <h3>{{ __('messages.new_return') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('purchasing.returns.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.grn_reference') }}</span>
                    <select name="grn_id">
                        <option value="">— {{ __('messages.none') }} —</option>
                        @foreach ($grns as $grn)
                            <option value="{{ $grn->id }}">{{ $grn->grn_no }}{{ $grn->supplier ? ' – '.$grn->supplier->name : '' }}</option>
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
                <label><span class="label-cap">{{ __('messages.date') }}</span>
                    <input type="datetime-local" name="returned_at">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.reason') }}</span>
                    <input type="text" name="reason" maxlength="255">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2"></textarea>
                </label>
            </div>
            <p class="label-cap" style="margin:16px 0 8px">{{ __('messages.items') }} <span class="req">*</span></p>
            @include('app.purchasing._item-rows', ['products' => $products, 'prefix' => 'modal-add-return'])
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
