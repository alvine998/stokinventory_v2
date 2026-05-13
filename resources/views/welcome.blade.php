@extends('layouts.guest', ['title' => 'StokInventory — Inventory for growing businesses'])

@section('content')
@if ($promoBanners->isNotEmpty())
<section class="promo-carousel" aria-label="{{ __('messages.promo_carousel') }}">
    <div class="carousel-track" id="carouselTrack">
        @foreach ($promoBanners as $i => $banner)
        <div class="carousel-slide" style="--promo-bg: {{ $banner->background }}">
            @if ($banner->image)
                <img src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}" class="carousel-bg-image">
            @endif
            <div class="carousel-overlay"></div>
            <div class="carousel-content">
                <div class="carousel-text">
                    @if ($banner->badge)<span class="promo-badge">{{ $banner->badge }}</span>@endif
                    <h2>{{ $banner->title }}</h2>
                    @if ($banner->subtitle)<p>{{ $banner->subtitle }}</p>@endif
                    @if ($banner->button_label)
                    <a href="{{ $banner->button_url ?: route('register') }}" class="promo-cta">{{ $banner->button_label }} <i class="fa-solid fa-arrow-right"></i></a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if ($promoBanners->count() > 1)
    <button class="carousel-btn carousel-prev" id="carouselPrev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
    <button class="carousel-btn carousel-next" id="carouselNext" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
    <div class="carousel-dots" id="carouselDots">
        @foreach ($promoBanners as $i => $banner)
        <button class="carousel-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
    </div>
    @endif
</section>
@endif

<section class="hero">
    <div class="hero-copy">
        <div class="hero-eyebrow">
            <span class="trust-pill"><i class="fa-solid fa-shield-halved"></i> {{ __('messages.free_trial_30') }}</span>
            <span class="trust-pill trust-pill-blue"><i class="fa-solid fa-bolt"></i> {{ __('messages.setup_workspace') }}</span>
        </div>
        <h1>{{ __('messages.hero_title') }}</h1>
        <p>{{ __('messages.hero_body') }}</p>
        <div class="hero-actions">
            @auth
                <a class="primary-link large" href="{{ route('dashboard') }}"><i class="fa-solid fa-gauge"></i> {{ __('messages.go_to_dashboard') }}</a>
            @else
                <a class="primary-link large" href="{{ route('register') }}"><i class="fa-solid fa-rocket"></i> {{ __('messages.start_trial') }}</a>
            @endauth
            <a class="ghost-link large" href="#packages"><i class="fa-solid fa-tag"></i> {{ __('messages.view_pricing') }}</a>
        </div>
        <div class="social-proof">
            <div class="proof-item"><i class="fa-solid fa-circle-check"></i> {{ __('messages.no_credit_card') }}</div>
            <div class="proof-item"><i class="fa-solid fa-circle-check"></i> {{ __('messages.cancel_anytime') }}</div>
            <div class="proof-item"><i class="fa-solid fa-circle-check"></i> {{ __('messages.free_30_days') }}</div>
        </div>
    </div>
    <div class="hero-panel">
        <div class="mini-toolbar">
            <span></span><span></span><span></span>
            <small class="toolbar-title">StokInventory Dashboard</small>
        </div>
        <div class="metric-grid">
            <div><strong>12,480</strong><small>{{ __('messages.units_tracked') }}</small></div>
            <div><strong>98%</strong><small>{{ __('messages.stock_accuracy') }}</small></div>
            <div><strong>31</strong><small>{{ __('messages.active_locations') }}</small></div>
        </div>
        <div class="hero-activity">
            <div class="hero-activity-item in"><i class="fa-solid fa-arrow-down"></i><span>Barang masuk</span><strong>+240</strong></div>
            <div class="hero-activity-item out"><i class="fa-solid fa-arrow-up"></i><span>Barang keluar</span><strong>-88</strong></div>
            <div class="hero-activity-item transfer"><i class="fa-solid fa-right-left"></i><span>Transfer</span><strong>12</strong></div>
        </div>
        <div class="flow-line"><i class="fa-solid fa-box"></i><span></span><i class="fa-solid fa-warehouse"></i><span></span><i class="fa-solid fa-receipt"></i></div>
    </div>
