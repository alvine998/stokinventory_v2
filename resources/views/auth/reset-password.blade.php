@extends('layouts.guest', ['title' => __('messages.reset_password')])

@section('content')
<section class="auth-card">
    <div>
        <p class="eyebrow"><i class="fa-solid fa-key"></i> {{ __('messages.reset_password') }}</p>
        <h1>{{ __('messages.reset_password_title') }}</h1>
        <p>{{ __('messages.reset_password_body') }}</p>
    </div>

    @include('partials.errors')

    <form method="POST" action="{{ route('password.update') }}" class="form-grid">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <label>{{ __('messages.email') }}
            <input type="email" value="{{ $email }}" disabled style="opacity:.6;cursor:not-allowed">
        </label>
        <label>{{ __('messages.new_password') }}
            <input name="password" type="password" placeholder="{{ __('messages.new_password') }}" required autofocus>
        </label>
        <label>{{ __('messages.confirm_password') }}
            <input name="password_confirmation" type="password" placeholder="{{ __('messages.confirm_password') }}" required>
        </label>

        <button class="primary-button"><i class="fa-solid fa-lock"></i> {{ __('messages.reset_password') }}</button>
    </form>

    <p style="text-align:center;margin-top:4px;font-size:13px">
        <a href="{{ route('login') }}" style="color:var(--teal);font-weight:600">
            <i class="fa-solid fa-arrow-left"></i> {{ __('messages.back_to_login') }}
        </a>
    </p>
</section>
@endsection
