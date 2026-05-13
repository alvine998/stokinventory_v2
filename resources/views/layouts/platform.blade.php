<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('messages.platform_console') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
</head>
<body>
@include('partials.toasts')
@php
    $user = auth()->user();
    $nav = [
        ['super-admin.dashboard', 'super_admin_dashboard', 'fa-gauge-high'],
        ['super-admin.support-rooms', 'support_rooms', 'fa-headset'],
    ];

    if ($user?->isPlatformStaff(['super_admin', 'platform_admin'])) {
        $nav = [
            ['super-admin.dashboard', 'super_admin_dashboard', 'fa-gauge-high'],
            ['super-admin.customers', 'customer_management', 'fa-building-user'],
            ['super-admin.commerce', 'super_admin_commerce', 'fa-tags'],
            ['super-admin.testimonials', 'testimonials', 'fa-quote-left'],
            ['super-admin.bank-accounts', 'bank_accounts', 'fa-building-columns'],
            ['super-admin.billing-payments', 'billing_payments', 'fa-money-check-dollar'],
            ['super-admin.cms', 'cms_management', 'fa-pen-nib'],
            ['super-admin.notifications', 'notification_management', 'fa-bell'],
            ['super-admin.support-rooms', 'support_rooms', 'fa-headset'],
        ];
    }
@endphp

<div class="app-shell platform-shell">
    <aside class="sidebar platform-sidebar">
        <a class="brand" href="{{ route('super-admin.dashboard') }}">
            <span class="brand-mark"><i class="fa-solid fa-crown"></i></span>
            <span>{{ __('messages.platform_console') }}</span>
        </a>
        <nav class="nav-list">
            @foreach ($nav as [$route, $label, $icon])
                <a class="{{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">
                    <i class="fa-solid {{ $icon }}"></i>
                    <span>{{ __('messages.' . $label) }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <p class="eyebrow">{{ __('messages.platform_owner_area') }}</p>
                <h1>{{ $heading ?? __('messages.platform_console') }}</h1>
            </div>
            <div class="topbar-actions">
                <span class="platform-role-pill"><i class="fa-solid fa-id-card-clip"></i> {{ str_replace('_', ' ', $user->platform_role) }}</span>
                <a class="ghost-link" href="{{ route('locale.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}">
                    {{ strtoupper(app()->getLocale() === 'id' ? 'en' : 'id') }}
                </a>
                <a class="ghost-link" href="{{ route('dashboard') }}"><i class="fa-solid fa-arrow-right-arrow-left"></i> {{ __('messages.customer_area') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="icon-button" title="{{ __('messages.logout') }}"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
                </form>
            </div>
        </header>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
