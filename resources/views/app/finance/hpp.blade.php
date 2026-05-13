@extends('layouts.app', ['title' => __('messages.hpp_auto'), 'heading' => __('messages.nav_finance')])

@section('content')
@include('app.finance._subnav')

<section class="panel">
    <div class="section-head">
        <div>
            <p class="eyebrow">{{ __('messages.nav_finance') }}</p>
            <h2><i class="fa-solid fa-calculator"></i> {{ __('messages.hpp_auto') }}</h2>
        </div>
    </div>

    @include('partials.errors')

    {{-- HPP Method Config --}}
    <div class="panel-section" style="max-width:560px;margin-bottom:28px">
        <h3 style="font-size:14px;font-weight:700;margin-bottom:12px">{{ __('messages.hpp_method_config') }}</h3>
        <form method="POST" action="{{ route('finance.hpp.update') }}">
            @csrf
            <div class="form-grid two">
                <label style="grid-column:span 2">
                    <span class="label-cap">{{ __('messages.costing_method') }}</span>
                    <select name="method">
                        <option value="weighted_average" {{ $config->method === 'weighted_average' ? 'selected' : '' }}>{{ __('messages.hpp_method_weighted_average') }}</option>
                        <option value="fifo"             {{ $config->method === 'fifo'             ? 'selected' : '' }}>{{ __('messages.hpp_method_fifo') }}</option>
                        <option value="lifo"             {{ $config->method === 'lifo'             ? 'selected' : '' }}>{{ __('messages.hpp_method_lifo') }}</option>
                    </select>
                </label>
                <label style="grid-column:span 2;display:flex;align-items:center;gap:10px">
                    <input type="hidden" name="is_auto" value="0">
                    <input type="checkbox" name="is_auto" value="1" id="is_auto" {{ $config->is_auto ? 'checked' : '' }} style="width:16px;height:16px">
                    <span class="label-cap" for="is_auto" style="margin-bottom:0;font-size:13px">{{ __('messages.hpp_auto_update') }}</span>
                </label>
                <label style="grid-column:span 2"><span class="label-cap">{{ __('messages.notes') }}</span>
                    <textarea name="notes" rows="2">{{ $config->notes }}</textarea>
                </label>
            </div>
            <button class="primary-button" style="margin-top:10px">{{ __('messages.save') }}</button>
        </form>
    </div>

    {{-- Product cost_price table --}}
    <h3 style="font-size:14px;font-weight:700;margin-bottom:12px">{{ __('messages.product_cost_prices') }}</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th class="num">{{ __('messages.current_stock') }}</th>
                    <th class="num">{{ __('messages.cost_price') }}</th>
                    <th class="num">{{ __('messages.selling_price') }}</th>
                    <th class="num">{{ __('messages.stock_value') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            @forelse ($products as $product)
            <tbody class="row-group">
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td><code>{{ $product->sku ?? '—' }}</code></td>
                    <td class="num">{{ number_format($product->current_stock, 2) }} {{ $product->unit }}</td>
                    <td class="num">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td class="num" style="font-weight:600">Rp {{ number_format($product->cost_price * $product->current_stock, 0, ',', '.') }}</td>
                    <td>
                        <button type="button" class="icon-button" onclick="const r=this.closest('tbody').querySelector('.edit-row');r.hidden=!r.hidden">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </td>
                </tr>
                <tr class="edit-row" hidden>
                    <td colspan="7" style="padding:0">
                        <form method="POST" action="{{ route('finance.hpp.product', $product) }}" style="padding:14px 16px;background:#f6fafc;border-top:2px solid #e3ecef;display:flex;align-items:flex-end;gap:12px">
                            @csrf @method('PATCH')
                            <label>
                                <span class="label-cap">{{ __('messages.cost_price') }}</span>
                                <input type="number" name="cost_price" min="0" step="0.01" value="{{ $product->cost_price }}" style="width:160px" required>
                            </label>
                            <button class="primary-button">{{ __('messages.save') }}</button>
                            <button type="button" class="secondary-button" onclick="this.closest('tbody').querySelector('.edit-row').hidden=true">{{ __('messages.cancel') }}</button>
                        </form>
                    </td>
                </tr>
            </tbody>
            @empty
                <tbody><tr><td colspan="7" class="empty-cell">{{ __('messages.no_products') }}</td></tr></tbody>
            @endforelse
        </table>
    </div>
    {{ $products->links() }}
</section>
@endsection
