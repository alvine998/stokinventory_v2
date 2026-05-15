@extends('layouts.app', ['title' => __('messages.price_levels'), 'heading' => __('messages.nav_sales')])

@section('content')
@include('app.sales._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_sales') }}</p>
            <h2><i class="fa-solid fa-tags"></i> {{ __('messages.price_levels') }}</h2>
        </div>
        <a href="#modal-add-pl" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_price_level') }}</a>
        <a href="{{ route('sales.price-levels.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
        <a href="#modal-import-price-levels" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.name') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.discount_percent') }}</th>
                    <th>{{ __('messages.is_default') }}</th>
                    <th>{{ __('messages.is_active') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($priceLevels as $pl)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $pl->name }}</strong></td>
                    <td>{{ $pl->description ?? '—' }}</td>
                    <td>{{ $pl->discount_percent > 0 ? $pl->discount_percent.'%' : '—' }}</td>
                    <td>
                        @if($pl->is_default)
                            <span class="badge-status badge-active"><i class="fa-solid fa-star"></i> {{ __('messages.default') }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($pl->is_active)
                            <span class="badge-status badge-active">{{ __('messages.active') }}</span>
                        @else
                            <span class="badge-status badge-inactive">{{ __('messages.inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('sales.price-levels.destroy', $pl) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="6" style="padding:0">
                        <form method="POST" action="{{ route('sales.price-levels.update', $pl) }}" style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.name') }} <span class="req">*</span></span>
                                    <input type="text" name="name" value="{{ $pl->name }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.discount_percent') }}</span>
                                    <input type="number" name="discount_percent" min="0" max="100" step="0.01" value="{{ $pl->discount_percent }}">
                                </label>
                                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.description') }}</span>
                                    <input type="text" name="description" value="{{ $pl->description }}">
                                </label>
                                <label><span class="label-cap">{{ __('messages.is_default') }}</span>
                                    <select name="is_default">
                                        <option value="0" {{ !$pl->is_default ? 'selected' : '' }}>{{ __('messages.no') }}</option>
                                        <option value="1" {{ $pl->is_default ? 'selected' : '' }}>{{ __('messages.yes') }}</option>
                                    </select>
                                </label>
                                <label><span class="label-cap">{{ __('messages.is_active') }}</span>
                                    <select name="is_active">
                                        <option value="1" {{ $pl->is_active ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                        <option value="0" {{ !$pl->is_active ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                                    </select>
                                </label>
                            </div>
                            <div style="margin-top:12px;display:flex;gap:8px">
                                <button type="submit" class="primary-button">{{ __('messages.save') }}</button>
                                <button type="button" class="secondary-button" onclick="this.closest('tbody').querySelector('.edit-row').hidden=true">{{ __('messages.cancel') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="6" class="empty-cell">{{ __('messages.no_price_levels') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $priceLevels->links() }}
</section>

<div class="modal-overlay" id="modal-add-pl">
    <div class="modal" style="max-width:460px">
        <div class="modal-head">
            <h3>{{ __('messages.new_price_level') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.price-levels.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.name') }} <span class="req">*</span></span>
                    <input type="text" name="name" required>
                </label>
                <label><span class="label-cap">{{ __('messages.discount_percent') }}</span>
                    <input type="number" name="discount_percent" min="0" max="100" step="0.01" value="0">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.description') }}</span>
                    <input type="text" name="description">
                </label>
                <label><span class="label-cap">{{ __('messages.is_default') }}</span>
                    <select name="is_default">
                        <option value="0" selected>{{ __('messages.no') }}</option>
                        <option value="1">{{ __('messages.yes') }}</option>
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.is_active') }}</span>
                    <select name="is_active">
                        <option value="1" selected>{{ __('messages.active') }}</option>
                        <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </label>
            </div>
            <div class="modal-footer">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>

@include('partials._xl-import-modal', [
    'modalId'     => 'modal-import-price-levels',
    'title'       => __('messages.import') . ' Price Levels',
    'importRoute' => route('sales.price-levels.import'),
    'columns'     => 'name, description, discount_percent, is_default, is_active',
])
@endsection
