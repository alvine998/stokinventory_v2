@extends('layouts.guest', ['title' => __('messages.order_package')])

@section('content')
<div class="order-progress">
    <div class="order-progress-inner">
        <div class="progress-step active"><span>1</span> {{ __('messages.your_info') }}</div>
        <div class="progress-connector"></div>
        <div class="progress-step active"><span>2</span> {{ __('messages.payment_method') }}</div>
        <div class="progress-connector"></div>
        <div class="progress-step"><span>3</span> {{ __('messages.confirmation') }}</div>
    </div>
</div>

<section class="order-shell">
    <div class="order-summary">
        <div class="order-summary-top">
            <p class="eyebrow" style="color:rgba(255,255,255,.65)">{{ __('messages.order_package') }}</p>
            <h1>{{ $package->name }}</h1>
            @if($package->tagline)<p class="order-tagline">{{ $package->tagline }}</p>@endif
        </div>

        <div class="order-price-block">
            <div class="price-row">
                @if ($package->discount_percent > 0)
                    <span id="summary-badge" class="discount-badge">-{{ $package->discount_percent }}%</span>
                    <del id="summary-original">Rp{{ number_format($package->price, 0, ',', '.') }}</del>
                @else
                    <span id="summary-badge" class="discount-badge" style="display:none"></span>
                    <del id="summary-original" style="display:none">Rp{{ number_format($package->price, 0, ',', '.') }}</del>
                @endif
                <strong id="summary-monthly">Rp{{ number_format($package->discountedPrice(), 0, ',', '.') }}</strong>
                <small>/ {{ __('messages.month') }}</small>
            </div>
            <div id="summary-total-row" class="price-total-row" style="display:none">
                <span>{{ __('messages.total_commitment') }}</span>
                <strong id="summary-total"></strong>
            </div>
        </div>

        <div class="order-trial-badge">
            <i class="fa-solid fa-gift"></i>
            {{ $package->trial_days }} {{ __('messages.trial_days') }} — {{ __('messages.no_charge_trial') }}
        </div>

        @if(!empty($package->features))
        <ul class="order-features">
            @foreach ($package->features as $feature)
                <li><i class="fa-solid fa-check-circle"></i> {{ $feature }}</li>
            @endforeach
        </ul>
        @endif

        <div class="order-trust">
            <div><i class="fa-solid fa-lock"></i> {{ __('messages.secure_transaction') }}</div>
            <div><i class="fa-solid fa-rotate-left"></i> {{ __('messages.cancel_anytime') }}</div>
        </div>
    </div>

    <div class="order-form-wrap">
        @include('partials.errors')
        <form method="POST" action="{{ route('order.store', $package) }}" class="form-grid" enctype="multipart/form-data">
            @csrf

            @if ($package->billing_periods && count($package->billing_periods))
            <div class="order-form-section">
                <h3 class="order-form-heading"><span>1</span> {{ __('messages.choose_billing_period') }}</h3>
                <div class="period-selector">
                    {{-- 1-month option is always first at full price --}}
                    <div class="period-option">
                        <input
                            type="radio"
                            name="billing_months"
                            id="period_1"
                            value="1"
                            data-base="{{ $package->price }}"
                            data-discount="0"
                        >
                        <label for="period_1">
                            <strong>1 {{ __('messages.month') }}</strong>
                            <span class="period-discount period-discount--none">{{ __('messages.full_price') }}</span>
                            <small>Rp{{ number_format($package->price, 0, ',', '.') }}/{{ __('messages.month') }}</small>
                        </label>
                    </div>
                    @foreach (collect($package->billing_periods)->sortBy('months') as $period)
                    <div class="period-option">
                        <input
                            type="radio"
                            name="billing_months"
                            id="period_{{ $period['months'] }}"
                            value="{{ $period['months'] }}"
                            data-base="{{ $package->price }}"
                            data-discount="{{ $period['discount_percent'] }}"
                        >
                        <label for="period_{{ $period['months'] }}">
                            <strong>{{ $period['months'] }} {{ __('messages.months') }}</strong>
                            <span class="period-discount">-{{ $period['discount_percent'] }}%</span>
                            <small>Rp{{ number_format($package->price * (100 - $period['discount_percent']) / 100, 0, ',', '.') }}/{{ __('messages.month') }}</small>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @guest
                <div class="order-form-section">
                    <h3 class="order-form-heading"><span>{{ $package->billing_periods ? 2 : 1 }}</span> {{ __('messages.your_info') }}</h3>
                    <div class="form-grid two">
                        <label>{{ __('messages.name') }}<input name="name" value="{{ old('name') }}" placeholder="{{ __('messages.name') }}" required autocomplete="name"></label>
                        <label>{{ __('messages.company_name') }}<input name="company_name" value="{{ old('company_name') }}" placeholder="{{ __('messages.company_name') }}" required></label>
                        <label>{{ __('messages.email') }}<input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required autocomplete="email"></label>
                        <label>{{ __('messages.password') }}<input name="password" type="password" placeholder="{{ __('messages.password') }}" required autocomplete="new-password"></label>
                        <label class="span-2">{{ __('messages.confirm_password') }}<input name="password_confirmation" type="password" placeholder="{{ __('messages.confirm_password') }}" required autocomplete="new-password"></label>
                    </div>
                </div>
            @endguest

            <div class="order-form-section">
                <h3 class="order-form-heading"><span>{{ auth()->check() ? ($package->billing_periods ? 2 : 1) : ($package->billing_periods ? 3 : 2) }}</span> {{ __('messages.choose_payment_method') }}</h3>
                @if($bankAccounts->isEmpty())
                    <div class="empty-state"><i class="fa-solid fa-building-columns"></i><p>{{ __('messages.no_bank_accounts') }}</p></div>
                @else
                    <div class="bank-choice-grid">
                        @foreach ($bankAccounts as $bankAccount)
                            <label class="bank-choice">
                                <input type="radio" name="bank_account_id" value="{{ $bankAccount->id }}" required>
                                <span class="bank-choice-body">
                                    <span class="bank-choice-icon"><i class="fa-solid fa-building-columns"></i></span>
                                    <span>
                                        <strong>{{ $bankAccount->bank_name }}</strong>
                                        <small>{{ $bankAccount->account_number }}</small>
                                        <small>{{ $bankAccount->account_name }}{{ $bankAccount->branch ? ' — ' . $bankAccount->branch : '' }}</small>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="order-form-section">
                <h3 class="order-form-heading"><span>{{ auth()->check() ? ($package->billing_periods ? 3 : 2) : ($package->billing_periods ? 4 : 3) }}</span> {{ __('messages.extra_info') }}</h3>
                <div class="form-grid">
                    <label>{{ __('messages.discount_code') }}
                        <div class="input-with-icon">
                            <i class="fa-solid fa-tag input-icon"></i>
                            <input name="discount_code" value="{{ old('discount_code') }}" placeholder="{{ __('messages.discount_code_placeholder') }}">
                        </div>
                    </label>
                    <label>{{ __('messages.payment_notes') }}<textarea name="payment_notes" placeholder="{{ __('messages.payment_notes_placeholder') }}" rows="3">{{ old('payment_notes') }}</textarea></label>
                </div>
            </div>

            <div class="order-form-section">
                <h3 class="order-form-heading">
                    <span><i class="fa-solid fa-image" style="font-size:12px"></i></span>
                    {{ __('messages.evidence_image') }}
                </h3>
                <label id="evidence-label" style="cursor:pointer;display:block;border:2px dashed #c8d8e0;border-radius:10px;padding:20px;text-align:center;transition:.2s;background:#f8fafb">
                    <input type="file" name="payment_evidence" id="evidence-input" accept="image/*" style="display:none">
                    <div id="evidence-placeholder">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px;color:#8fa4ae;margin-bottom:6px"></i>
                        <p style="margin:0;font-size:13px;color:#8fa4ae">{{ __('messages.evidence_image') }} — JPG, PNG, WEBP (max 2 MB)</p>
                        <p style="margin:4px 0 0;font-size:12px;color:#b0c4ce">Click to upload</p>
                    </div>
                    <img id="evidence-preview" src="" alt="" style="display:none;max-width:100%;max-height:220px;border-radius:8px;object-fit:contain">
                </label>
                @error('payment_evidence')<p class="field-error" style="margin-top:6px">{{ $message }}</p>@enderror
            </div>

            <div class="order-submit-row">
                <div class="order-submit-trust">
                    <i class="fa-solid fa-lock"></i> {{ __('messages.secure_transaction') }}
                </div>
                <button class="primary-button large">
                    <i class="fa-solid fa-money-check-dollar"></i> {{ __('messages.pay_now') }}
                </button>
            </div>
        </form>
    </div>