</section>

<section class="trust-band">
    <div class="trust-band-inner">
        <div class="trust-header">
            <span>{{ __('messages.trusted_by') }}</span>
            <p class="trust-subtitle">{{ __('messages.trusted_description') }}</p>
        </div>
        <div class="trust-stats">
            <div><strong>500+</strong><small>{{ __('messages.businesses') }}</small></div>
            <div><strong>2M+</strong><small>{{ __('messages.units_tracked') }}</small></div>
            <div><strong>98%</strong><small>{{ __('messages.stock_accuracy') }}</small></div>
            <div><strong>24/7</strong><small>{{ __('messages.support') }}</small></div>
        </div>
    </div>
</section>

<section id="features" class="feature-band">
    @php
    $features = [
    ['stores', 'fa-store', 'teal'],
    ['warehouses', 'fa-warehouse', 'blue'],
    ['products', 'fa-boxes-stacked', 'violet'],
    ['stock_movements', 'fa-right-left', 'amber'],
    ['stock_opname', 'fa-clipboard-check', 'cyan'],
    ['reports', 'fa-file-lines', 'rose'],
    ];
    @endphp
    @foreach ($features as [$key, $icon, $color])
    <article class="feature-card feature-card--{{ $color }}">
        <div class="feature-icon-wrap"><i class="fa-solid {{ $icon }}"></i></div>
        <h3>{{ __('messages.' . $key) }}</h3>
        <p>{{ __('messages.feature_' . $key) }}</p>
    </article>
    @endforeach
</section>

<section id="about" class="about-section">
    <div class="about-copy">
        <p class="eyebrow">{{ __('messages.about_us') }}</p>
        <h2>{{ $aboutUs->title ?? __('messages.about_us_title') }}</h2>
        <p>{{ $aboutUs->body ?? __('messages.about_us_body') }}</p>
    </div>
    <div class="about-highlights">
        <div class="about-highlight-card">
            <span class="about-icon"><i class="fa-solid fa-shield-halved"></i></span>
            <div>
                <strong>RBAC</strong>
                <span>{{ __('messages.about_rbac') }}</span>
            </div>
        </div>
        <div class="about-highlight-card">
            <span class="about-icon about-icon--amber"><i class="fa-solid fa-receipt"></i></span>
            <div>
                <strong>{{ __('messages.reports') }}</strong>
                <span>{{ __('messages.about_reports') }}</span>
            </div>
        </div>
        <div class="about-highlight-card">
            <span class="about-icon about-icon--cyan"><i class="fa-solid fa-warehouse"></i></span>
            <div>
                <strong>{{ __('messages.warehouses') }}</strong>
                <span>{{ __('messages.about_inventory') }}</span>
            </div>
        </div>
    </div>
</section>

@if ($testimonials->isNotEmpty())
<section class="testimonials-section">
    <div class="section-kicker" style="padding: 0 clamp(18px,5vw,72px); margin-bottom: 36px;">
        <p class="eyebrow">{{ __('messages.testimonials') }}</p>
        <h2>{{ __('messages.testimonials_title') }}</h2>
        <p>{{ __('messages.testimonials_subtitle') }}</p>
    </div>
    <div class="testimonials-grid">
        @foreach ($testimonials as $testimonial)
        <article class="testimonial-card">
            <div class="star-rating">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="fa-{{ $i <= $testimonial->rating ? 'solid' : 'regular' }} fa-star"></i>
                @endfor
            </div>
            <p class="testimonial-body">"{{ $testimonial->body }}"</p>
            <div class="testimonial-author">
                @if ($testimonial->avatar)
                    <img src="{{ Storage::url($testimonial->avatar) }}" alt="{{ $testimonial->name }}" class="testimonial-avatar">
                @else
                    <span class="testimonial-avatar-placeholder"><i class="fa-solid fa-user"></i></span>
                @endif
                <div>
                    <strong>{{ $testimonial->name }}</strong>
                    @if ($testimonial->role || $testimonial->company)
                        <small>{{ collect([$testimonial->role, $testimonial->company])->filter()->implode(' · ') }}</small>
                    @endif
                </div>
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif

