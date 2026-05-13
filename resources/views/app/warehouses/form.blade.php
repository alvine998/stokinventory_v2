@php
    $isEdit = $mode === 'edit';
@endphp
@extends('layouts.app', [
    'title' => $isEdit ? __('messages.edit_warehouse') : __('messages.create_warehouse'),
    'heading' => $isEdit ? __('messages.edit_warehouse') : __('messages.create_warehouse'),
])

@section('content')
@include('partials.errors')

<form method="POST" action="{{ $isEdit ? route('warehouses.update', $warehouse) : route('warehouses.store') }}" class="panel entity-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.warehouses') }}</p>
            <h2><i class="fa-solid fa-warehouse"></i> {{ __('messages.warehouse_information') }}</h2>
        </div>
    </div>

    <div class="form-grid two">
        <label>{{ __('messages.select_store') }}
            <select name="store_id">
                <option value="">{{ __('messages.select_store') }}</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" @selected((int) old('store_id', $warehouse->store_id) === $store->id)>{{ $store->name }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('messages.warehouse_name') }}
            <input name="name" value="{{ old('name', $warehouse->name) }}" placeholder="{{ __('messages.warehouse_name') }}" required>
        </label>
        <label>{{ __('messages.code') }}
            <input name="code" value="{{ old('code', $warehouse->code) }}" placeholder="{{ __('messages.code') }}">
        </label>
        <label>{{ __('messages.status') }}
            <select name="status" required>
                <option value="active" @selected(old('status', $warehouse->status ?: 'active') === 'active')>{{ __('messages.active') }}</option>
                <option value="inactive" @selected(old('status', $warehouse->status) === 'inactive')>{{ __('messages.inactive') }}</option>
            </select>
        </label>
        <label class="span-2">{{ __('messages.address') }}
            <textarea name="address" placeholder="{{ __('messages.address') }}">{{ old('address', $warehouse->address) }}</textarea>
        </label>
    </div>

    <div class="product-form-actions">
        <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}</button>
        <a class="secondary-button" href="{{ route('warehouses.index') }}"><i class="fa-solid fa-arrow-left"></i> {{ __('messages.back_to_warehouses') }}</a>
    </div>
</form>
@endsection
