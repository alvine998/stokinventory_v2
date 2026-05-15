@extends('layouts.app', ['title' => __('messages.purchase_request'), 'heading' => __('messages.nav_purchasing')])

@section('content')
@include('app.purchasing._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_purchasing') }}</p>
            <h2><i class="fa-solid fa-file-pen"></i> {{ __('messages.purchase_request') }}</h2>
        </div>
        <div class="head-actions">
            <a href="{{ route('purchasing.pr.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
            <a href="#modal-add-pr" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_pr') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.pr_no') }}</th>
                    <th>{{ __('messages.supplier') }}</th>
                    <th>{{ __('messages.items') }}</th>
                    <th>{{ __('messages.total_amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.by') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($prs as $pr)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $pr->pr_no }}</strong></td>
                    <td>{{ $pr->supplier?->name ?? '—' }}</td>
                    <td>{{ $pr->items->count() }}</td>
                    <td>Rp {{ number_format($pr->totalAmount(), 0, ',', '.') }}</td>
                    <td>
                        @php
                            $prColors = ['draft'=>'','pending'=>'','approved'=>'badge-active','rejected'=>'badge-inactive','cancelled'=>'badge-inactive'];
                        @endphp
                        <span class="badge-status {{ $prColors[$pr->status] ?? '' }}">{{ __('messages.pr_status_'.$pr->status) }}</span>
                    </td>
                    <td>{{ $pr->requested_at->format('d M Y') }}</td>
                    <td>{{ $pr->requestedBy?->name ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.view_items') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-chevron-down':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            @if(in_array($pr->status, ['draft','pending']))
                            <form method="POST" action="{{ route('purchasing.pr.status', $pr) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button class="icon-button" style="color:var(--teal)" title="{{ __('messages.approve') }}"><i class="fa-solid fa-check"></i></button>
                            </form>
                            <form method="POST" action="{{ route('purchasing.pr.status', $pr) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.reject') }}"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('purchasing.pr.destroy', $pr) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0">
                        <div style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <table style="width:100%;font-size:13px">
                                <thead><tr style="color:#888"><th style="text-align:left;padding:4px 8px">{{ __('messages.product') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.qty') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.unit_price') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.subtotal') }}</th><th style="text-align:left;padding:4px 8px">{{ __('messages.notes') }}</th></tr></thead>
                                <tbody>
                                @foreach ($pr->items as $item)
                                    <tr>
                                        <td style="padding:4px 8px">{{ $item->product_name }}</td>
                                        <td style="padding:4px 8px;text-align:right">{{ number_format($item->quantity, 2) }}</td>
                                        <td style="padding:4px 8px;text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td style="padding:4px 8px;text-align:right;font-weight:600">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
                                        <td style="padding:4px 8px;color:#888">{{ $item->notes ?: '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @if($pr->notes)<p style="margin:12px 8px 0;font-size:13px;color:#555"><strong>{{ __('messages.notes') }}:</strong> {{ $pr->notes }}</p>@endif
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="8" class="empty-cell">{{ __('messages.no_prs') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $prs->links() }}
</section>

{{-- Add Modal --}}
<div class="modal-overlay" id="modal-add-pr">
    <div class="modal" style="max-width:700px">
        <div class="modal-head">
            <h3>{{ __('messages.new_pr') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('purchasing.pr.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
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
                    <input type="datetime-local" name="requested_at">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2"></textarea>
                </label>
            </div>
            <p class="label-cap" style="margin:16px 0 8px">{{ __('messages.items') }} <span class="req">*</span></p>
            @include('app.purchasing._item-rows', ['products' => $products, 'prefix' => 'modal-add-pr'])
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