<section id="packages" class="pricing-section">
    <div class="section-kicker">
        <p class="eyebrow">{{ __('messages.landing_packages') }}</p>
        <h2>{{ __('messages.choose_package') }}</h2>
        <p>{{ __('messages.package_intro') }}</p>
    </div>
    <div class="pricing-grid">
        @forelse ($packages as $package)
        <article class="pricing-card {{ $package->is_featured ? 'featured' : '' }}">
            @if ($package->is_featured)
            <span class="best-badge"><i class="fa-solid fa-star"></i> {{ __('messages.best_value') }}</span>
            @endif
            <div>
                <h3>{{ $package->name }}</h3>
                <p>{{ $package->tagline }}</p>
            </div>
            <div class="price-row">
                @php
                    $bestPeriod = collect($package->billing_periods ?? [])->sortByDesc('discount_percent')->first();
                    $bestDiscount = $bestPeriod ? (int) $bestPeriod['discount_percent'] : (int) $package->discount_percent;
                    $displayPrice = $bestDiscount > 0
                        ? $package->price * (100 - $bestDiscount) / 100
                        : $package->discountedPrice();
                @endphp
                @if ($bestDiscount > 0)
                <span class="discount-badge">-{{ $bestDiscount }}%</span>
                <del>Rp{{ number_format($package->price, 0, ',', '.') }}</del>
                @endif
                <strong>Rp{{ number_format($displayPrice, 0, ',', '.') }}</strong>
                <small>/ {{ __('messages.month') }}</small>
            </div>
            @if (!empty($package->billing_periods))
            <div class="period-badges" style="margin-top:6px">
                @foreach (collect($package->billing_periods)->sortBy('months') as $p)
                    <span class="period-badge">{{ $p['months'] }}{{ __('messages.mo') }} · -{{ $p['discount_percent'] }}%</span>
                @endforeach
            </div>
            @endif
            <ul>
                <li><i class="fa-solid fa-gift"></i> {{ $package->trial_days }} {{ __('messages.trial_days') }}</li>
                @foreach ($package->features ?? [] as $feature)
                <li><i class="fa-solid fa-check"></i> {{ $feature }}</li>
                @endforeach
            </ul>
            <a class="primary-link {{ $package->is_featured ? '' : 'outline-link' }}" href="{{ route('order.show', $package) }}">
                {{ __('messages.order_now') }} <i class="fa-solid fa-arrow-right"></i>
            </a>
        </article>
        @empty
        <article class="feature-card" style="grid-column: 1 / -1; text-align: center;">
            <i class="fa-solid fa-tags"></i>
            <h3>{{ __('messages.no_packages') }}</h3>
            <p>{{ __('messages.no_packages_hint') }}</p>
        </article>
        @endforelse
    </div>
</section>

<section class="cta-section">
    <div class="cta-inner">
        <p class="eyebrow">{{ __('messages.free_trial_30') }}</p>
        <h2>{{ __('messages.cta_title') }}</h2>
        <p>{{ __('messages.cta_body') }}</p>
        <div class="hero-actions">
            @auth
                <a class="primary-link large" href="{{ route('dashboard') }}"><i class="fa-solid fa-gauge"></i> {{ __('messages.go_to_dashboard') }}</a>
            @else
                <a class="primary-link large" href="{{ route('register') }}"><i class="fa-solid fa-rocket"></i> {{ __('messages.start_trial') }}</a>
                <a class="cta-ghost" href="{{ route('login') }}">{{ __('messages.login') }} <i class="fa-solid fa-arrow-right"></i></a>
            @endauth
        </div>
    </div>
</section>

