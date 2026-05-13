@extends('layouts.guest', ['title' => __('messages.register')])

@section('content')
<section class="auth-card wide">
    <div>
        <p class="eyebrow">{{ __('messages.free_trial_30') }}</p>
        <h1>{{ __('messages.register_title') }}</h1>
        <p>{{ __('messages.register_body') }}</p>
    </div>
    @include('partials.errors')
    <form method="POST" action="{{ route('register.store') }}" class="form-grid two">
        @csrf
        <label>{{ __('messages.name') }}<input name="name" value="{{ old('name') }}" placeholder="{{ __('messages.name') }}" required></label>
        <label>{{ __('messages.company_name') }}<input name="company_name" value="{{ old('company_name') }}" placeholder="{{ __('messages.company_name') }}" required></label>
        <label>{{ __('messages.email') }}<input name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required></label>
        <label>{{ __('messages.password') }}<input name="password" type="password" placeholder="{{ __('messages.password') }}" required></label>
        <label>{{ __('messages.confirm_password') }}<input name="password_confirmation" type="password" placeholder="{{ __('messages.confirm_password') }}" required></label>
        <button class="primary-button"><i class="fa-solid fa-gift"></i> {{ __('messages.start_trial') }}</button>
    </form>
</section>
@endsection
