@extends('layouts.app', ['title' => __('messages.suppliers'), 'heading' => __('messages.master_data')])

@section('content')
@include('app.master-data._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.master_data') }}</p>
            <h2><i class="fa-solid fa-truck"></i> {{ __('messages.suppliers') }}</h2>
        </div>
        <a href="#modal-add-supplier" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_supplier') }}</a>
        <a href="{{ route('master-data.suppliers.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
        <a href="#modal-import-suppliers" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.supplier_name') }}</th>
                    <th>{{ __('messages.code') }}</th>
                    <th>{{ __('messages.contact_person') }}</th>
                    <th>{{ __('messages.phone') }}</th>
                    <th>{{ __('messages.email') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($suppliers as $supplier)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $supplier->name }}</strong></td>
                    <td>{{ $supplier->code ? '<code>'.$supplier->code.'</code>' : '—' }}</td>
                    <td>{{ $supplier->contact_person ?: '—' }}</td>
                    <td>{{ $supplier->phone ?: '—' }}</td>
                    <td>{{ $supplier->email ?: '—' }}</td>
                    <td>
                        <span class="badge-status {{ $supplier->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $supplier->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.suppliers.destroy', $supplier) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="7" style="padding:0">
                        <form method="POST" action="{{ route('master-data.suppliers.update', $supplier) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.supplier_name') }} <span class="req">*</span></span>
                                    <input name="name" value="{{ $supplier->name }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.code') }}</span>
                                    <input name="code" value="{{ $supplier->code }}">
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.contact_person') }}</span>
                                    <input name="contact_person" value="{{ $supplier->contact_person }}">
                                </label>
                                <label><span class="label-cap">{{ __('messages.phone') }}</span>
                                    <input name="phone" value="{{ $supplier->phone }}">
                                </label>
                            </div>
                            <label><span class="label-cap">{{ __('messages.email') }}</span>
                                <input name="email" type="email" value="{{ $supplier->email }}">
                            </label>
                            <label><span class="label-cap">{{ __('messages.address') }}</span>
                                <textarea name="address" rows="2">{{ $supplier->address }}</textarea>
                            </label>
                            <div class="form-row-spread">
                                <label class="check-row"><input name="is_active" type="checkbox" value="1" {{ $supplier->is_active ? 'checked' : '' }}> {{ __('messages.active') }}</label>
                                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="7" class="empty-cell">{{ __('messages.no_suppliers') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- Add Supplier Modal --}}
<div class="modal-overlay" id="modal-add-supplier" role="dialog" aria-modal="true">
    <div class="modal-card">
        <header>
            <h3><i class="fa-solid fa-truck"></i> {{ __('messages.add_supplier') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('master-data.suppliers.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.supplier_name') }} <span class="req">*</span></span>
                    <input name="name" value="{{ old('name') }}" required>
                </label>
                <label><span class="label-cap">{{ __('messages.code') }}</span>
                    <input name="code" value="{{ old('code') }}" placeholder="SUP-001">
                </label>
            </div>
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.contact_person') }}</span>
                    <input name="contact_person" value="{{ old('contact_person') }}">
                </label>
                <label><span class="label-cap">{{ __('messages.phone') }}</span>
                    <input name="phone" value="{{ old('phone') }}" placeholder="+62...">
                </label>
            </div>
            <label><span class="label-cap">{{ __('messages.email') }}</span>
                <input name="email" type="email" value="{{ old('email') }}">
            </label>
            <label><span class="label-cap">{{ __('messages.address') }}</span>
                <textarea name="address" rows="2">{{ old('address') }}</textarea>
            </label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

@include('partials._xl-import-modal', [
    'modalId'     => 'modal-import-suppliers',
    'title'       => __('messages.import') . ' ' . __('messages.suppliers'),
    'importRoute' => route('master-data.suppliers.import'),
    'columns'     => 'name, code, contact_person, phone, email, address, is_active',
])
@endsection
