@extends('layouts.app', ['title' => 'Master Data — Docs', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Documentation › Master Data</p>
            <h2><i class="fa-solid fa-database"></i> Master Data</h2>
            <p>Foundational reference data used across all modules. Always set up master data before creating products or transactions.</p>
        </div>

        <div class="docs-req" style="margin-bottom:20px">
            <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: master_data.manage</span>
            <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Set up categories and units before adding products</span>
        </div>

        {{-- Categories --}}
        <div class="docs-section" id="categories">
            <div class="docs-section-title"><i class="fa-solid fa-tag"></i> Categories</div>
            <div class="docs-section-subtitle">Group products by type for filtering and reporting purposes.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.categories') }}">Master Data → Categories</a></strong><p>All existing categories are listed in the table.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Click "Add Category"</strong><p>Enter the category name and an optional description.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>The category is now available when creating or editing a product.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Edit or delete</strong><p>Click the row to expand it, then use the Edit or Delete action. Deleting a category that has products attached will be blocked.</p></div></div>
            </div>
        </div>

        {{-- Brands --}}
        <div class="docs-section" id="brands">
            <div class="docs-section-title"><i class="fa-solid fa-certificate"></i> Brands</div>
            <div class="docs-section-subtitle">Optionally tag products with a brand or manufacturer.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.brands') }}">Brands</a></strong><p>Enter a brand name. Optionally add a website or description.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Save</strong><p>Brand will appear in the product form's brand dropdown.</p></div></div>
            </div>
        </div>

        {{-- Suppliers --}}
        <div class="docs-section" id="suppliers">
            <div class="docs-section-title"><i class="fa-solid fa-truck"></i> Suppliers</div>
            <div class="docs-section-subtitle">Register vendors you purchase goods from. Required before creating purchase orders.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.suppliers') }}">Suppliers</a></strong><p>Click "Add Supplier".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Fill in supplier details</strong><p>Name, contact person, phone, email, address, and optional tax ID (NPWP).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>This supplier can now be selected in purchase requests and purchase orders.</p></div></div>
            </div>
        </div>

        {{-- Customers --}}
        <div class="docs-section" id="customers">
            <div class="docs-section-title"><i class="fa-solid fa-address-book"></i> Customers</div>
            <div class="docs-section-subtitle">Register buyers used in sales orders and invoices.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.customers') }}">Customers</a></strong><p>Click "Add Customer".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Fill in customer details</strong><p>Name, phone, email, address, and optional price level assignment.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Customer appears in the Sales Order form's customer dropdown.</p></div></div>
            </div>
        </div>

        {{-- Units --}}
        <div class="docs-section" id="units">
            <div class="docs-section-title"><i class="fa-solid fa-ruler"></i> Units of Measure</div>
            <div class="docs-section-subtitle">Define units such as pcs, box, kg, liter. Required when adding products.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.units') }}">Units</a></strong><p>Click "Add Unit".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Enter a unit name and abbreviation</strong><p>Example: "Pieces" with abbreviation "pcs".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Unit is now selectable on the product form.</p></div></div>
            </div>
            <div class="docs-warn"><i class="fa-solid fa-triangle-exclamation"></i> Create all needed units before onboarding products to avoid inconsistencies later.</div>
        </div>

        {{-- Barcodes --}}
        <div class="docs-section" id="barcodes">
            <div class="docs-section-title"><i class="fa-solid fa-barcode"></i> Barcodes</div>
            <div class="docs-section-subtitle">Assign one or multiple barcodes to a product for scanner-based lookup.</div>
            <div class="docs-req">
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Products must exist first</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.barcodes') }}">Barcodes</a></strong><p>Click "Add Barcode".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select a product and enter the barcode value</strong><p>Supports EAN-13, QR, Code 128, and custom formats.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>The barcode is now linked to the product for scanning workflows.</p></div></div>
            </div>
        </div>

        {{-- Batches --}}
        <div class="docs-section" id="batches">
            <div class="docs-section-title"><i class="fa-solid fa-layer-group"></i> Batch / Lot Numbers</div>
            <div class="docs-section-subtitle">Track products by batch for expiry management and traceability.</div>
            <div class="docs-req">
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Products must exist first</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.batches') }}">Batches</a></strong><p>Click "Add Batch".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select a product, enter lot number, manufacture date, and expiry date</strong><p>All dates are stored and used on the Expired Products report.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Batch is linked and visible in the Expired Products view for proactive management.</p></div></div>
            </div>
        </div>

        {{-- Expired --}}
        <div class="docs-section" id="expired">
            <div class="docs-section-title"><i class="fa-solid fa-triangle-exclamation"></i> Expired Products</div>
            <div class="docs-section-subtitle">Monitor products with batches approaching or past their expiry date.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.expired') }}">Expired Products</a></strong><p>The list automatically flags products based on expiry date from their batch records.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Review status badges</strong><p><em>Expired</em> = already past date. <em>Expiring Soon</em> = within 30 days. <em>OK</em> = safe.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Take action</strong><p>Return to the supplier or record a stock adjustment to write off expired goods.</p></div></div>
            </div>
        </div>

        {{-- Bin Locations --}}
        <div class="docs-section" id="bin-locations">
            <div class="docs-section-title"><i class="fa-solid fa-location-dot"></i> Bin Locations</div>
            <div class="docs-section-subtitle">Define physical storage slots within a warehouse (aisle, rack, shelf, bin).</div>
            <div class="docs-req">
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Warehouses must be created first</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('master-data.bin-locations') }}">Bin Locations</a></strong><p>Click "Add Location".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select a warehouse and enter location code</strong><p>Example: A-01-R3-B2 (Aisle A, Zone 01, Rack 3, Bin 2).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Location is usable when recording stock transfers and adjustments.</p></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
