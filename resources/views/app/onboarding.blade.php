@extends('layouts.app', ['title' => __('messages.onboarding'), 'heading' => __('messages.onboarding')])

@section('content')
<section class="panel split">
    <div>
        <p class="eyebrow">{{ __('messages.setup_workspace') }}</p>
        <h2>{{ __('messages.onboarding_title') }}</h2>
        <p>{{ __('messages.onboarding_body') }}</p>
        <div class="timeline">
            <span class="active">1</span><span>2</span><span>3</span>
        </div>
    </div>
    <div>
        @include('partials.errors')
        <form method="POST" action="{{ route('onboarding.store') }}" class="form-grid">
            @csrf
            <label>{{ __('messages.industry') }}<input name="industry" placeholder="Retail, F&B, Distributor" required></label>
            <label>{{ __('messages.business_size') }}<select name="business_size" required><option value="1-10">1-10</option><option value="11-50">11-50</option><option value="51+">51+</option></select></label>
            <label>{{ __('messages.inventory_goal') }}<input name="inventory_goal" placeholder="{{ __('messages.inventory_goal_placeholder') }}" required></label>
            <label class="check-row"><input name="has_multiple_locations" type="checkbox" value="1" placeholder="{{ __('messages.multiple_locations') }}"> {{ __('messages.multiple_locations') }}</label>
            <button class="primary-button"><i class="fa-solid fa-check"></i> {{ __('messages.finish_onboarding') }}</button>
        </form>
    </div>
</section>
@endsection
