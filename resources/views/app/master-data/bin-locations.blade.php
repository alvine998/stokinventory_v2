@extends('layouts.app', ['title' => __('messages.bin_locations'), 'heading' => __('messages.master_data')])

@section('content')
@include('app.master-data._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.master_data') }}</p>
            <h2><i class="fa-solid fa-location-dot"></i> {{ __('messages.bin_locations') }}</h2>
        </div>
        <a href="#modal-add-bin" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_bin_location') }}</a>
        <a href="{{ route('master-data.bin-locations.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
        <a href="#modal-import-bin-locations" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.code') }}</th>
                    <th>{{ __('messages.warehouse_name') }}</th>
                    <th>{{ __('messages.aisle') }}</th>
                    <th>{{ __('messages.rack') }}</th>
                    <th>{{ __('messages.level') }}</th>
                    <th>{{ __('messages.bin') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($binLocations as $binLoc)
            <tbody class="row-group">
                <tr>
                    <td><code><strong>{{ $binLoc->code }}</strong></code></td>
                    <td>{{ $binLoc->warehouse?->name ?? '—' }}</td>
                    <td>{{ $binLoc->aisle ?: '—' }}</td>
                    <td>{{ $binLoc->rack ?: '—' }}</td>
                    <td>{{ $binLoc->level ?: '—' }}</td>
                    <td>{{ $binLoc->bin ?: '—' }}</td>
                    <td>
                        <span class="badge-status {{ $binLoc->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $binLoc->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.bin-locations.destroy', $binLoc) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0">
                        <form method="POST" action="{{ route('master-data.bin-locations.update', $binLoc) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.code') }} <span class="req">*</span></span>
                                    <input name="code" value="{{ $binLoc->code }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.warehouse_name') }}</span>
                                    <select name="warehouse_id">
                                        <option value="">— {{ __('messages.select_warehouse') }} —</option>
                                        @foreach ($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ $binLoc->warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.aisle') }}</span>
                                    <input name="aisle" value="{{ $binLoc->aisle }}" placeholder="A">
                                </label>
                                <label><span class="label-cap">{{ __('messages.rack') }}</span>
                                    <input name="rack" value="{{ $binLoc->rack }}" placeholder="R1">
                                </label>
                            </div>
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.level') }}</span>
                                    <input name="level" value="{{ $binLoc->level }}" placeholder="L2">
                                </label>
                                <label><span class="label-cap">{{ __('messages.bin') }}</span>
                                    <input name="bin" value="{{ $binLoc->bin }}" placeholder="B01">
                                </label>
                            </div>
                            <label><span class="label-cap">{{ __('messages.description') }}</span>
                                <input name="description" value="{{ $binLoc->description }}">
                            </label>
                            <div class="form-row-spread">
                                <label class="check-row"><input name="is_active" type="checkbox" value="1" {{ $binLoc->is_active ? 'checked' : '' }}> {{ __('messages.active') }}</label>
                                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="8" class="empty-cell">{{ __('messages.no_bin_locations') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- Add Bin Location Modal --}}
<div class="modal-overlay" id="modal-add-bin" role="dialog" aria-modal="true">
    <div class="modal-card" style="max-width:580px">
        <header>
            <h3><i class="fa-solid fa-location-dot"></i> {{ __('messages.add_bin_location') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('master-data.bin-locations.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.code') }} <span class="req">*</span></span>
                    <input name="code" value="{{ old('code') }}" placeholder="A-R1-L2-B01" required>
                </label>
                <label><span class="label-cap">{{ __('messages.warehouse_name') }}</span>
                    <select name="warehouse_id">
                        <option value="">— {{ __('messages.select_warehouse') }} —</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.aisle') }}</span>
                    <input name="aisle" value="{{ old('aisle') }}" placeholder="A">
                </label>
                <label><span class="label-cap">{{ __('messages.rack') }}</span>
                    <input name="rack" value="{{ old('rack') }}" placeholder="R1">
                </label>
            </div>
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.level') }}</span>
                    <input name="level" value="{{ old('level') }}" placeholder="L2">
                </label>
                <label><span class="label-cap">{{ __('messages.bin') }}</span>
                    <input name="bin" value="{{ old('bin') }}" placeholder="B01">
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

@include('partials._xl-import-modal', [
    'modalId'     => 'modal-import-bin-locations',
    'title'       => __('messages.import') . ' Bin Locations',
    'importRoute' => route('master-data.bin-locations.import'),
    'columns'     => 'warehouse, code, aisle, rack, level, bin, description, is_active',
])
@endsection
