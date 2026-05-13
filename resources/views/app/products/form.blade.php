@php
    $isEdit = $mode === 'edit';
@endphp
@extends('layouts.app', [
    'title' => $isEdit ? __('messages.edit_product') : __('messages.create_product'),
    'heading' => $isEdit ? __('messages.edit_product') : __('messages.create_product'),
])

@section('content')
@include('partials.errors')

<form method="POST" action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}" class="product-editor" enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="panel">
        <div class="section-head">
            <div>
                <p class="eyebrow">{{ __('messages.products') }}</p>
                <h2><i class="fa-solid fa-boxes-stacked"></i> {{ __('messages.product_information') }}</h2>
            </div>
        </div>

        <div class="form-grid two">
            <label>SKU
                <input name="sku" value="{{ old('sku', $product->sku) }}" placeholder="Example: SKU-001">
            </label>
            <label>{{ __('messages.product_name') }}
                <input name="name" value="{{ old('name', $product->name) }}" placeholder="{{ __('messages.product_name') }}" required>
            </label>
            <label>{{ __('messages.category') }}
                <input name="category" value="{{ old('category', $product->category) }}" placeholder="{{ __('messages.category') }}">
            </label>
            <label>{{ __('messages.unit') }}
                <input name="unit" value="{{ old('unit', $product->unit ?: 'pcs') }}" placeholder="{{ __('messages.unit') }}" required>
            </label>
            <label>{{ __('messages.price') }}
                <input name="price" type="number" min="0" step="0.01" value="{{ old('price', $product->price) }}" placeholder="{{ __('messages.price') }}" required>
            </label>
            <label>{{ __('messages.minimum_stock') }}
                <input name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', $product->minimum_stock) }}" placeholder="{{ __('messages.minimum_stock') }}" required>
            </label>
            <label>{{ __('messages.current_stock') }}
                <input name="current_stock" type="number" min="0" value="{{ old('current_stock', $product->current_stock) }}" placeholder="{{ __('messages.current_stock') }}" required>
            </label>
        </div>

        <div class="product-form-actions">
            <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_product') }}</button>
            <a class="secondary-button" href="{{ route('products.index') }}"><i class="fa-solid fa-arrow-left"></i> {{ __('messages.back_to_products') }}</a>
        </div>
    </section>

    <aside class="panel product-photo-panel">
        <div class="section-head">
            <div>
                <p class="eyebrow">{{ __('messages.image') }}</p>
                <h2><i class="fa-solid fa-image"></i> {{ __('messages.product_media') }}</h2>
            </div>
        </div>

        @if ($product->photo_path)
            <img class="current-photo" src="{{ asset('storage/' . $product->photo_path) }}" alt="{{ __('messages.current_photo') }}">
        @else
            <div class="photo-placeholder"><i class="fa-solid fa-box-open"></i></div>
        @endif

        <div class="photo-dropzone">
            <label>{{ __('messages.product_photo') }}
                <input name="photo" type="file" accept="image/*" placeholder="{{ __('messages.product_photo') }}">
            </label>
            <p>{{ __('messages.product_photo') }}</p>
        </div>
    </aside>
</form>
@endsection
