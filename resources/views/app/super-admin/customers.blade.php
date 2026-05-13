@extends('layouts.platform', ['title' => __('messages.customer_management'), 'heading' => __('messages.customer_management')])

@section('content')
<section class="panel">
    <div class="section-head"><h2><i class="fa-solid fa-building-user"></i> {{ __('messages.customers') }}</h2></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>{{ __('messages.company_name') }}</th><th>{{ __('messages.industry') }}</th><th>{{ __('messages.trial_days') }}</th><th>{{ __('messages.status') }}</th></tr></thead>
            <tbody>
            @foreach ($customers as $customer)
                <tr><td>{{ $customer->name }}</td><td>{{ $customer->industry ?? '-' }}</td><td>{{ $customer->trialDaysLeft() }}</td><td><span class="status">{{ $customer->onboarding_completed_at ? 'onboarded' : 'trial' }}</span></td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
