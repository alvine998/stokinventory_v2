@extends('layouts.platform', ['title' => __('messages.billing_payments'), 'heading' => __('messages.billing_payments')])
@php use Illuminate\Support\Facades\Storage; @endphp

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.platform_owner_area') }}</p>
            <h2><i class="fa-solid fa-money-check-dollar"></i> {{ __('messages.customer_payment_status') }}</h2>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('messages.invoice_no') }}</th><th>{{ __('messages.customer') }}</th><th>{{ __('messages.package_name') }}</th><th>{{ __('messages.discount_code') }}</th><th>{{ __('messages.bank_accounts') }}</th><th>{{ __('messages.amount') }}</th><th>{{ __('messages.evidence_image') }}</th><th>{{ __('messages.status') }}</th><th>{{ __('messages.actions') }}</th></tr></thead>
            <tbody>
            @foreach ($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_no }}</td>
                    <td>{{ $invoice->business->name ?? $invoice->customer_name }}</td>
                    <td>{{ $invoice->subscriptionPackage->name ?? '-' }}</td>
                    <td>{{ $invoice->discount_code ?? '-' }}</td>
                    <td>{{ $invoice->bankAccount?->bank_name ?? '-' }}</td>
                    <td>Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    <td>
                        @if ($invoice->payment_evidence)
                            <a href="{{ Storage::url($invoice->payment_evidence) }}" target="_blank" style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--teal)">
                                <img src="{{ Storage::url($invoice->payment_evidence) }}" alt="" style="width:44px;height:36px;object-fit:cover;border-radius:5px;border:1px solid #d1e0e7">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        @else
                            <span style="color:#b0c4ce;font-size:12px">—</span>
                        @endif
                    </td>
                    <td><span class="status">{{ $invoice->status }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('super-admin.billing-payments.update', $invoice) }}" class="inline-form">
                            @csrf
                            @method('PATCH')
                            <select name="status" required>
                                @foreach (['pending', 'unpaid', 'paid'] as $status)
                                    <option value="{{ $status }}" @selected($invoice->status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <input name="payment_notes" placeholder="{{ __('messages.payment_notes') }}" value="{{ $invoice->payment_notes }}">
                            <button class="secondary-button">{{ __('messages.update') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
