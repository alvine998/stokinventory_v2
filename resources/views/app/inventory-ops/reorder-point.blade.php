@extends('layouts.app', ['title' => __('messages.reorder_point'), 'heading' => __('messages.nav_inventory')])

@section('content')
@include('app.inventory-ops._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_inventory') }}</p>
            <h2><i class="fa-solid fa-rotate"></i> {{ __('messages.reorder_point') }}</h2>
        </div>
        <div class="head-actions">
            <div class="btn-group">
                <a href="{{ route('inventory.reorder-point.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
                <a href="#modal-import-reorder-point" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
            </div>
        </div>
    </div>

    @include('partials.errors')

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.current_stock') }}</th>
                    <th>{{ __('messages.minimum_stock') }}</th>
                    <th>{{ __('messages.reorder_point') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @foreach ($products as $product)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td><code>{{ $product->sku ?: '—' }}</code></td>
                    <td>
                        @php $low = $product->current_stock <= $product->reorder_point && $product->reorder_point > 0; @endphp
                        <span style="{{ $low ? 'color:var(--amber);font-weight:600' : '' }}">{{ number_format($product->current_stock) }}</span>
                        @if($low)<i class="fa-solid fa-rotate" style="color:var(--amber);margin-left:4px" title="{{ __('messages.needs_reorder') }}"></i>@endif
                    </td>
                    <td>{{ number_format($product->minimum_stock) }}</td>
                    <td>{{ number_format($product->reorder_point) }}</td>
                    <td>
                        <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                            onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="6" style="padding:0">
                        <form method="POST" action="{{ route('inventory.reorder-point.update', $product) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.reorder_point') }} <span class="req">*</span></span>
                                    <input type="number" name="reorder_point" value="{{ $product->reorder_point }}" min="0" placeholder="0" required>
                                </label>
                            </div>
                            <div style="display:flex;gap:8px">
                                <button class="primary-button">{{ __('messages.save') }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
            @endforeach
        </table>
    </div>
</section>

@include('partials._xl-import-modal', [
    'modalId'     => 'modal-import-reorder-point',
    'title'       => __('messages.import') . ' Reorder Point',
    'importRoute' => route('inventory.reorder-point.import'),
    'columns'     => 'product, sku, reorder_point',
])
@endsection
