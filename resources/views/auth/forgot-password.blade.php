@extends('layouts.guest', ['title' => __('messages.forgot_password')])

@section('content')
<section class="auth-card">
    <div>
        <p class="eyebrow"><i class="fa-solid fa-lock-open"></i> {{ __('messages.forgot_password') }}</p>
        <h1>{{ __('messages.forgot_password_title') }}</h1>
        <p>{{ __('messages.forgot_password_body') }}</p>
    </div>

    @if (session('status'))
        <div style="background:color-mix(in srgb,var(--teal) 12%,transparent);border:1px solid color-mix(in srgb,var(--teal) 30%,transparent);border-radius:10px;padding:14px 16px;display:flex;gap:10px;align-items:flex-start;font-size:13px;color:var(--ink)">
            <i class="fa-solid fa-circle-check" style="color:var(--teal);margin-top:1px;flex-shrink:0"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @include('partials.errors')

    <form method="POST" action="{{ route('password.email') }}" class="form-grid">
        @csrf
        <label>{{ __('messages.email') }}
            <input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required autofocus>
        </label>
        <button class="primary-button"><i class="fa-solid fa-paper-plane"></i> {{ __('messages.send_reset_link') }}</button>
    </form>

    <p style="text-align:center;margin-top:4px;font-size:13px">
        <a href="{{ route('login') }}" style="color:var(--teal);font-weight:600">
            <i class="fa-solid fa-arrow-left"></i> {{ __('messages.back_to_login') }}
        </a>
    </p>
</section>
@endsection
