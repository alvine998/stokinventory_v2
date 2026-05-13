@extends('layouts.guest', ['title' => __('messages.login')])

@section('content')
<section class="auth-card">
    <div>
        <p class="eyebrow">{{ __('messages.welcome_back') }}</p>
        <h1>{{ __('messages.login_title') }}</h1>
        <p>{{ __('messages.login_body') }}</p>
    </div>
    @include('partials.errors')
    <form method="POST" action="{{ route('login.store') }}" class="form-grid">
        @csrf
        <label>{{ __('messages.email') }}<input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required autofocus></label>
        <label>{{ __('messages.password') }}<input name="password" type="password" placeholder="{{ __('messages.password') }}" required></label>
        <label class="check-row"><input name="remember" type="checkbox" value="1" placeholder="{{ __('messages.remember_me') }}"> {{ __('messages.remember_me') }}</label>
        <button class="primary-button"><i class="fa-solid fa-arrow-right-to-bracket"></i> {{ __('messages.login') }}</button>
    </form>
</section>
@endsection
