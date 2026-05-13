@php
    $isEdit = $mode === 'edit';
@endphp
@extends('layouts.app', [
    'title' => $isEdit ? __('messages.edit_store') : __('messages.create_store'),
    'heading' => $isEdit ? __('messages.edit_store') : __('messages.create_store'),
])

@section('content')
@include('partials.errors')

<form method="POST" action="{{ $isEdit ? route('stores.update', $store) : route('stores.store') }}" class="panel entity-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.stores') }}</p>
            <h2><i class="fa-solid fa-store"></i> {{ __('messages.store_information') }}</h2>
        </div>
    </div>

    <div class="form-grid two">
        <label>{{ __('messages.store_name') }}
            <input name="name" value="{{ old('name', $store->name) }}" placeholder="{{ __('messages.store_name') }}" required>
        </label>
        <label>{{ __('messages.code') }}
            <input name="code" value="{{ old('code', $store->code) }}" placeholder="{{ __('messages.code') }}">
        </label>
        <label>{{ __('messages.phone') }}
            <input name="phone" value="{{ old('phone', $store->phone) }}" placeholder="{{ __('messages.phone') }}">
        </label>
        <label>{{ __('messages.status') }}
            <select name="status" required>
                <option value="active" @selected(old('status', $store->status ?: 'active') === 'active')>{{ __('messages.active') }}</option>
                <option value="inactive" @selected(old('status', $store->status) === 'inactive')>{{ __('messages.inactive') }}</option>
            </select>
        </label>
        <label class="span-2">{{ __('messages.address') }}
            <textarea name="address" placeholder="{{ __('messages.address') }}">{{ old('address', $store->address) }}</textarea>
        </label>
    </div>

    <div class="product-form-actions">
        <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
        <a class="secondary-button" href="{{ route('stores.index') }}"><i class="fa-solid fa-arrow-left"></i> {{ __('messages.back_to_stores') }}</a>
    </div>
</form>
@endsection
