@extends('layouts.app', ['title' => 'Products & Stock — Docs', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Documentation › Products & Stock</p>
            <h2><i class="fa-solid fa-boxes-stacked"></i> Products & Stock</h2>
            <p>Manage your product catalog, record stock movements, and conduct physical stock counts.</p>
        </div>

        {{-- Stores --}}
        <div class="docs-section" id="stores">
            <div class="docs-section-title"><i class="fa-solid fa-store"></i> Stores</div>
            <div class="docs-section-subtitle">Physical or virtual sales locations. Products are sold from a store.</div>
            <div class="docs-req">
                <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: stores.manage</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('stores.index') }}">Stores</a></strong><p>Click "Add Store".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Fill in store details</strong><p>Name, address, phone, and the warehouse that supplies this store.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Store is now available for selection in sales orders and stock movements.</p></div></div>
            </div>
        </div>

        {{-- Warehouses --}}
        <div class="docs-section" id="warehouses">
            <div class="docs-section-title"><i class="fa-solid fa-warehouse"></i> Warehouses</div>
            <div class="docs-section-subtitle">Storage facilities that hold your physical stock.</div>
            <div class="docs-req">
                <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: warehouses.manage</span>
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Create at least one warehouse before adding products</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('warehouses.index') }}">Warehouses</a></strong><p>Click "Add Warehouse".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Enter name, code, address, and contact</strong><p>The code is a short identifier used in transfer documents.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Warehouse is active and can hold stock from goods receiving and adjustments.</p></div></div>
            </div>
        </div>

        {{-- Products --}}
        <div class="docs-section" id="products">
            <div class="docs-section-title"><i class="fa-solid fa-boxes-stacked"></i> Products</div>
            <div class="docs-section-subtitle">The core item registry. Every item you buy, sell, or store must be a product.</div>
            <div class="docs-req">
                <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: products.manage</span>
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Categories and units must exist first</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('products.index') }}">Products</a></strong><p>Click "Add Product" or "Import" for bulk upload.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Fill in the product form</strong><p>Required: name, SKU, category, unit. Optional: brand, selling price, cost price, minimum stock level, description, photo.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Set minimum stock</strong><p>When current stock drops to or below this threshold, the product appears in Min Stock Alerts.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Save</strong><p>Product is now visible in the catalog and can be added to transactions.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> SKU must be unique per business. Use a consistent naming convention like <code>CAT-BRAND-001</code>.</div>
        </div>

        {{-- Stock Movements --}}
        <div class="docs-section" id="movements">
            <div class="docs-section-title"><i class="fa-solid fa-right-left"></i> Stock Movements</div>
            <div class="docs-section-subtitle">Manually record stock in (inbound) or stock out (outbound) for a product.</div>
            <div class="docs-req">
                <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: stock.manage</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('stock-movements.index') }}">Stock Movements</a></strong><p>The table shows all historical movements sorted by date.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Click "Record Movement"</strong><p>Select: product, movement type (in/out), quantity, warehouse, date, and optional reference number.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>The product's <code>current_stock</code> is updated automatically based on the type (+qty for "in", −qty for "out").</p></div></div>
            </div>
            <div class="docs-warn"><i class="fa-solid fa-triangle-exclamation"></i> Stock movements cannot bring stock below zero. The system will block the save if the result would be negative.</div>
        </div>

        {{-- Stock Opname --}}
        <div class="docs-section" id="opname">
            <div class="docs-section-title"><i class="fa-solid fa-clipboard-check"></i> Stock Opname (Physical Count)</div>
            <div class="docs-section-subtitle">Reconcile the system stock count against an actual physical count.</div>
            <div class="docs-req">
                <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: stock.manage</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('stock-opname.index') }}">Stock Opname</a></strong><p>Click "New Opname Count".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select product and warehouse</strong><p>The system stock (expected) is shown. Enter the actual physical count quantity.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Submit</strong><p>The system calculates the variance (actual − expected). If you approve the opname, the product's stock is adjusted to the actual count.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Update status</strong><p>Set status to <em>approved</em> to apply the adjustment, or <em>rejected</em> to discard it.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> Conduct opname regularly (monthly or quarterly) to catch shrinkage, theft, or data entry errors early.</div>
        </div>
    </div>
</div>
@endsection
