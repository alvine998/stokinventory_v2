@extends('layouts.app', ['title' => __('messages.units'), 'heading' => __('messages.master_data')])

@section('content')
@include('app.master-data._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.master_data') }}</p>
            <h2><i class="fa-solid fa-ruler"></i> {{ __('messages.units') }}</h2>
        </div>
        <a href="#modal-add-unit" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_unit') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.unit_name') }}</th>
                    <th>{{ __('messages.symbol') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($units as $unit)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $unit->name }}</strong></td>
                    <td><code>{{ $unit->symbol }}</code></td>
                    <td>{{ $unit->description ?: '—' }}</td>
                    <td>
                        <span class="badge-status {{ $unit->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $unit->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.units.destroy', $unit) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="5" style="padding:0">
                        <form method="POST" action="{{ route('master-data.units.update', $unit) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.unit_name') }} <span class="req">*</span></span>
                                    <input name="name" value="{{ $unit->name }}" required placeholder="Piece">
                                </label>
                                <label><span class="label-cap">{{ __('messages.symbol') }} <span class="req">*</span></span>
                                    <input name="symbol" value="{{ $unit->symbol }}" required placeholder="pcs">
                                </label>
                            </div>
                            <label><span class="label-cap">{{ __('messages.description') }}</span>
                                <input name="description" value="{{ $unit->description }}">
                            </label>
                            <div class="form-row-spread">
                                <label class="check-row"><input name="is_active" type="checkbox" value="1" {{ $unit->is_active ? 'checked' : '' }}> {{ __('messages.active') }}</label>
                                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="5" class="empty-cell">{{ __('messages.no_units') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- Add Unit Modal --}}
<div class="modal-overlay" id="modal-add-unit" role="dialog" aria-modal="true">
    <div class="modal-card">
        <header>
            <h3><i class="fa-solid fa-ruler"></i> {{ __('messages.add_unit') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('master-data.units.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.unit_name') }} <span class="req">*</span></span>
                    <input name="name" value="{{ old('name') }}" placeholder="Piece" required>
                </label>
                <label><span class="label-cap">{{ __('messages.symbol') }} <span class="req">*</span></span>
                    <input name="symbol" value="{{ old('symbol') }}" placeholder="pcs" required>
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
