@extends('layouts.app', ['title' => 'Reporting & Analytics — Docs', 'heading' => 'Documentation'])

@section('content')
<div class="docs-layout">
    @include('app.docs._sidebar')

    <div class="docs-content">
        <div class="docs-hero">
            <p class="eyebrow">Documentation › Reporting & Analytics</p>
            <h2><i class="fa-solid fa-chart-bar"></i> Reporting & Analytics</h2>
            <p>Nine data-driven reports to understand your inventory performance, demand, and financials. Every report can be exported to Excel or PDF.</p>
        </div>

        <div class="docs-req" style="margin-bottom:20px">
            <span class="docs-req-pill"><i class="fa-solid fa-key"></i> Permission: reporting.view</span>
            <span class="docs-req-pill"><i class="fa-solid fa-file-excel"></i> Export to Excel & PDF available on all reports</span>
        </div>

        {{-- How to export --}}
        <div class="docs-section" id="export">
            <div class="docs-section-title"><i class="fa-solid fa-download"></i> Exporting Reports</div>
            <div class="docs-section-subtitle">Every report page has Excel and PDF export buttons in the top-right of the panel.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Apply your filters first</strong><p>Select date range, category, or other filters and click Apply. The export will use the same filters.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Click "Excel" or "PDF"</strong><p>The file downloads immediately to your browser.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Excel</strong><p>Auto-sized columns, bold headers, and formatted as <code>.xlsx</code>. Compatible with Microsoft Excel, Google Sheets, LibreOffice.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">4</div><div class="docs-step-body"><strong>PDF</strong><p>A4 landscape format with company branding. Suitable for printing or attaching to reports.</p></div></div>
            </div>
        </div>

        {{-- KPI --}}
        <div class="docs-section" id="kpi">
            <div class="docs-section-title"><i class="fa-solid fa-gauge-high"></i> KPI Dashboard</div>
            <div class="docs-section-subtitle">Monthly overview: revenue, orders, average order value, gross profit, and inventory value.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.kpi') }}">Reporting → KPI</a></strong><p>Select year and month, then click Apply.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Set KPI targets</strong><p>Click "Set Target" to define revenue and order targets for the month. Actual vs. target is shown with colour coding.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Top 5 products by revenue</strong><p>The bottom section ranks your best-selling products for the selected period.</p></div></div>
            </div>
        </div>

        {{-- Stock Report --}}
        <div class="docs-section" id="stock">
            <div class="docs-section-title"><i class="fa-solid fa-warehouse"></i> Stock Report</div>
            <div class="docs-section-subtitle">Current stock levels, values, and status for all products.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.stock') }}">Stock Report</a></strong><p>Filter by category, status (normal / low / out / over), or search by name.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Status badges</strong><p><em>Normal</em> = healthy stock. <em>Low</em> = at or below minimum. <em>Out</em> = zero stock. <em>Over</em> = more than 2× minimum.</p></div></div>
            </div>
        </div>

        {{-- Movement --}}
        <div class="docs-section" id="movement">
            <div class="docs-section-title"><i class="fa-solid fa-bolt"></i> Fast / Slow Moving Report</div>
            <div class="docs-section-subtitle">Identify which products move quickly and which are stagnant within a date range.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.movement') }}">Fast / Slow Moving</a></strong><p>Set start date, end date, and sort order (by qty out or movement count).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Interpret the movement level</strong><p><em>Fast</em> (≥50 units out), <em>Medium</em> (10–49), <em>Slow</em> (1–9), <em>No Movement</em> (0).</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> Use this to prioritise replenishment for fast movers and consider promotions for slow movers.</div>
        </div>

        {{-- Dead Stock --}}
        <div class="docs-section" id="dead-stock">
            <div class="docs-section-title"><i class="fa-solid fa-skull"></i> Dead Stock Report</div>
            <div class="docs-section-subtitle">Products with no outbound movement for a configurable number of days.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.dead-stock') }}">Dead Stock</a></strong><p>Select the inactivity threshold: 30, 60, 90, or 180 days.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Review locked capital</strong><p>The total value of all dead stock is shown as "Locked Capital" — money tied up in unsellable goods.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Take action</strong><p>Consider liquidation, promotions, or supplier return for these items.</p></div></div>
            </div>
        </div>

        {{-- Aging --}}
        <div class="docs-section" id="aging">
            <div class="docs-section-title"><i class="fa-solid fa-hourglass-half"></i> Stock Aging</div>
            <div class="docs-section-subtitle">How long current stock has been sitting based on the last inbound date.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.aging') }}">Stock Aging</a></strong><p>Products are automatically bucketed by age.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Aging buckets</strong><p><em>Fresh</em> (&lt;30 days), <em>Aging</em> (30–60 days), <em>Aging</em> (60–90 days), <em>Aged</em> (&gt;90 days).</p></div></div>
            </div>
        </div>

        {{-- Margin --}}
        <div class="docs-section" id="margin">
            <div class="docs-section-title"><i class="fa-solid fa-tags"></i> Margin Report</div>
            <div class="docs-section-subtitle">Gross margin per product: selling price minus cost price.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.margin') }}">Margin Report</a></strong><p>Filter by category or sort by margin % to find your most and least profitable products.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Review margin badges</strong><p>Green (≥30%), Blue (10–29%), Red (&lt;10%) — quickly spot underperforming products.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Average margin</strong><p>The top bar shows the weighted average margin across all filtered products.</p></div></div>
            </div>
            <div class="docs-warn"><i class="fa-solid fa-triangle-exclamation"></i> Products without a cost price set will show 100% margin. Always set cost prices on your product records.</div>
        </div>

        {{-- PvS --}}
        <div class="docs-section" id="purchase-vs-sales">
            <div class="docs-section-title"><i class="fa-solid fa-arrows-left-right"></i> Purchase vs. Sales</div>
            <div class="docs-section-subtitle">Compare monthly purchase spend vs. sales revenue over the last 12 months.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.purchase-vs-sales') }}">Purchase vs. Sales</a></strong><p>A bar chart shows both trends side by side.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Read the table</strong><p>Each month shows sales, purchases, and the net difference. A positive difference means sales exceeded procurement spend.</p></div></div>
            </div>
        </div>

        {{-- Forecast --}}
        <div class="docs-section" id="forecast">
            <div class="docs-section-title"><i class="fa-solid fa-wand-magic-sparkles"></i> Demand Forecast</div>
            <div class="docs-section-subtitle">Predict how much stock you'll need based on historical average monthly outbound movement.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.forecast') }}">Demand Forecast</a></strong><p>Select "History Months" (how far back to look) and "Forecast Months" (how far ahead to predict).</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Click Apply</strong><p>The system calculates: Average Monthly Out × Forecast Months = Forecasted Need.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">3</div><div class="docs-step-body"><strong>Surplus / Deficit column</strong><p>Current Stock − Forecasted Need. Negative = restock needed. Reorder quantity is shown.</p></div></div>
            </div>
            <div class="docs-tip"><i class="fa-solid fa-lightbulb"></i> Products flagged as "Needs Restock" are highlighted in red. Create a Purchase Request directly from this view.</div>
        </div>

        {{-- Inventory Value --}}
        <div class="docs-section" id="inventory-value">
            <div class="docs-section-title"><i class="fa-solid fa-coins"></i> Inventory Value</div>
            <div class="docs-section-subtitle">Total stock value grouped by category — the balance sheet inventory asset figure.</div>
            <div class="docs-steps">
                <div class="docs-step"><div class="docs-step-num">1</div><div class="docs-step-body"><strong>Go to <a href="{{ route('reporting.inventory-value') }}">Inventory Value</a></strong><p>Filter by category or view all. Grand total is shown at the top.</p></div></div>
                <div class="docs-step"><div class="docs-step-num">2</div><div class="docs-step-body"><strong>Drill into categories</strong><p>Each category shows its subtotal and lists all products with stock × cost price.</p></div></div>
            </div>
        </div>
    </div>
</div>
@endsection
