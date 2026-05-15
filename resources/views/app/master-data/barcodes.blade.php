@extends('layouts.app', ['title' => __('messages.barcodes'), 'heading' => __('messages.master_data')])

@section('content')
@include('app.master-data._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.master_data') }}</p>
            <h2><i class="fa-solid fa-barcode"></i> {{ __('messages.barcodes') }}</h2>
        </div>
        <a href="#modal-add-barcode" class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.add_barcode') }}</a>
        <a href="{{ route('master-data.barcodes.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.barcode_value') }}</th>
                    <th>{{ __('messages.barcode_type') }}</th>
                    <th>{{ __('messages.product_name') }}</th>
                    <th>{{ __('messages.primary_barcode') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($barcodes as $barcode)
            <tbody class="row-group">
                <tr>
                    <td><code style="font-size:13px;letter-spacing:.05em">{{ $barcode->value }}</code></td>
                    <td><span class="badge-tag">{{ strtoupper(str_replace('_', ' ', $barcode->barcode_type)) }}</span></td>
                    <td>{{ $barcode->product?->name ?? '—' }}</td>
                    <td>
                        @if ($barcode->is_primary)
                            <span class="badge-status badge-active">{{ __('messages.primary_barcode') }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                                onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('master-data.barcodes.destroy', $barcode) }}" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="icon-button" style="color:var(--rose)" title="{{ __('messages.delete') }}"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="5" style="padding:0">
                        <form method="POST" action="{{ route('master-data.barcodes.update', $barcode) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.barcode_value') }} <span class="req">*</span></span>
                                    <input name="value" value="{{ $barcode->value }}" required>
                                </label>
                                <label><span class="label-cap">{{ __('messages.barcode_type') }} <span class="req">*</span></span>
                                    <select name="barcode_type">
                                        @foreach (['barcode','qr_code','ean13','upc','code128'] as $type)
                                            <option value="{{ $type }}" {{ $barcode->barcode_type === $type ? 'selected' : '' }}>{{ strtoupper(str_replace('_',' ',$type)) }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <label><span class="label-cap">{{ __('messages.product_name') }}</span>
                                <select name="product_id">
                                    <option value="">— {{ __('messages.select_product') }} —</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ $barcode->product_id == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="form-row-spread">
                                <label class="check-row"><input name="is_primary" type="checkbox" value="1" {{ $barcode->is_primary ? 'checked' : '' }}> {{ __('messages.primary_barcode') }}</label>
                                <button class="primary-button"><i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save_changes') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
            <tbody><tr><td colspan="5" class="empty-cell">{{ __('messages.no_barcodes') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
</section>

{{-- Add Barcode Modal --}}
<div class="modal-overlay" id="modal-add-barcode" role="dialog" aria-modal="true">
    <div class="modal-card">
        <header>
            <h3><i class="fa-solid fa-barcode"></i> {{ __('messages.add_barcode') }}</h3>
            <a href="#" class="icon-button" aria-label="{{ __('messages.close') }}"><i class="fa-solid fa-xmark"></i></a>
        </header>
        <form method="POST" action="{{ route('master-data.barcodes.store') }}" class="form-grid">
            @csrf
            <div class="form-grid two">
                <label><span class="label-cap">{{ __('messages.barcode_value') }} <span class="req">*</span></span>
                    <input name="value" value="{{ old('value') }}" placeholder="1234567890128" required>
                </label>
                <label><span class="label-cap">{{ __('messages.barcode_type') }} <span class="req">*</span></span>
                    <select name="barcode_type">
                        @foreach (['barcode','qr_code','ean13','upc','code128'] as $type)
                            <option value="{{ $type }}" {{ old('barcode_type') === $type ? 'selected' : '' }}>{{ strtoupper(str_replace('_',' ',$type)) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label><span class="label-cap">{{ __('messages.product_name') }}</span>
                <select name="product_id">
                    <option value="">— {{ __('messages.select_product') }} —</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="check-row"><input name="is_primary" type="checkbox" value="1"> {{ __('messages.primary_barcode') }}</label>
            <div class="modal-actions">
                <a href="#" class="secondary-button">{{ __('messages.cancel') }}</a>
                <button class="primary-button"><i class="fa-solid fa-plus"></i> {{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
