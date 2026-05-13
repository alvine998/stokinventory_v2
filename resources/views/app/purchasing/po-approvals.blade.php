@extends('layouts.app', ['title' => __('messages.po_approval'), 'heading' => __('messages.nav_purchasing')])

@section('content')
@include('app.purchasing._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_purchasing') }}</p>
            <h2><i class="fa-solid fa-circle-check"></i> {{ __('messages.po_approval') }}</h2>
        </div>
    </div>

    @include('partials.errors')

    @if(session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    <h3 style="font-size:14px;font-weight:700;margin:0 0 12px;color:var(--ink)">{{ __('messages.pending_approval') }}</h3>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.po_no') }}</th>
                    <th>{{ __('messages.supplier') }}</th>
                    <th>{{ __('messages.items') }}</th>
                    <th>{{ __('messages.total_amount') }}</th>
                    <th>{{ __('messages.expected_at') }}</th>
                    <th>{{ __('messages.by') }}</th>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($pending as $po)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $po->po_no }}</strong></td>
                    <td>{{ $po->supplier?->name ?? '—' }}</td>
                    <td>{{ $po->items->count() }}</td>
                    <td>Rp {{ number_format($po->totalAmount(), 0, ',', '.') }}</td>
                    <td>{{ $po->expected_at?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $po->createdBy?->name ?? '—' }}</td>
                    <td>{{ $po->ordered_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.view_items') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-chevron-down':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <form method="POST" action="{{ route('purchasing.po-approvals.approve', $po) }}">
                                @csrf
                                <button class="icon-button" style="color:var(--teal)" title="{{ __('messages.approve') }}"><i class="fa-solid fa-check"></i></button>
                            </form>
                            <a href="#modal-reject-{{ $po->id }}" class="icon-button" style="color:var(--rose)" title="{{ __('messages.reject') }}"><i class="fa-solid fa-xmark"></i></a>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0">
                        <div style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            <table style="width:100%;font-size:13px">
                                <thead><tr style="color:#888"><th style="text-align:left;padding:4px 8px">{{ __('messages.product') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.qty') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.unit_price') }}</th><th style="text-align:right;padding:4px 8px">{{ __('messages.subtotal') }}</th></tr></thead>
                                <tbody>
                                @foreach ($po->items as $item)
                                    <tr>
                                        <td style="padding:4px 8px">{{ $item->product_name }}</td>
                                        <td style="padding:4px 8px;text-align:right">{{ number_format($item->quantity, 2) }}</td>
                                        <td style="padding:4px 8px;text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td style="padding:4px 8px;text-align:right;font-weight:600">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="8" class="empty-cell">{{ __('messages.no_pending_pos') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $pending->links() }}

    {{-- Reject modals --}}
    @foreach ($pending as $po)
    <div class="modal-overlay" id="modal-reject-{{ $po->id }}">
        <div class="modal" style="max-width:440px">
            <div class="modal-head">
                <h3>{{ __('messages.reject') }} {{ $po->po_no }}</h3>
                <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
            </div>
            <form method="POST" action="{{ route('purchasing.po-approvals.reject', $po) }}" class="modal-body">
                @csrf
                <label><span class="label-cap">{{ __('messages.reject_reason') }}</span>
                    <textarea name="reason" rows="3"></textarea>
                </label>
                <div class="modal-footer">
                    <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                    <button class="primary-button" style="background:var(--rose)">{{ __('messages.reject') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <hr style="margin:32px 0;border:none;border-top:1px solid #e3ecef">
    <h3 style="font-size:14px;font-weight:700;margin:0 0 12px;color:var(--ink)">{{ __('messages.recently_processed') }}</h3>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.po_no') }}</th>
                    <th>{{ __('messages.supplier') }}</th>
                    <th>{{ __('messages.total_amount') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.approved_by') }}</th>
                    <th>{{ __('messages.updated_at') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($all as $po)
                @php
                    $colors = ['approved'=>'badge-active','rejected'=>'badge-inactive','cancelled'=>'badge-inactive'];
                @endphp
                <tr>
                    <td><strong>{{ $po->po_no }}</strong></td>
                    <td>{{ $po->supplier?->name ?? '—' }}</td>
                    <td>Rp {{ number_format($po->totalAmount(), 0, ',', '.') }}</td>
                    <td><span class="badge-status {{ $colors[$po->status] ?? '' }}">{{ __('messages.po_status_'.$po->status) }}</span></td>
                    <td>{{ $po->approvedBy?->name ?? '—' }}</td>
                    <td>{{ $po->updated_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-cell">—</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
