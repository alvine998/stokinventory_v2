{{-- Reusable repeatable item rows for purchasing modals --}}
{{-- Usage: @include('app.purchasing._item-rows', ['products' => $products, 'prefix' => 'modal-add-pr']) --}}
<div class="item-rows-header" style="display:grid;grid-template-columns:1fr 100px 130px 24px;gap:8px;padding:0 0 4px;font-size:11px;font-weight:700;text-transform:uppercase;color:#888;letter-spacing:.04em">
    <span>{{ __('messages.product') }}</span>
    <span>{{ __('messages.qty') }}</span>
    <span>{{ __('messages.unit_price') }}</span>
    <span></span>
</div>
<div class="item-rows" id="{{ $prefix }}-items">
    <div class="item-row" style="display:grid;grid-template-columns:1fr 100px 130px 24px;gap:8px;margin-bottom:6px;align-items:center">
        <select name="items[0][product_id]" required>
            <option value="">— {{ __('messages.select_product') }} —</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }}{{ $p->sku ? ' ('.$p->sku.')' : '' }}</option>
            @endforeach
        </select>
        <input type="number" name="items[0][quantity]" min="0.01" step="0.01" placeholder="1" required>
        <input type="number" name="items[0][unit_price]" min="0" step="0.01" placeholder="0">
        <button type="button" class="icon-button remove-item-row" title="Remove" style="color:var(--rose)"><i class="fa-solid fa-xmark"></i></button>
    </div>
</div>
<button type="button" class="secondary-button" style="font-size:12px;padding:5px 12px;margin-top:4px" data-add-items="{{ $prefix }}-items">
    <i class="fa-solid fa-plus"></i> {{ __('messages.add_item') }}
</button>
