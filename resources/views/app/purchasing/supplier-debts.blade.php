@extends('layouts.app', ['title' => __('messages.supplier_debt'), 'heading' => __('messages.nav_purchasing')])

@section('content')
@include('app.purchasing._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_purchasing') }}</p>
            <h2><i class="fa-solid fa-hand-holding-dollar"></i> {{ __('messages.supplier_debt') }}</h2>
        </div>
        <div class="head-actions">
            <a href="{{ route('purchasing.supplier-debts.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
            <a href="#modal-add-debt" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_debt') }}</a>
        </div>
    </div>

    @include('partials.errors')

    {{-- Summary bar --}}
    <div class="stat-row" style="margin-bottom:20px">
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.total_debt') }}</span>
            <span class="stat-value">Rp {{ number_format($summary['total'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">{{ __('messages.total_paid') }}</span>
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
                    <th>{{ __('messages.supplier') }}</th>
                    <th>{{ __('messages.po_no') }}</th>
                    <th>{{ __('messages.amount') }}</th>
                    <th>{{ __('messages.paid_amount') }}</th>
                    <th>{{ __('messages.outstanding') }}</th>
                    <th>{{ __('messages.due_date') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($debts as $debt)
            @php
                $isOverdue = $debt->isOverdue();
                $rowColor = match($debt->status) {
                    'paid'    => 'var(--teal)',
                    'partial' => 'var(--amber)',
                    default   => $isOverdue ? 'var(--rose)' : '',
                };
                $badgeClass = match($debt->status) {
                    'paid'    => 'badge-active',
                    'partial' => '',
                    default   => $isOverdue ? 'badge-inactive' : '',
                };
            @endphp
            <tbody class="row-group">
                <tr style="{{ $isOverdue && $debt->status !== 'paid' ? 'background:rgba(255,70,70,.05)' : '' }}">
                    <td><strong style="{{ $rowColor ? 'color:'.$rowColor : '' }}">{{ $debt->invoice_no ?? '—' }}</strong></td>
                    <td>{{ $debt->supplier?->name ?? '—' }}</td>
                    <td>{{ $debt->purchaseOrder?->po_no ?? '—' }}</td>
                    <td>Rp {{ number_format($debt->amount, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}</td>
                    <td style="{{ $debt->outstanding() > 0 ? 'font-weight:600;color:var(--amber)' : 'color:var(--teal)' }}">
                        Rp {{ number_format($debt->outstanding(), 0, ',', '.') }}
                    </td>
                    <td>{{ $debt->due_date?->format('d M Y') ?? '—' }}</td>
                    <td><span class="badge-status {{ $badgeClass }}">{{ __('messages.debt_status_'.$debt->status) }}</span></td>
                    <td>
                        @if($debt->status !== 'paid')
                        <a href="#modal-pay-{{ $debt->id }}" class="icon-button" style="color:var(--teal)" title="{{ __('messages.mark_paid') }}"><i class="fa-solid fa-circle-dollar-to-slot"></i></a>
                        @endif
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="9" class="empty-cell">{{ __('messages.no_debts') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $debts->links() }}
</section>

{{-- Pay modals --}}
@foreach ($debts as $debt)
@if($debt->status !== 'paid')
<div class="modal-overlay" id="modal-pay-{{ $debt->id }}">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <h3>{{ __('messages.update_payment') }} — {{ $debt->invoice_no ?? $debt->id }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('purchasing.supplier-debts.update', $debt) }}" class="modal-body">
            @csrf @method('PATCH')
            <label><span class="label-cap">{{ __('messages.paid_amount') }}</span>
                <input type="number" name="paid_amount" min="0" step="0.01" value="{{ $debt->paid_amount }}" placeholder="0" required>
            </label>
            <label style="margin-top:12px"><span class="label-cap">{{ __('messages.notes') }}</span>
                <textarea name="notes" rows="2">{{ $debt->notes }}</textarea>
            </label>
            <p style="margin-top:8px;font-size:12px;color:#888">{{ __('messages.total_debt') }}: <strong>Rp {{ number_format($debt->amount, 0, ',', '.') }}</strong></p>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach

{{-- Add debt modal --}}
<div class="modal-overlay" id="modal-add-debt">
    <div class="modal" style="max-width:500px">
        <div class="modal-head">
            <h3>{{ __('messages.new_debt') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('purchasing.supplier-debts.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.supplier') }} <span class="req">*</span></span>
                    <select name="supplier_id" required>
                        <option value="">—</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.po_reference') }}</span>
                    <select name="purchase_order_id">
                        <option value="">—</option>
                        @foreach ($pos as $po)
                            <option value="{{ $po->id }}">{{ $po->po_no }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.invoice_no') }}</span>
                    <input type="text" name="invoice_no" maxlength="100" placeholder="{{ __('messages.invoice_no') }}">
                </label>
                <label><span class="label-cap">{{ __('messages.amount') }} <span class="req">*</span></span>
                    <input type="number" name="amount" min="0.01" step="0.01" placeholder="0" required>
                </label>
                <label><span class="label-cap">{{ __('messages.paid_amount') }}</span>
                    <input type="number" name="paid_amount" min="0" step="0.01" value="0" placeholder="0">
                </label>
                <label><span class="label-cap">{{ __('messages.due_date') }}</span>
                    <input type="date" name="due_date">
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
