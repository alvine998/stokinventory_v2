@extends('layouts.app', ['title' => __('messages.safety_stock'), 'heading' => __('messages.nav_inventory')])

@section('content')
@include('app.inventory-ops._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_inventory') }}</p>
            <h2><i class="fa-solid fa-shield-halved"></i> {{ __('messages.safety_stock') }}</h2>
        </div>
        <div class="head-actions">
            <div class="btn-group">
                <a href="{{ route('inventory.safety-stock.export') }}" class="secondary-button"><i class="fa-solid fa-file-excel"></i> {{ __('messages.export') }}</a>
                <a href="#modal-import-safety-stock" class="secondary-button"><i class="fa-solid fa-file-import"></i> {{ __('messages.import') }}</a>
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
                    <th>{{ __('messages.safety_stock') }}</th>
                    <th>{{ __('messages.buffer') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @foreach ($products as $product)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td><code>{{ $product->sku ?: '—' }}</code></td>
                    <td>{{ number_format($product->current_stock) }}</td>
                    <td>{{ number_format($product->safety_stock) }}</td>
                    <td>
                        @php $buffer = $product->current_stock - $product->safety_stock; @endphp
                        <span style="color:{{ $buffer < 0 ? 'var(--rose)' : ($buffer < 10 ? 'var(--amber)' : 'var(--teal)') }};font-weight:600">
                            {{ $buffer >= 0 ? '+' : '' }}{{ number_format($buffer) }}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="icon-button" title="{{ __('messages.edit') }}"
                            onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden;this.querySelector('i').className=r.hidden?'fa-solid fa-pen':'fa-solid fa-chevron-up'">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="6" style="padding:0">
                        <form method="POST" action="{{ route('inventory.safety-stock.update', $product) }}" class="edit-inline-form">
                            @csrf @method('PATCH')
                            <div class="form-grid two">
                                <label><span class="label-cap">{{ __('messages.safety_stock') }} <span class="req">*</span></span>
                                    <input type="number" name="safety_stock" value="{{ $product->safety_stock }}" min="0" required>
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
    'modalId'     => 'modal-import-safety-stock',
    'title'       => __('messages.import') . ' Safety Stock',
    'importRoute' => route('inventory.safety-stock.import'),
    'columns'     => 'product, sku, safety_stock',
])
@endsection
