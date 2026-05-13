@extends('layouts.app', ['title' => __('messages.brands'), 'heading' => __('messages.master_data')])

@section('content')
@include('app.master-data._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.master_data') }}</p>
            <h2><i class="fa-solid fa-certificate"></i> {{ __('messages.brands') }}</h2>
        </div>
        <a href="#modal-add-brand" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_brand') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.brand_name') }}</th>
                    <th>{{ __('messages.code') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($brands as $brand)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $brand->name }}</strong></td>
                    <td>{{ $brand->code ? '<code>'.$brand->code.'</code>' : '—' }}</td>
                    <td>{{ $brand->description ?: '—' }}</td>
                    <td>
                        <span class="badge-status {{ $brand->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $brand->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.brands.destroy', $brand) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="5" style="padding:0">
                        <form method="POST" action="{{ route('master-data.brands.update', $brand) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.brand_name') }} <span class="req">*</span></span>
                                    <input name="name" value="{{ $brand->name }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.code') }}</span>
                                    <input name="code" value="{{ $brand->code }}">
                                </label>
                            </div>
                            <label><span class="label-cap">{{ __('messages.description') }}</span>
                                <input name="description" value="{{ $brand->description }}">
                            </label>
                            <div class="form-row-spread">
                                <label class="check-row"><input name="is_active" type="checkbox" value="1" {{ $brand->is_active ? 'checked' : '' }}> {{ __('messages.active') }}</label>
                                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="5" class="empty-cell">{{ __('messages.no_brands') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- Add Brand Modal --}}
<div class="modal-overlay" id="modal-add-brand" role="dialog" aria-modal="true">
    <div class="modal-card">
        <header>
            <h3><i class="fa-solid fa-certificate"></i> {{ __('messages.add_brand') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('master-data.brands.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.brand_name') }} <span class="req">*</span></span>
                    <input name="name" value="{{ old('name') }}" required>
                </label>
                <label><span class="label-cap">{{ __('messages.code') }}</span>
                    <input name="code" value="{{ old('code') }}" placeholder="BRD-01">
                </label>
            </div>
            <label><span class="label-cap">{{ __('messages.description') }}</span>
                <input name="description" value="{{ old('description') }}">
            </label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
