@extends('layouts.app', ['title' => __('messages.expedition'), 'heading' => __('messages.nav_sales')])

@section('content')
@include('app.sales._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_sales') }}</p>
            <h2><i class="fa-solid fa-plane-departure"></i> {{ __('messages.expedition') }}</h2>
        </div>
        <div class="head-actions">
            <div class="btn-group">
                <a href="{{ route('sales.expeditions.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
                <a href="#modal-import-expeditions" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
            </div>
            <a href="#modal-add-exp" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_expedition') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.name') }}</th>
                    <th>{{ __('messages.code') }}</th>
                    <th>{{ __('messages.tracking_url') }}</th>
                    <th>{{ __('messages.is_active') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($expeditions as $exp)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $exp->name }}</strong></td>
                    <td><code>{{ $exp->code ?? '—' }}</code></td>
                    <td>
                        @if($exp->tracking_url_template)
                            <code style="font-size:11px;word-break:break-all">{{ Str::limit($exp->tracking_url_template, 60) }}</code>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($exp->is_active)
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
                            <form method="POST" action="{{ route('sales.expeditions.destroy', $exp) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="5" style="padding:0">
                        <form method="POST" action="{{ route('sales.expeditions.update', $exp) }}" style="padding:16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.name') }} <span class="req">*</span></span>
                                    <input type="text" name="name" value="{{ $exp->name }}" placeholder="{{ __('messages.name') }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.code') }}</span>
                                    <input type="text" name="code" value="{{ $exp->code }}" placeholder="JNE">
                                </label>
                                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.tracking_url') }}</span>
                                    <input type="url" name="tracking_url_template" value="{{ $exp->tracking_url_template }}" placeholder="https://cek.jne.co.id/tracking?no={tracking_no}">
                                </label>
                                <label><span class="label-cap">{{ __('messages.is_active') }}</span>
                                    <select name="is_active">
                                        <option value="1" {{ $exp->is_active ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                        <option value="0" {{ !$exp->is_active ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
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
                <tbody><tr><td colspan="5" class="empty-cell">{{ __('messages.no_expeditions') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $expeditions->links() }}
</section>

<div class="modal-overlay" id="modal-add-exp">
    <div class="modal" style="max-width:500px">
        <div class="modal-head">
            <h3>{{ __('messages.new_expedition') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('sales.expeditions.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.name') }} <span class="req">*</span></span>
                    <input type="text" name="name" required>
                </label>
                <label><span class="label-cap">{{ __('messages.code') }}</span>
                    <input type="text" name="code" placeholder="JNE">
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.tracking_url') }}</span>
                    <input type="url" name="tracking_url_template" placeholder="https://example.com/tracking?no={tracking_no}">
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
    'modalId'     => 'modal-import-expeditions',
    'title'       => __('messages.import') . ' Expeditions',
    'importRoute' => route('sales.expeditions.import'),
    'columns'     => 'name, code, tracking_url_template, is_active',
])
@endsection
