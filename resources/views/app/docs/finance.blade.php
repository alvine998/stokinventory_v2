@extends('layouts.app', ['title' => 'Finance & Accounting — Docs', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Documentation › Finance & Accounting</p>
            <h2><i class="fa-solid fa-landmark"></i> Finance & Accounting</h2>
            <p>Automate cost calculations, maintain your chart of accounts, track cashflow, and generate financial reports.</p>
        </div>

        <div class="docs-req" style="margin-bottom:20px">
            <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: finance.manage</span>
            <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Set up Chart of Accounts before recording journal entries</span>
        </div>

        {{-- HPP --}}
        <div class="docs-section" id="hpp">
            <div class="docs-section-title"><i class="fa-solid fa-calculator"></i> HPP / Cost of Goods Sold (COGS)</div>
            <div class="docs-section-subtitle">Automatically calculate the cost of goods sold based on your inventory movements and costing method.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('finance.hpp') }}">Finance → HPP / COGS</a></strong><p>The page shows the calculated COGS for all products based on outbound movements.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Recalculate</strong><p>Click "Recalculate HPP" to refresh COGS figures using the latest stock movements and costing methods.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Override cost per product</strong><p>Expand a product row and edit its cost price if the calculated value needs manual adjustment.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> COGS is used in the Profit & Loss report. Ensure costing methods are set correctly in Inventory Operations first.</div>
        </div>

        {{-- Chart of Accounts --}}
        <div class="docs-section" id="accounts">
            <div class="docs-section-title"><i class="fa-solid fa-sitemap"></i> Chart of Accounts</div>
            <div class="docs-section-subtitle">Define your accounting structure: assets, liabilities, equity, income, and expenses.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('finance.accounts') }}">Chart of Accounts</a></strong><p>Default accounts are pre-seeded. Click "Add Account" to add custom ones.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Fill in account code, name, and type</strong><p>Types: <em>Asset, Liability, Equity, Revenue, Expense</em>. Use a consistent numbering like 1xxx = Assets.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>Account is now available in the Journal Entry form.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>To deactivate</strong><p>Edit the account and set it to inactive. Inactive accounts cannot receive new entries but are preserved for history.</p></div></div>
            </div>
        </div>

        {{-- Journals --}}
        <div class="docs-section" id="journals">
            <div class="docs-section-title"><i class="fa-solid fa-book-open"></i> Journal Entries</div>
            <div class="docs-section-subtitle">Record double-entry accounting transactions manually or review auto-generated entries.</div>
            <div class="docs-req">
                <span class="docs-req-pill warn"><i class="fa-solid fa-triangle-exclamation"></i> Chart of Accounts must be set up first</span>
            </div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('finance.journals') }}">Journal Entries</a></strong><p>Auto-generated entries from purchases and sales are listed here.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Click "New Journal Entry"</strong><p>Enter the date, description, and at least two lines (debit and credit). Total debits must equal total credits.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>The entry is posted and reflected in financial reports.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Delete</strong><p>Only unposted or draft journals can be deleted. Posted entries should be reversed with a contra entry.</p></div></div>
            </div>
        </div>

        {{-- Integration --}}
        <div class="docs-section" id="integration">
            <div class="docs-section-title"><i class="fa-solid fa-plug"></i> Accounting Integration</div>
            <div class="docs-section-subtitle">Connect StokInventory with external accounting software (e.g. Accurate, Jurnal.id, Xero).</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('finance.integration') }}">Accounting Integration</a></strong><p>Select your accounting platform from the dropdown.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Enter your API key or OAuth credentials</strong><p>Refer to your accounting platform's API documentation for where to find these.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Map your accounts</strong><p>Link StokInventory accounts to the corresponding accounts in your external platform.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Save and test</strong><p>Click "Test Connection". A success message confirms the link is working.</p></div></div>
            </div>
        </div>

        {{-- Cashflow --}}
        <div class="docs-section" id="cashflow">
            <div class="docs-section-title"><i class="fa-solid fa-water"></i> Cashflow Report</div>
            <div class="docs-section-subtitle">Track cash inflows (sales receipts) and outflows (supplier payments) over time.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('finance.cashflow') }}">Cashflow</a></strong><p>Select the period (month/quarter/year).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Review inflows and outflows</strong><p>Operating, investing, and financing activities are shown separately.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Net cash position</strong><p>The bottom line shows opening balance + net cash flow = closing balance for the period.</p></div></div>
            </div>
        </div>

        {{-- Valuation --}}
        <div class="docs-section" id="valuation">
            <div class="docs-section-title"><i class="fa-solid fa-scale-balanced"></i> Inventory Valuation</div>
            <div class="docs-section-subtitle">The total monetary value of all stock on hand — a key balance sheet figure.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('finance.valuation') }}">Inventory Valuation</a></strong><p>Current stock quantities × cost price per product are summed.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Filter by warehouse or category</strong><p>Drill down to specific segments of your inventory.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> Compare this report month-over-month to identify inventory build-up or drawdown trends.</div>
        </div>

        {{-- P&L --}}
        <div class="docs-section" id="profit-loss">
            <div class="docs-section-title"><i class="fa-solid fa-chart-line"></i> Profit & Loss</div>
            <div class="docs-section-subtitle">Revenue minus COGS and operating expenses equals net profit for any period.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('finance.profit-loss') }}">Profit & Loss</a></strong><p>Select a date range (month, quarter, or custom).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Read the report</strong><p>Revenue → Gross Profit (after COGS) → Operating Profit (after expenses) → Net Profit (after tax).</p></div></div>
            </div>
        </div>

        {{-- Tax --}}
        <div class="docs-section" id="tax">
            <div class="docs-section-title"><i class="fa-solid fa-percent"></i> Tax Configuration (PPN/VAT)</div>
            <div class="docs-section-subtitle">Set up tax rates applied to sales invoices and purchase orders.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('finance.tax') }}">Tax</a></strong><p>Click "Add Tax".</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Enter tax name and rate</strong><p>Example: PPN = 11%. Mark as default if it should be auto-applied to new transactions.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Save</strong><p>The tax rate appears as an option on sales invoices and purchase orders.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>Toggle active/inactive</strong><p>Inactive taxes are hidden from transaction forms but preserved in historical records.</p></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
