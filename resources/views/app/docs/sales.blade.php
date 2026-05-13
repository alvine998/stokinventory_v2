@extends('layouts.app', ['title' => 'Sales & Distribution — Docs', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Documentation › Sales & Distribution</p>
            <h2><i class="fa-solid fa-bag-shopping"></i> Sales & Distribution</h2>
            <p>Manage the complete sales cycle: orders, delivery, invoicing, returns, and shipment tracking.</p>
        </div>

        <div class="docs-req" style="margin-bottom:20px">
            <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: sales.manage</span>
            <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Customers, products, and stores must be set up first</span>
        </div>

        {{-- Flow --}}
        <div class="docs-section" id="flow">
            <div class="docs-section-title"><i class="fa-solid fa-diagram-project"></i> Recommended Sales Flow</div>
            <div class="docs-section-subtitle">Follow this sequence for a complete sales cycle.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Sales Order (SO)</strong><p>Customer places an order. Stock is reserved.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Delivery Order (DO)</strong><p>Goods are packed and dispatched. Stock is deducted.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Sales Invoice</strong><p>Customer is billed. Payment status is tracked.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Shipment Tracking</strong><p>Real-time tracking updates are added against the delivery order.</p></div></div>
            </div>
        </div>

        {{-- SO --}}
        <div class="docs-section" id="sales-orders">
            <div class="docs-section-title"><i class="fa-solid fa-file-contract"></i> Sales Orders</div>
            <div class="docs-section-subtitle">Create and manage customer orders.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('sales.orders') }}">Sales → Sales Orders</a></strong><p>Click "New Sales Order".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select customer, store, and products</strong><p>Enter quantity and selling price per line. Apply a discount code or price level if applicable.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Set expected delivery date</strong><p>This is used to calculate on-time delivery in customer analytics.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Save</strong><p>SO is created with status <em>pending</em>. Update to <em>confirmed</em> when approved, or <em>cancelled</em> to void it.</p></div></div>
            </div>
        </div>

        {{-- DO --}}
        <div class="docs-section" id="delivery-orders">
            <div class="docs-section-title"><i class="fa-solid fa-truck"></i> Delivery Orders</div>
            <div class="docs-section-subtitle">Dispatch goods to the customer. Stock is deducted from the warehouse upon creation.</div>
            <div class="docs-req">
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> A confirmed Sales Order must exist</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('sales.delivery-orders') }}">Delivery Orders</a></strong><p>Click "New Delivery Order".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select the sales order and source warehouse</strong><p>Items from the SO are pre-filled. Adjust quantities for partial delivery.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Assign courier / expedition</strong><p>Select from your registered couriers. Optionally enter a waybill number.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Save</strong><p>Stock is deducted. DO status starts as <em>pending</em>. Update to <em>shipped</em> → <em>delivered</em> as it progresses.</p></div></div>
            </div>
        </div>

        {{-- Invoices --}}
        <div class="docs-section" id="invoices">
            <div class="docs-section-title"><i class="fa-solid fa-file-invoice-dollar"></i> Sales Invoices</div>
            <div class="docs-section-subtitle">Bill your customers and track payment status.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('sales.invoices') }}">Sales Invoices</a></strong><p>Click "New Invoice".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select the sales order</strong><p>Invoice amount is auto-calculated from SO line items. Set the due date and payment terms.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Invoice is issued with status <em>unpaid</em>. Edit it to mark as <em>paid</em> or <em>partial</em> when payment is received.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> Unpaid invoices past their due date contribute to the Customer Outstanding report.</div>
        </div>

        {{-- Returns --}}
        <div class="docs-section" id="sales-returns">
            <div class="docs-section-title"><i class="fa-solid fa-rotate-left"></i> Sales Returns</div>
            <div class="docs-section-subtitle">Process returned goods from customers and add them back to stock.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('sales.returns') }}">Sales Returns</a></strong><p>Click "New Return".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select the original sales order and product</strong><p>Enter return quantity and reason.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Stock is added back to the warehouse. A credit note should be issued to the customer.</p></div></div>
            </div>
        </div>

        {{-- Shipment Tracking --}}
        <div class="docs-section" id="shipment-tracking">
            <div class="docs-section-title"><i class="fa-solid fa-location-dot"></i> Shipment Tracking</div>
            <div class="docs-section-subtitle">Log tracking events for each delivery order so customers can be informed of progress.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('sales.shipment-tracking') }}">Shipment Tracking</a></strong><p>Find the delivery order you want to update.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Add a tracking event</strong><p>Enter the event (e.g. "Departed from hub"), location, and timestamp.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Update DO status</strong><p>When the final "Delivered" event is added, update the delivery order status to <em>delivered</em>.</p></div></div>
            </div>
        </div>

        {{-- Expeditions --}}
        <div class="docs-section" id="expeditions">
            <div class="docs-section-title"><i class="fa-solid fa-plane-departure"></i> Couriers / Expeditions</div>
            <div class="docs-section-subtitle">Register the courier companies your business uses for deliveries.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('sales.expeditions') }}">Expeditions</a></strong><p>Click "Add Courier".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Enter courier name, code, and contact</strong><p>Example: JNE, TIKI, SiCepat, J&T.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Courier is selectable on Delivery Orders.</p></div></div>
            </div>
        </div>

        {{-- Price Levels --}}
        <div class="docs-section" id="price-levels">
            <div class="docs-section-title"><i class="fa-solid fa-tags"></i> Price Levels</div>
            <div class="docs-section-subtitle">Define customer tiers with different pricing (e.g. Retail, Wholesale, VIP).</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('sales.price-levels') }}">Price Levels</a></strong><p>Click "Add Price Level".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Name the level and set a discount percentage or fixed price rule</strong><p>Example: "Wholesale" = 15% discount off the standard price.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Assign customers to the level</strong><p>Done from the Customer record. On a new SO, the customer's level is applied automatically.</p></div></div>
            </div>
        </div>

        {{-- Customer Outstanding --}}
        <div class="docs-section" id="customer-outstanding">
            <div class="docs-section-title"><i class="fa-solid fa-circle-exclamation"></i> Customer Outstanding</div>
            <div class="docs-section-subtitle">View all unpaid or overdue invoices per customer in one place.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('sales.customer-outstanding') }}">Customer Outstanding</a></strong><p>All customers with outstanding balances are shown.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Review aging</strong><p>Each invoice shows how many days overdue it is and the outstanding amount.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Follow up</strong><p>Use customer contact details to chase payment. Mark invoices as paid in Sales Invoices once collected.</p></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
