@extends('layouts.app', ['title' => __('messages.billing'), 'heading' => __('messages.billing')])

@section('content')
<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.customer_billing') }}</p>
            <h2><i class="fa-solid fa-file-invoice-dollar"></i> {{ __('messages.invoices') }}</h2>
        </div>
        <button class="secondary-button" onclick="window.print()"><i class="fa-solid fa-print"></i> {{ __('messages.print_report') }}</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('messages.invoice_no') }}</th><th>{{ __('messages.package_name') }}</th><th>{{ __('messages.discount_code') }}</th><th>{{ __('messages.bank_accounts') }}</th><th>{{ __('messages.amount') }}</th><th>{{ __('messages.status') }}</th><th>{{ __('messages.due_at') }}</th></tr></thead>
            <tbody>
            @forelse ($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_no }}</td>
                    <td>{{ $invoice->subscriptionPackage->name ?? '-' }}</td>
                    <td>{{ $invoice->discount_code ?? '-' }}</td>
                    <td>{{ $invoice->bankAccount?->bank_name ?? '-' }} {{ $invoice->bankAccount ? '- ' . $invoice->bankAccount->account_number : '' }}</td>
                    <td>Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    <td><span class="status">{{ $invoice->status }}</span></td>
                    <td>{{ optional($invoice->due_at)->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-receipt"></i><p>{{ __('messages.no_data_yet') }}</p></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