{{-- ── Sticky WhatsApp Chat Widget ─────────────────────────────────────── --}}
@if (!empty($waNumber))
@php $waLink = 'https://wa.me/' . preg_replace('/\D/', '', $waNumber) . '?text=' . rawurlencode($waMessage); @endphp
<div id="wa-widget" style="position:fixed;bottom:28px;right:28px;z-index:9999;display:flex;flex-direction:column;align-items:flex-end;gap:10px">
    <div id="wa-panel" style="display:none;width:300px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.16);overflow:hidden">
        <div style="background:#25d366;padding:14px 16px;display:flex;align-items:center;gap:10px">
            <div style="width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fa-brands fa-whatsapp" style="color:#fff;font-size:20px"></i>
            </div>
            <div style="flex:1">
                <p style="color:#fff;font-weight:700;margin:0;font-size:14px">StokInventory Support</p>
                <p style="color:rgba(255,255,255,.8);margin:0;font-size:11px">
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#8de48d;margin-right:4px;vertical-align:middle"></span>Online
                </p>
            </div>
            <button onclick="toggleWa()" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,.8);font-size:20px;line-height:1;padding:2px" aria-label="Close">&times;</button>
        </div>
        <div style="padding:16px;background:#e5ddd5">
            <div style="background:#fff;border-radius:10px 10px 10px 2px;padding:12px 14px;font-size:13px;line-height:1.55;box-shadow:0 1px 3px rgba(0,0,0,.1)">
                <p style="margin:0 0 5px;font-weight:700;font-size:11px;color:#25d366">Support Team</p>
                <p style="margin:0;color:#1a2e3b">Halo! 👋<br>Ada yang bisa kami bantu? Chat langsung di WhatsApp ya.</p>
            </div>
            <p style="margin:5px 0 0;font-size:10px;color:#888;text-align:right">Balas dalam hitungan menit</p>
        </div>
        <div style="padding:12px 16px;background:#fff;border-top:1px solid #eef2f4">
            <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
               style="display:flex;align-items:center;justify-content:center;gap:8px;background:#25d366;color:#fff;padding:11px 16px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none">
                <i class="fa-brands fa-whatsapp" style="font-size:16px"></i>
                Mulai Chat WhatsApp
            </a>
        </div>
    </div>
    <button id="wa-fab" onclick="toggleWa()" aria-label="Chat WhatsApp"
            style="width:58px;height:58px;border-radius:50%;background:#25d366;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(37,211,102,.5);position:relative;transition:transform .2s"
            onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        <i class="fa-brands fa-whatsapp" style="color:#fff;font-size:28px"></i>
        <span style="position:absolute;inset:-5px;border-radius:50%;border:2px solid rgba(37,211,102,.45);animation:waPulse 2s ease-out infinite;pointer-events:none"></span>
    </button>
</div>
<style>
@keyframes waPulse{0%{transform:scale(1);opacity:.8}70%{transform:scale(1.4);opacity:0}100%{transform:scale(1.4);opacity:0}}
</style>
@endif

@endsection

@if ($promoBanners->count() > 1)
@push('scripts')
<script>
// WhatsApp widget toggle
function toggleWa() {
    var p = document.getElementById('wa-panel');
    if (p) p.style.display = p.style.display === 'none' ? 'block' : 'none';
}
// Auto-open after 8 s on first visit this session
if (!sessionStorage.getItem('wa_seen')) {
    setTimeout(function () {
        var p = document.getElementById('wa-panel');
        if (p && p.style.display === 'none') { p.style.display = 'block'; }
        sessionStorage.setItem('wa_seen', '1');
    }, 8000);
}
</script>
<script>
(function () {
    var track = document.getElementById('carouselTrack');
    var dots = document.querySelectorAll('.carousel-dot');
    var total = {{ $promoBanners->count() }};
    var current = 0;
    var timer;

    function goTo(n) {
        current = (n + total) % total;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';
        dots.forEach(function (d, i) { d.classList.toggle('active', i === current); });
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startTimer() { timer = setInterval(next, 5000); }
    function resetTimer() { clearInterval(timer); startTimer(); }

    document.getElementById('carouselNext')?.addEventListener('click', function () { next(); resetTimer(); });
    document.getElementById('carouselPrev')?.addEventListener('click', function () { prev(); resetTimer(); });
    dots.forEach(function (d) {
        d.addEventListener('click', function () { goTo(parseInt(this.dataset.index)); resetTimer(); });
    });

    // Pause on hover
    var carousel = track.closest('.promo-carousel');
    carousel.addEventListener('mouseenter', function () { clearInterval(timer); });
    carousel.addEventListener('mouseleave', startTimer);

    // Touch/swipe
    var startX = 0;
    carousel.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    carousel.addEventListener('touchend', function (e) {
        var diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) { diff > 0 ? next() : prev(); resetTimer(); }
    });

    startTimer();
})();
</script>
@endpush
@endif