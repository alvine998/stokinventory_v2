@extends('layouts.guest', ['title' => __('messages.trial_expired_title')])

@section('content')
<div class="expired-shell">
    <div class="expired-card">
        <div class="expired-icon">
            <i class="fa-solid fa-clock"></i>
        </div>

        <h1>{{ __('messages.trial_expired_title') }}</h1>
        <p class="expired-body">{{ __('messages.trial_expired_body') }}</p>

        @if($daysUntilDeletion > 0)
        <div class="expired-countdown">
            <div class="countdown-number">{{ $daysUntilDeletion }}</div>
            <div class="countdown-label">{{ __('messages.days_until_deletion') }}</div>
        </div>
        <p class="expired-warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            {{ __('messages.trial_deletion_warning') }}
        </p>
        @else
        <div class="expired-countdown expired-countdown--danger">
            <div class="countdown-number">0</div>
            <div class="countdown-label">{{ __('messages.days_until_deletion') }}</div>
        </div>
        <p class="expired-warning expired-warning--urgent">
            <i class="fa-solid fa-triangle-exclamation"></i>
            {{ __('messages.trial_deletion_imminent') }}
        </p>
        @endif

        <div class="expired-actions">
            <a href="{{ route('billing.index') }}" class="primary-button large">
                <i class="fa-solid fa-credit-card"></i> {{ __('messages.pay_now') }}
            </a>
            <a href="{{ route('landing') }}" class="ghost-link">
                <i class="fa-solid fa-arrow-left"></i> {{ __('messages.back_to_landing') }}
            </a>
        </div>

        @if($packages->count())
        <div class="expired-packages">
            <h2>{{ __('messages.choose_package') }}</h2>
            <div class="expired-package-grid">
                @foreach($packages as $package)
                <a href="{{ route('order.show', $package) }}" class="expired-package-card {{ $package->is_featured ? 'featured' : '' }}">
                    <h3>{{ $package->name }}</h3>
                    @if($package->tagline)<p>{{ $package->tagline }}</p>@endif
                    <strong>Rp{{ number_format($package->discountedPrice(), 0, ',', '.') }}<small>/{{ __('messages.month') }}</small></strong>
                    @if($package->is_featured)
                        <span class="best-badge">{{ __('messages.best_value') }}</span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="expired-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="ghost-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> {{ __('messages.logout') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
