@extends('layouts.app', ['title' => __('messages.tax_ppn'), 'heading' => __('messages.nav_finance')])

@section('content')
@include('app.finance._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_finance') }}</p>
            <h2><i class="fa-solid fa-percent"></i> {{ __('messages.tax_ppn') }}</h2>
        </div>
        <div class="head-actions">
            <div class="btn-group">
                <a href="{{ route('finance.tax.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
                <a href="#modal-import-tax" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
            </div>
            <a href="#modal-add-tax" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.new_tax') }}</a>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.name') }}</th>
                    <th>{{ __('messages.code') }}</th>
                    <th>{{ __('messages.tax_type') }}</th>
                    <th class="num">{{ __('messages.rate') }}</th>
                    <th>{{ __('messages.inclusive') }}</th>
                    <th>{{ __('messages.applies_to') }}</th>
                    <th>{{ __('messages.is_active') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($taxes as $tax)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $tax->name }}</strong></td>
                    <td><code>{{ $tax->code }}</code></td>
                    <td><span class="badge-status">{{ strtoupper($tax->tax_type) }}</span></td>
                    <td class="num">{{ $tax->rate_percent }}%</td>
                    <td>
                        @if($tax->is_inclusive)
                            <span class="badge-status badge-active">{{ __('messages.inclusive') }}</span>
                        @else
                            <span class="badge-status">{{ __('messages.exclusive') }}</span>
                        @endif
                    </td>
                    <td>{{ __('messages.tax_applies_'.$tax->applies_to) }}</td>
                    <td>
                        @if($tax->is_active)
                            <span class="badge-status badge-active">{{ __('messages.active') }}</span>
                        @else
                            <span class="badge-status badge-inactive">{{ __('messages.inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden"><i class="fa-solid fa-pen"></i></button>
                            <form method="POST" action="{{ route('finance.tax.destroy', $tax) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="8" style="padding:0">
                        <form method="POST" action="{{ route('finance.tax.update', $tax) }}" style="padding:14px 16px;background:#f6fafc;border-top:2px solid #e3ecef">
                            @csrf @method('PATCH')
                            <div class="form-grid two" style="grid-template-columns:1fr 1fr 1fr">
                                <label><span class="label-cap">{{ __('messages.name') }} <span class="req">*</span></span>
                                    <input type="text" name="name" value="{{ $tax->name }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.code') }} <span class="req">*</span></span>
                                    <input type="text" name="code" value="{{ $tax->code }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.rate') }} % <span class="req">*</span></span>
                                    <input type="number" name="rate_percent" min="0" max="100" step="0.01" value="{{ $tax->rate_percent }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.tax_type') }}</span>
                                    <select name="tax_type">
                                        <option value="ppn" {{ $tax->tax_type==='ppn'?'selected':'' }}>PPN</option>
                                        <option value="pph" {{ $tax->tax_type==='pph'?'selected':'' }}>PPh</option>
                                        <option value="other" {{ $tax->tax_type==='other'?'selected':'' }}>{{ __('messages.other') }}</option>
                                    </select>
                                </label>
                                <label><span class="label-cap">{{ __('messages.applies_to') }}</span>
                                    <select name="applies_to">
                                        <option value="sales"     {{ $tax->applies_to==='sales'    ?'selected':'' }}>{{ __('messages.tax_applies_sales') }}</option>
                                        <option value="purchases" {{ $tax->applies_to==='purchases'?'selected':'' }}>{{ __('messages.tax_applies_purchases') }}</option>
                                        <option value="all"       {{ $tax->applies_to==='all'       ?'selected':'' }}>{{ __('messages.tax_applies_all') }}</option>
                                    </select>
                                </label>
                                <label><span class="label-cap">{{ __('messages.is_active') }}</span>
                                    <select name="is_active">
                                        <option value="1" {{ $tax->is_active?'selected':'' }}>{{ __('messages.active') }}</option>
                                        <option value="0" {{ !$tax->is_active?'selected':'' }}>{{ __('messages.inactive') }}</option>
                                    </select>
                                </label>
                                <label style="grid-column:span 3;display:flex;align-items:center;gap:10px">
                                    <input type="hidden" name="is_inclusive" value="0">
                                    <input type="checkbox" name="is_inclusive" value="1" {{ $tax->is_inclusive?'checked':'' }} style="width:16px;height:16px">
                                    <span class="label-cap" style="margin-bottom:0;font-size:13px">{{ __('messages.tax_inclusive_hint') }}</span>
                                </label>
                            </div>
                            <div style="margin-top:10px;display:flex;gap:8px">
                                <button class="primary-button">{{ __('messages.save') }}</button>
                                <button type="button" class="secondary-button" onclick="this.closest('tbody').querySelector('.edit-row').hidden=true">{{ __('messages.cancel') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="8" class="empty-cell">{{ __('messages.no_taxes') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

<div class="modal-overlay" id="modal-add-tax">
    <div class="modal" style="max-width:540px">
        <div class="modal-head">
            <h3>{{ __('messages.new_tax') }}</h3>
            <a href="#" class="modal-close"><i class="fa-solid fa-xmark"></i></a>
        </div>
        <form method="POST" action="{{ route('finance.tax.store') }}" class="modal-body">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.name') }} <span class="req">*</span></span>
                    <input type="text" name="name" placeholder="PPN 11%" required>
                </label>
                <label><span class="label-cap">{{ __('messages.code') }} <span class="req">*</span></span>
                    <input type="text" name="code" placeholder="PPN" required>
                </label>
                <label><span class="label-cap">{{ __('messages.rate') }} % <span class="req">*</span></span>
                    <input type="number" name="rate_percent" min="0" max="100" step="0.01" value="11" required>
                </label>
                <label><span class="label-cap">{{ __('messages.tax_type') }}</span>
                    <select name="tax_type">
                        <option value="ppn" selected>PPN</option>
                        <option value="pph">PPh</option>
                        <option value="other">{{ __('messages.other') }}</option>
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.applies_to') }}</span>
                    <select name="applies_to">
                        <option value="sales" selected>{{ __('messages.tax_applies_sales') }}</option>
                        <option value="purchases">{{ __('messages.tax_applies_purchases') }}</option>
                        <option value="all">{{ __('messages.tax_applies_all') }}</option>
                    </select>
                </label>
                <label><span class="label-cap">{{ __('messages.is_active') }}</span>
                    <select name="is_active">
                        <option value="1" selected>{{ __('messages.active') }}</option>
                        <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </label>
                <label style="grid-column:span 2;display:flex;align-items:center;gap:10px;margin-top:4px">
                    <input type="hidden" name="is_inclusive" value="0">
                    <input type="checkbox" name="is_inclusive" value="1" style="width:16px;height:16px">
                    <span class="label-cap" style="margin-bottom:0;font-size:13px">{{ __('messages.tax_inclusive_hint') }}</span>
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
    'modalId'     => 'modal-import-tax',
    'title'       => __('messages.import') . ' Tax',
    'importRoute' => route('finance.tax.import'),
    'columns'     => 'name, code, rate_percent, tax_type (ppn/pph/other), is_inclusive, applies_to (sales/purchases/all), is_active',
])
@endsection
