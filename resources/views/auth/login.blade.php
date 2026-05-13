@extends('layouts.guest', ['title' => __('messages.login')])

@section('content')
<section class="auth-card">
    <div>
        <p class="eyebrow">{{ __('messages.welcome_back') }}</p>
        <h1>{{ __('messages.login_title') }}</h1>
        <p>{{ __('messages.login_body') }}</p>
    </div>
    @include('partials.errors')
    @if (session('status'))
        <div style="background:color-mix(in srgb,var(--teal) 12%,transparent);border:1px solid color-mix(in srgb,var(--teal) 30%,transparent);border-radius:10px;padding:14px 16px;display:flex;gap:10px;align-items:flex-start;font-size:13px;color:var(--ink)">
            <i class="fa-solid fa-circle-check" style="color:var(--teal);margin-top:1px;flex-shrink:0"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    <form method="POST" action="{{ route('login.store') }}" class="form-grid">
        @csrf
        <label>{{ __('messages.email') }}<input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required autofocus></label>
        <label>{{ __('messages.password') }}<input name="password" type="password" placeholder="{{ __('messages.password') }}" required></label>
        <div style="display:flex;justify-content:flex-end;margin-top:-8px">
            <a href="{{ route('password.request') }}" style="font-size:12px;color:var(--teal);font-weight:600">{{ __('messages.forgot_password') }}</a>
        </div>
        <label class="check-row"><input name="remember" type="checkbox" value="1" placeholder="{{ __('messages.remember_me') }}"> {{ __('messages.remember_me') }}</label>
        <button class="primary-button"><i class="fa-solid fa-arrow-right-to-bracket"></i> {{ __('messages.login') }}</button>
    </form>
</section>
@endsection
