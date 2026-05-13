@extends('layouts.app', ['title' => 'Purchasing — Docs', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Documentation › Purchasing</p>
            <h2><i class="fa-solid fa-cart-shopping"></i> Purchasing</h2>
            <p>Manage the full procurement cycle: from requesting goods to receiving them and paying your suppliers.</p>
        </div>

        <div class="docs-req" style="margin-bottom:20px">
            <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: purchasing.manage</span>
            <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Suppliers and products must be set up first</span>
        </div>

        {{-- Recommended flow --}}
        <div class="docs-section" id="flow">
            <div class="docs-section-title"><i class="fa-solid fa-diagram-project"></i> Recommended Purchase Flow</div>
            <div class="docs-section-subtitle">Follow this sequence for a complete procurement cycle.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Purchase Request (PR)</strong><p>A team member requests goods. PR is reviewed and approved internally.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Purchase Order (PO)</strong><p>An approved PR is converted to a PO and sent to the supplier.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>PO Approval</strong><p>PO is approved by an authorised manager before it becomes binding.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Goods Receive Note (GRN)</strong><p>When goods arrive, a GRN records what was received. Stock is added to the warehouse.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">5</div><div class="docs-step-body"><strong>Supplier Debt</strong><p>Record payment obligations and mark them paid as invoices are settled.</p></div></div>
            </div>
        </div>

        {{-- PR --}}
        <div class="docs-section" id="pr">
            <div class="docs-section-title"><i class="fa-solid fa-file-pen"></i> Purchase Request (PR)</div>
            <div class="docs-section-subtitle">Internal request to purchase items before issuing a formal order to a supplier.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('purchasing.pr') }}">Purchasing → Purchase Request</a></strong><p>Click "New PR".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select products and quantities</strong><p>Add a reason and urgency note if needed. The PR is submitted in <em>pending</em> status.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Update status</strong><p>Manager reviews and sets status to <em>approved</em> or <em>rejected</em>. Approved PRs can generate a PO.</p></div></div>
            </div>
        </div>

        {{-- PO --}}
        <div class="docs-section" id="po">
            <div class="docs-section-title"><i class="fa-solid fa-file-invoice"></i> Purchase Order (PO)</div>
            <div class="docs-section-subtitle">Formal document sent to a supplier committing to purchase goods at agreed prices.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('purchasing.po') }}">Purchase Order</a></strong><p>Click "New PO".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select supplier, products, quantities, and unit prices</strong><p>Optionally reference the source PR number.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>PO is created in <em>draft</em> status. Send to PO Approvals for sign-off.</p></div></div>
            </div>
        </div>

        {{-- PO Approvals --}}
        <div class="docs-section" id="po-approvals">
            <div class="docs-section-title"><i class="fa-solid fa-circle-check"></i> PO Approval</div>
            <div class="docs-section-subtitle">Authorise or reject purchase orders before they are sent to suppliers.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('purchasing.po-approvals') }}">PO Approvals</a></strong><p>Pending POs awaiting approval are listed here.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Review the PO details</strong><p>Expand the row to see all line items, total value, and the requesting user.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Click Approve or Reject</strong><p>Approved POs move to <em>approved</em> status and can be used for GRN. Rejected POs are returned to draft.</p></div></div>
            </div>
        </div>

        {{-- GRN --}}
        <div class="docs-section" id="grn">
            <div class="docs-section-title"><i class="fa-solid fa-truck-ramp-box"></i> Goods Receive Note (GRN)</div>
            <div class="docs-section-subtitle">Record goods arriving from a supplier and add them to warehouse stock.</div>
            <div class="docs-req">
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> An approved PO must exist before creating a GRN</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('purchasing.grn') }}">Goods Receive Note</a></strong><p>Click "New GRN".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select the PO and target warehouse</strong><p>PO line items are pre-filled. Adjust received quantities if partial delivery.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Product stock in the selected warehouse is increased by the received quantities. PO status updates to <em>received</em> or <em>partial</em>.</p></div></div>
            </div>
        </div>

        {{-- Returns --}}
        <div class="docs-section" id="purchase-returns">
            <div class="docs-section-title"><i class="fa-solid fa-rotate-left"></i> Purchase Returns</div>
            <div class="docs-section-subtitle">Return defective or incorrect goods back to a supplier and deduct from stock.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('purchasing.returns') }}">Purchase Returns</a></strong><p>Click "New Return".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Select supplier, product, and quantity to return</strong><p>State the return reason (damaged, wrong item, over-delivery).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Stock is deducted from the warehouse and the return is logged against the supplier.</p></div></div>
            </div>
        </div>

        {{-- Supplier Debts --}}
        <div class="docs-section" id="supplier-debts">
            <div class="docs-section-title"><i class="fa-solid fa-hand-holding-dollar"></i> Supplier Debts</div>
            <div class="docs-section-subtitle">Track amounts owed to suppliers and record payments.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('purchasing.supplier-debts') }}">Supplier Debts</a></strong><p>Unpaid supplier invoices are listed with due dates.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Add a new debt record</strong><p>Select supplier, enter invoice number, amount, due date, and payment terms.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Record payment</strong><p>Edit the record and mark it as <em>paid</em> with the payment date.</p></div></div>
            </div>
        </div>

        {{-- Supplier Performance --}}
        <div class="docs-section" id="supplier-performance">
            <div class="docs-section-title"><i class="fa-solid fa-chart-bar"></i> Supplier Performance</div>
            <div class="docs-section-subtitle">View on-time delivery rate, return rate, and total purchase value per supplier.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('purchasing.supplier-performance') }}">Supplier Performance</a></strong><p>Performance metrics are automatically calculated from GRN and return records.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Review KPIs per supplier</strong><p>On-time %, return %, total spend, and average lead time days.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> Use this to negotiate better terms with high-performing suppliers and review underperforming ones.</div>
        </div>
    </div>
</div>
@endsection