</section>

@if ($package->billing_periods && count($package->billing_periods))
@push('scripts')
<script>
(function () {
    var fmt = function (n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); };
    var base = {{ (int) $package->price }};
    var monthLabel = @json(__('messages.months'));

    function update(radio) {
        var discount = parseFloat(radio.dataset.discount);
        var months = parseInt(radio.value);
        var monthly = base * (1 - discount / 100);
        var total = monthly * months;

        var badge = document.getElementById('summary-badge');
        var original = document.getElementById('summary-original');
        var monthlyEl = document.getElementById('summary-monthly');
        var totalRow = document.getElementById('summary-total-row');
        var totalEl = document.getElementById('summary-total');

        if (discount > 0) {
            badge.textContent = '-' + discount + '%';
            badge.style.display = '';
            original.textContent = fmt(base);
            original.style.display = '';
        } else {
            badge.style.display = 'none';
            original.style.display = 'none';
        }
        monthlyEl.textContent = fmt(monthly);

        if (months > 1) {
            totalRow.style.display = '';
            totalEl.textContent = fmt(total) + ' / ' + months + ' ' + monthLabel;
        } else {
            totalRow.style.display = 'none';
        }
    }

    var radios = document.querySelectorAll('input[name="billing_months"]');
    radios.forEach(function (r) {
        r.addEventListener('change', function () { update(this); });
    });

    // Default to 1-month (full price)
    var period1 = document.getElementById('period_1');
    if (period1) { period1.checked = true; update(period1); }
})();
</script>
@endpush
@endif

@push('scripts')
<script>
(function () {
    var input    = document.getElementById('evidence-input');
    var preview  = document.getElementById('evidence-preview');
    var holder   = document.getElementById('evidence-placeholder');
    var label    = document.getElementById('evidence-label');
    if (!input) return;
    input.addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            holder.style.display  = 'none';
            label.style.borderColor = 'var(--teal)';
            label.style.background  = '#f0fdfa';
        };
        reader.readAsDataURL(file);
    });
})();
</script>
@endpush

@endsection
