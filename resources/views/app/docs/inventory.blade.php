@extends('layouts.app', ['title' => 'Inventory Operations — Docs', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Documentation › Inventory Operations</p>
            <h2><i class="fa-solid fa-sliders"></i> Inventory Operations</h2>
            <p>Advanced tools for adjusting stock, moving goods between warehouses, and fine-tuning replenishment strategies.</p>
        </div>

        <div class="docs-req" style="margin-bottom:20px">
            <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: inventory_ops.manage</span>
            <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Products and warehouses must be set up first</span>
        </div>

        {{-- Adjustments --}}
        <div class="docs-section" id="adjustments">
            <div class="docs-section-title"><i class="fa-solid fa-sliders"></i> Stock Adjustments</div>
            <div class="docs-section-subtitle">Correct stock quantities due to damage, loss, found surplus, or corrections.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('inventory.adjustments') }}">Inventory → Adjustments</a></strong><p>Click "New Adjustment".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select product, warehouse, and type</strong><p>Types: <em>increase</em> (add to stock) or <em>decrease</em> (remove from stock).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Enter quantity and reason</strong><p>Common reasons: damaged goods, count correction, write-off, found stock.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Save</strong><p>Product stock is updated and the adjustment is logged in stock history.</p></div></div>
            </div>
        </div>

        {{-- Transfers --}}
        <div class="docs-section" id="transfers">
            <div class="docs-section-title"><i class="fa-solid fa-arrow-right-arrow-left"></i> Warehouse Transfers</div>
            <div class="docs-section-subtitle">Move stock from one warehouse to another. Stock is deducted from source and added to destination.</div>
            <div class="docs-req">
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> At least 2 warehouses required</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('inventory.transfers') }}">Transfers</a></strong><p>Click "New Transfer".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select source warehouse, destination warehouse, and product</strong><p>Available quantity at source is shown. Enter the quantity to transfer.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Stock is immediately moved. Both warehouses update their quantities.</p></div></div>
            </div>
            <div class="docs-warn"><i class="fa-solid fa-triangle-exclamation"></i> You cannot transfer more stock than is available at the source warehouse.</div>
        </div>

        {{-- History --}}
        <div class="docs-section" id="history">
            <div class="docs-section-title"><i class="fa-solid fa-clock-rotate-left"></i> Stock History</div>
            <div class="docs-section-subtitle">Full audit trail of every stock movement, adjustment, and transfer.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('inventory.history') }}">History</a></strong><p>Browse the chronological log of all inventory changes.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Filter by product, warehouse, or date range</strong><p>Use the filter bar at the top to narrow down results.</p></div></div>
            </div>
        </div>

        {{-- Min Stock Alert --}}
        <div class="docs-section" id="min-stock">
            <div class="docs-section-title"><i class="fa-solid fa-triangle-exclamation"></i> Min Stock Alerts</div>
            <div class="docs-section-subtitle">See all products whose current stock is at or below their minimum stock threshold.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('inventory.min-stock') }}">Min Stock Alerts</a></strong><p>Products are shown with current stock, minimum stock, and the deficit.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Create a purchase request</strong><p>Click the link to Purchasing to raise a Purchase Request for the flagged items.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> Set minimum stock on the product form to get meaningful alerts here.</div>
        </div>

        {{-- Reorder Point --}}
        <div class="docs-section" id="reorder-point">
            <div class="docs-section-title"><i class="fa-solid fa-rotate"></i> Reorder Point</div>
            <div class="docs-section-subtitle">The exact stock level at which a replenishment order should be triggered.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('inventory.reorder-point') }}">Reorder Point</a></strong><p>All products are listed with their current reorder point.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Click "Edit" on a product row</strong><p>Enter the reorder point value (e.g. 50 units).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>The forecast report uses this value to flag products needing restock.</p></div></div>
            </div>
        </div>

        {{-- Safety Stock --}}
        <div class="docs-section" id="safety-stock">
            <div class="docs-section-title"><i class="fa-solid fa-shield-halved"></i> Safety Stock</div>
            <div class="docs-section-subtitle">Buffer stock kept to protect against demand spikes or supply delays.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('inventory.safety-stock') }}">Safety Stock</a></strong><p>View and update safety stock levels per product.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Set the buffer quantity</strong><p>Recommended formula: Average daily demand × Lead time days.</p></div></div>
            </div>
        </div>

        {{-- Costing Method --}}
        <div class="docs-section" id="costing">
            <div class="docs-section-title"><i class="fa-solid fa-calculator"></i> Costing Method</div>
            <div class="docs-section-subtitle">Control how product cost is calculated when goods are consumed or sold.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('inventory.costing-method') }}">Costing Method</a></strong><p>Each product can have its own costing method.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Choose a method per product</strong><p><em>FIFO</em> (First In First Out), <em>LIFO</em> (Last In First Out), or <em>Average Cost (WAC)</em>.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>The Finance HPP report uses this method to calculate Cost of Goods Sold.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> FIFO is recommended for perishable goods. Average Cost is simpler and used by most SMEs.</div>
        </div>

        {{-- Serial Numbers --}}
        <div class="docs-section" id="serial-numbers">
            <div class="docs-section-title"><i class="fa-solid fa-barcode"></i> Serial Number Tracking</div>
            <div class="docs-section-subtitle">Track individual units by unique serial number — ideal for electronics, machinery, or high-value items.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('inventory.serial-numbers') }}">Serial Numbers</a></strong><p>Click "Add Serial Number".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select product and enter serial number</strong><p>Optionally link to a batch lot and set status (available, sold, defective).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Serial number is now tracked. Update its status as it moves through the supply chain.</p></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
