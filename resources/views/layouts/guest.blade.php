<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'StokInventory' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
</head>
<body>
@include('partials.toasts')
<main class="guest-shell">
    <nav class="guest-nav" id="guest-nav">
        <a class="brand" href="{{ route('landing') }}">
            <span class="brand-mark"><i class="fa-solid fa-layer-group"></i></span>
            <span>StokInventory</span>
        </a>
        <div class="topbar-actions">
            <a class="ghost-link" href="{{ route('locale.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}">
                <i class="fa-solid fa-globe"></i> {{ strtoupper(app()->getLocale() === 'id' ? 'en' : 'id') }}
            </a>
            @auth
                <a class="primary-link" href="{{ route('dashboard') }}"><i class="fa-solid fa-gauge"></i> {{ __('messages.dashboard') }}</a>
            @else
                <a class="ghost-link" href="{{ route('login') }}">{{ __('messages.login') }}</a>
                <a class="primary-link" href="{{ route('register') }}"><i class="fa-solid fa-rocket"></i> {{ __('messages.free_trial') }}</a>
            @endauth
        </div>
    </nav>
    @yield('content')
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <a class="brand" href="{{ route('landing') }}">
                    <span class="brand-mark brand-mark-sm"><i class="fa-solid fa-layer-group"></i></span>
                    <span>StokInventory</span>
                </a>
                <p>{{ __('messages.hero_body') }}</p>
            </div>
            <div class="footer-links">
                <strong>{{ __('messages.product') }}</strong>
                <a href="#features">{{ __('messages.features') }}</a>
                <a href="#packages">{{ __('messages.landing_packages') }}</a>
                <a href="{{ route('register') }}">{{ __('messages.free_trial') }}</a>
            </div>
            <div class="footer-links">
                <strong>{{ __('messages.account') }}</strong>
                <a href="{{ route('login') }}">{{ __('messages.login') }}</a>
                <a href="{{ route('register') }}">{{ __('messages.register') }}</a>
            </div>
        </div>
        <div class="footer-bar">
            <span>&copy; {{ date('Y') }} StokInventory. All rights reserved.</span>
            <a href="{{ route('locale.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}">
                <i class="fa-solid fa-globe"></i> {{ strtoupper(app()->getLocale() === 'id' ? 'EN' : 'ID') }}
            </a>
        </div>
    </footer>
</main>
<script>
    // Sticky nav scroll effect
    const nav = document.getElementById('guest-nav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 40);
    });
</script>
@stack('scripts')
</body>
</html>
