@extends('layouts.app', ['title' => __('messages.accounting_integration'), 'heading' => __('messages.nav_finance')])

@section('content')
@include('app.finance._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_finance') }}</p>
            <h2><i class="fa-solid fa-plug"></i> {{ __('messages.accounting_integration') }}</h2>
        </div>
    </div>

    @include('partials.errors')

    <div style="max-width:580px">
        @if($integration?->is_active)
        <div class="notice-box notice-success" style="margin-bottom:20px">
            <i class="fa-solid fa-circle-check"></i>
            {{ __('messages.integration_active', ['provider' => ucfirst($integration->provider)]) }}
            @if($integration->last_sync_at)
                — {{ __('messages.last_sync') }}: {{ $integration->last_sync_at->format('d M Y H:i') }}
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('finance.integration.save') }}">
            @csrf
            <div class="form-grid two">
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.provider') }} <span class="req">*</span></span>
                    <select name="provider" required>
                        @foreach (\App\Models\AccountingIntegration::providers() as $p)
                            <option value="{{ $p }}" {{ $integration?->provider === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.api_key') }}</span>
                    <input type="password" name="api_key" autocomplete="new-password"
                        placeholder="{{ $integration?->masked_api_key ?: __('messages.api_key_placeholder') }}">
                    <span style="font-size:11px;color:#aaa">{{ __('messages.api_key_hint') }}</span>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.endpoint_url') }}</span>
                    <input type="url" name="endpoint" value="{{ $integration?->endpoint }}" placeholder="https://cloud.accurate.id/accurate/api">
                </label>
                <label style="grid-column:span 2;display:flex;align-items:center;gap:10px;margin-top:4px">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $integration?->is_active ? 'checked' : '' }} style="width:16px;height:16px">
                    <span class="label-cap" style="margin-bottom:0;font-size:13px">{{ __('messages.enable_integration') }}</span>
                </label>
            </div>
            <div style="margin-top:16px">
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>

        <div style="margin-top:32px;padding:16px;background:#f6fafc;border-radius:10px;font-size:13px">
            <p style="font-weight:700;margin-bottom:8px"><i class="fa-solid fa-circle-info" style="color:var(--teal)"></i> {{ __('messages.integration_info_title') }}</p>
            <p style="color:#555;line-height:1.6">{{ __('messages.integration_info_body') }}</p>
        </div>
    </div>
</section>
@endsection
