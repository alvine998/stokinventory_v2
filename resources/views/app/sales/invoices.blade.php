@extends('layouts.app', ['title' => __('messages.sales_invoice'), 'heading' => __('messages.nav_sales')])

@section('content')
@include('app.sales._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_sales') }}</p>
            <h2><i class="fa-solid fa-file-invoice-dollar"></i> {{ __('messages.sales_invoice') }}</h2>
        </div>
        <a href="#modal-add-inv" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_invoice') }}</a>
        <a href="{{ route('sales.invoices.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
    </div>

    @include('partials.errors')

    {{-- Summary bar --}}
    <div class="stat-row" style="margin-bottom:20px">
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.total_invoiced') }}</span>
            <span class="stat-value">Rp {{ number_format($summary['total'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.total_collected') }}</span>
            <span class="stat-value" style="color:var(--teal)">Rp {{ number_format($summary['paid'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.outstanding') }}</span>
            <span class="stat-value" style="color:var(--amber)">Rp {{ number_format($summary['total'] - $summary['paid'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.overdue_count') }}</span>
            <span class="stat-value" style="color:var(--rose)">{{ $summary['overdue'] }}</span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.invoice_no') }}</th>
                    <th>{{ __('messages.customer') }}</th>
                    <th>{{ __('messages.so_no') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.paid_amount') }}</th>
                    <th>{{ __('messages.outstanding') }}</th>
                    <th>{{ __('messages.due_date') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($invoices as $inv)
            @php
                $isOverdue = $inv->isOverdue();
                $badgeClass = match($inv->status) {
                    'paid'     => 'badge-active',
                    'partial'  => '',
                    'overdue'  => 'badge-inactive',
                    'cancelled'=> 'badge-inactive',
                    default    => $isOverdue ? 'badge-inactive' : '',
                };
            @endphp
            <tbody>
                <tr style="{{ $isOverdue && $inv->status !== 'paid' ? 'background:rgba(255,70,70,.05)' : '' }}">
                    <td><strong>{{ $inv->invoice_no }}</strong></td>
                    <td>{{ $inv->customer?->name ?? '—' }}</td>
                    <td>{{ $inv->salesOrder?->so_no ?? '—' }}</td>
                    <td>Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                    <td style="{{ $inv->outstanding() > 0 ? 'font-weight:600;color:var(--amber)' : 'color:var(--teal)' }}">
                        Rp {{ number_format($inv->outstanding(), 0, ',', '.') }}
                    </td>
                    <td>{{ $inv->due_at?->format('d M Y') ?? '—' }}</td>
                    <td><span class="badge-status {{ $badgeClass }}">{{ __('messages.inv_status_'.$inv->status) }}</span></td>
                    <td>
                        @if($inv->status !== 'paid' && $inv->status !== 'cancelled')
                        <a href="#modal-pay-inv-{{ $inv->id }}" class="icon-button" style="color:var(--teal)" title="{{ __('messages.update_payment') }}"><i class="fa-solid fa-circle-dollar-to-slot"></i></a>
                        @endif
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="9" class="empty-cell">{{ __('messages.no_invoices') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $invoices->links() }}
</section>

{{-- Pay modals --}}
@foreach ($invoices as $inv)
@if(!in_array($inv->status, ['paid','cancelled']))
<div class="modal-overlay" id="modal-pay-inv-{{ $inv->id }}">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <h3>{{ __('messages.update_payment') }} — {{ $inv->invoice_no }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.invoices.update', $inv) }}" class="modal-body">
            @csrf @method('PATCH')
            <label><span class="label-cap">{{ __('messages.paid_amount') }}</span>
                <input type="number" name="paid_amount" min="0" step="0.01" value="{{ $inv->paid_amount }}" required>
            </label>
            <label style="margin-top:12px"><span class="label-cap">{{ __('messages.notes') }}</span>
                <textarea name="notes" rows="2">{{ $inv->notes }}</textarea>
            </label>
            <p style="margin-top:8px;font-size:12px;color:#888">{{ __('messages.total_amount') }}: <strong>Rp {{ number_format($inv->amount, 0, ',', '.') }}</strong></p>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach

{{-- Add invoice modal --}}
<div class="modal-overlay" id="modal-add-inv">
    <div class="modal" style="max-width:500px">
        <div class="modal-head">
            <h3>{{ __('messages.new_invoice') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.invoices.store') }}" class="modal-body">
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
                <label><span class="label-cap">{{ __('messages.so_reference') }}</span>
                    <select name="so_id">
                        <option value="">—</option>
                        @foreach ($sos as $so)
                            <option value="{{ $so->id }}">{{ $so->so_no }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.amount') }} <span class="req">*</span></span>
                    <input type="number" name="amount" min="0.01" step="0.01" required>
                </label>
                <label><span class="label-cap">{{ __('messages.paid_amount') }}</span>
                    <input type="number" name="paid_amount" min="0" step="0.01" value="0">
                </label>
                <label><span class="label-cap">{{ __('messages.due_date') }}</span>
                    <input type="date" name="due_at">
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
@endsection
