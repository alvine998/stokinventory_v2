<?php

namespace App\Http\Controllers\App;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\KpiTarget;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportingController extends Controller
{
    private function bid(): int
    {
        return Auth::user()->business_id;
    }

    // ─── Dashboard KPI ─────────────────────────────────────────────────────

    public function kpi(Request $request)
    {
        $bid   = $this->bid();
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $revenue = SalesInvoice::where('business_id', $bid)
            ->whereBetween('issued_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->sum('amount');

        $totalOrders = SalesOrder::where('business_id', $bid)
            ->whereBetween('ordered_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->count();

        $avgOrderValue = $totalOrders > 0 ? round($revenue / $totalOrders, 2) : 0;

        $cogs = StockMovement::where('stock_movements.business_id', $bid)
            ->where('stock_movements.type', 'out')
            ->whereBetween('stock_movements.moved_at', [$start, $end])
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * products.cost_price'));

        $grossProfit    = $revenue - $cogs;
        $inventoryValue = Product::where('business_id', $bid)
            ->sum(DB::raw('current_stock * cost_price'));

        $topByRevenue = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.so_id')
            ->where('sales_orders.business_id', $bid)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.ordered_at', [$start, $end])
            ->select('sales_order_items.product_name', DB::raw('SUM(sales_order_items.subtotal) as total_revenue'))
            ->groupBy('sales_order_items.product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        $topByQty = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.so_id')
            ->where('sales_orders.business_id', $bid)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.ordered_at', [$start, $end])
            ->select('sales_order_items.product_name', DB::raw('SUM(sales_order_items.quantity) as total_qty'))
            ->groupBy('sales_order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Revenue trend last 6 months
        $trendLabels  = [];
        $trendRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $trendLabels[]  = $m->format('M Y');
            $trendRevenue[] = (float) SalesInvoice::where('business_id', $bid)
                ->whereBetween('issued_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                ->where('status', '!=', 'cancelled')
                ->sum('amount');
        }

        $targets = KpiTarget::where('business_id', $bid)
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->keyBy('metric');

        return view('app.reporting.kpi', compact(
            'revenue', 'totalOrders', 'avgOrderValue', 'cogs', 'grossProfit',
            'inventoryValue', 'topByRevenue', 'topByQty',
            'trendLabels', 'trendRevenue', 'targets', 'year', 'month'
        ));
    }

    public function storeKpiTarget(Request $request)
    {
        $data = $request->validate([
            'metric'       => ['required', 'in:revenue,orders,gross_profit,inventory_value'],
            'target_value' => ['required', 'numeric', 'min:0'],
            'year'         => ['required', 'integer', 'min:2020', 'max:2100'],
            'month'        => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        KpiTarget::updateOrCreate(
            [
                'business_id' => $this->bid(),
                'metric'      => $data['metric'],
                'year'        => $data['year'],
                'month'       => $data['month'],
            ],
            ['target_value' => $data['target_value']]
        );

        return back()->with('status', __('messages.saved'));
    }

    // ─── Stock Report ──────────────────────────────────────────────────────

    public function stockReport(Request $request)
    {
        $bid      = $this->bid();
        $category = $request->input('category');
        $status   = $request->input('status', 'all');
        $search   = $request->input('search');

        $query = Product::where('business_id', $bid)->orderBy('name');

        if ($category) {
            $query->where('category', $category);
        }
        if ($search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }

        if ($status === 'out') {
            $query->where('current_stock', '<=', 0);
        } elseif ($status === 'low') {
            $query->whereRaw('current_stock > 0 AND current_stock <= minimum_stock AND minimum_stock > 0');
        } elseif ($status === 'over') {
            $query->whereRaw('minimum_stock > 0 AND current_stock > minimum_stock * 2');
        } elseif ($status === 'normal') {
            $query->whereRaw('current_stock > minimum_stock OR minimum_stock = 0');
        }

        $products   = $query->paginate(30)->withQueryString();
        $categories = Product::where('business_id', $bid)->distinct()->pluck('category')->filter()->sort()->values();

        $summary = [
            'total'  => Product::where('business_id', $bid)->count(),
            'out'    => Product::where('business_id', $bid)->where('current_stock', '<=', 0)->count(),
            'low'    => Product::where('business_id', $bid)->whereRaw('current_stock > 0 AND current_stock <= minimum_stock AND minimum_stock > 0')->count(),
            'over'   => Product::where('business_id', $bid)->whereRaw('minimum_stock > 0 AND current_stock > minimum_stock * 2')->count(),
        ];

        return view('app.reporting.stock', compact('products', 'categories', 'summary', 'category', 'status', 'search'));
    }

    // ─── Fast / Slow Moving ────────────────────────────────────────────────

    public function movement(Request $request)
    {
        $bid       = $this->bid();
        $startDate = $request->input('start_date', now()->subDays(29)->toDateString());
        $endDate   = $request->input('end_date',   now()->toDateString());
        $sort      = $request->input('sort', 'desc');
        $limit     = (int) $request->input('limit', 20);

        $products = DB::table('products')
            ->leftJoin('stock_movements', function ($join) use ($bid, $startDate, $endDate) {
                $join->on('stock_movements.product_id', '=', 'products.id')
                     ->where('stock_movements.type', '=', 'out')
                     ->where('stock_movements.business_id', '=', $bid)
                     ->whereBetween('stock_movements.moved_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
            })
            ->where('products.business_id', $bid)
            ->select(
                'products.id', 'products.name', 'products.sku', 'products.unit', 'products.current_stock',
                DB::raw('COALESCE(SUM(stock_movements.quantity), 0) as total_out'),
                DB::raw('COUNT(stock_movements.id) as movement_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.unit', 'products.current_stock')
            ->orderBy('total_out', $sort)
            ->limit($limit)
            ->get();

        return view('app.reporting.movement', compact('products', 'startDate', 'endDate', 'sort', 'limit'));
    }

    // ─── Dead Stock ────────────────────────────────────────────────────────

    public function deadStock(Request $request)
    {
        $bid    = $this->bid();
        $days   = (int) $request->input('days', 90);
        $cutoff = now()->subDays($days)->toDateTimeString();

        $products = DB::table('products')
            ->leftJoin('stock_movements', function ($join) use ($bid, $cutoff) {
                $join->on('stock_movements.product_id', '=', 'products.id')
                     ->where('stock_movements.business_id', '=', $bid)
                     ->where('stock_movements.type', '=', 'out')
                     ->where('stock_movements.moved_at', '>=', $cutoff);
            })
            ->where('products.business_id', $bid)
            ->where('products.current_stock', '>', 0)
            ->whereNull('stock_movements.id')
            ->select(
                'products.id', 'products.name', 'products.sku', 'products.unit',
                'products.category', 'products.current_stock', 'products.cost_price',
                DB::raw('products.current_stock * products.cost_price as stock_value')
            )
            ->orderByDesc('stock_value')
            ->get();

        $totalValue = $products->sum('stock_value');

        return view('app.reporting.dead-stock', compact('products', 'days', 'totalValue'));
    }

    // ─── Stock Aging ───────────────────────────────────────────────────────

    public function stockAging()
    {
        $bid = $this->bid();

        $lastIn = StockMovement::where('business_id', $bid)
            ->where('type', 'in')
            ->select('product_id', DB::raw('MAX(moved_at) as last_in_at'))
            ->groupBy('product_id')
            ->pluck('last_in_at', 'product_id');

        $products = Product::where('business_id', $bid)
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($p) use ($lastIn) {
                $lastInAt = $lastIn[$p->id] ?? null;
                $days     = $lastInAt ? now()->diffInDays(Carbon::parse($lastInAt)) : null;
                $bucket   = match (true) {
                    $days === null => 'unknown',
                    $days < 30    => 'fresh',
                    $days < 60    => 'aging_30',
                    $days < 90    => 'aging_60',
                    default       => 'aged',
                };
                return ['product' => $p, 'last_in_at' => $lastInAt, 'age_days' => $days, 'bucket' => $bucket];
            });

        $bucketCounts = $products->groupBy('bucket')->map->count();

        return view('app.reporting.aging', compact('products', 'bucketCounts'));
    }

    // ─── Margin Report ─────────────────────────────────────────────────────

    public function marginReport(Request $request)
    {
        $bid      = $this->bid();
        $category = $request->input('category');
        $sort     = $request->input('sort', 'desc');

        $query = Product::where('business_id', $bid);
        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('name')->get()->map(function ($p) {
            $margin    = (float) $p->price - (float) $p->cost_price;
            $marginPct = $p->price > 0 ? round($margin / $p->price * 100, 1) : 0;
            return ['product' => $p, 'price' => (float) $p->price, 'cost_price' => (float) $p->cost_price, 'margin' => $margin, 'margin_pct' => $marginPct];
        });

        $products   = $sort === 'asc' ? $products->sortBy('margin_pct') : $products->sortByDesc('margin_pct');
        $categories = Product::where('business_id', $bid)->distinct()->pluck('category')->filter()->sort()->values();
        $avgMargin  = $products->count() > 0 ? round($products->avg('margin_pct'), 1) : 0;

        return view('app.reporting.margin', compact('products', 'categories', 'category', 'sort', 'avgMargin'));
    }

    // ─── Pembelian vs Penjualan ────────────────────────────────────────────

    public function purchaseVsSales()
    {
        $bid = $this->bid();

        $periods = [];
        for ($i = 11; $i >= 0; $i--) {
            $periods[] = now()->subMonths($i)->format('Y-m');
        }

        $salesRaw = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.so_id')
            ->where('sales_orders.business_id', $bid)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->where('sales_orders.ordered_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(sales_orders.ordered_at, '%Y-%m') as period, SUM(sales_order_items.subtotal) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        $purchasesRaw = DB::table('grn_items')
            ->join('goods_receive_notes', 'goods_receive_notes.id', '=', 'grn_items.grn_id')
            ->where('goods_receive_notes.business_id', $bid)
            ->where('goods_receive_notes.received_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(goods_receive_notes.received_at, '%Y-%m') as period, SUM(grn_items.quantity * grn_items.unit_price) as total")
            ->groupBy('period')
            ->pluck('total', 'period');

        return view('app.reporting.purchase-vs-sales', compact('periods', 'salesRaw', 'purchasesRaw'));
    }

    // ─── Forecast Kebutuhan Barang ─────────────────────────────────────────

    public function forecast(Request $request)
    {
        $bid            = $this->bid();
        $monthsBack     = (int) $request->input('months_back',     3);
        $forecastMonths = (int) $request->input('forecast_months', 3);
        $since          = now()->subMonths($monthsBack)->startOfMonth();

        $outPerProduct = StockMovement::where('business_id', $bid)
            ->where('type', 'out')
            ->where('moved_at', '>=', $since)
            ->select('product_id', DB::raw('SUM(quantity) as total_out'))
            ->groupBy('product_id')
            ->pluck('total_out', 'product_id');

        $products = Product::where('business_id', $bid)->orderBy('name')->get()->map(function ($p) use ($outPerProduct, $monthsBack, $forecastMonths) {
            $totalOut    = (float) ($outPerProduct[$p->id] ?? 0);
            $avgMonthly  = round($totalOut / $monthsBack, 2);
            $forecasted  = round($avgMonthly * $forecastMonths, 2);
            $surplus     = (float) $p->current_stock - $forecasted;
            $reorderQty  = max(0, (float) $p->minimum_stock + $forecasted - (float) $p->current_stock);

            return [
                'product'         => $p,
                'current_stock'   => (float) $p->current_stock,
                'minimum_stock'   => (float) $p->minimum_stock,
                'avg_monthly'     => $avgMonthly,
                'forecasted_need' => $forecasted,
                'surplus'         => $surplus,
                'reorder_qty'     => round($reorderQty, 2),
                'needs_reorder'   => $surplus < 0,
            ];
        })->sortByDesc('needs_reorder')->values();

        return view('app.reporting.forecast', compact('products', 'monthsBack', 'forecastMonths'));
    }

    // ─── Nilai Inventory ───────────────────────────────────────────────────

    public function inventoryValue(Request $request)
    {
        $bid      = $this->bid();
        $category = $request->input('category');

        $query = Product::where('business_id', $bid);
        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('category')->orderBy('name')->get();

        $grouped = $products->groupBy('category')->map(function ($group, $cat) {
            return [
                'category'    => $cat ?: '(Uncategorized)',
                'products'    => $group,
                'total_units' => $group->sum(fn($p) => (float) $p->current_stock),
                'total_value' => $group->sum(fn($p) => (float) $p->current_stock * (float) $p->cost_price),
            ];
        })->sortByDesc('total_value');

        $grandTotal = $grouped->sum('total_value');
        $categories = Product::where('business_id', $bid)->distinct()->pluck('category')->filter()->sort()->values();

        return view('app.reporting.inventory-value', compact('grouped', 'grandTotal', 'categories', 'category'));
    }

    // ─── Export Helpers ────────────────────────────────────────────────────

    private function respondExport(string $format, string $filename, array $headings, array $rows, string $pdfView, array $pdfData)
    {
        if ($format === 'pdf') {
            $pdf = Pdf::loadView($pdfView, $pdfData)->setPaper('a4', 'landscape');
            return $pdf->download($filename . '.pdf');
        }

        return Excel::download(new ReportExport($headings, $rows, $filename), $filename . '.xlsx');
    }

    // ─── KPI Export ────────────────────────────────────────────────────────

    public function kpiExport(Request $request)
    {
        $bid   = $this->bid();
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);
        $format = $request->input('format', 'excel');

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $revenue        = SalesInvoice::where('business_id', $bid)->whereBetween('issued_at', [$start, $end])->where('status', '!=', 'cancelled')->sum('amount');
        $totalOrders    = SalesOrder::where('business_id', $bid)->whereBetween('ordered_at', [$start, $end])->where('status', '!=', 'cancelled')->count();
        $avgOrderValue  = $totalOrders > 0 ? round($revenue / $totalOrders, 2) : 0;
        $cogs           = StockMovement::where('stock_movements.business_id', $bid)->where('stock_movements.type', 'out')->whereBetween('stock_movements.moved_at', [$start, $end])->join('products', 'stock_movements.product_id', '=', 'products.id')->sum(DB::raw('stock_movements.quantity * products.cost_price'));
        $grossProfit    = $revenue - $cogs;
        $inventoryValue = Product::where('business_id', $bid)->sum(DB::raw('current_stock * cost_price'));

        $topByRevenue = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.so_id')
            ->where('sales_orders.business_id', $bid)->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.ordered_at', [$start, $end])
            ->select('sales_order_items.product_name', DB::raw('SUM(sales_order_items.subtotal) as total_revenue'))
            ->groupBy('sales_order_items.product_name')->orderByDesc('total_revenue')->limit(5)->get();

        $headings = ['Metric', 'Value'];
        $rows = [
            ['Revenue', number_format($revenue, 2)],
            ['Total Orders', $totalOrders],
            ['Avg Order Value', number_format($avgOrderValue, 2)],
            ['COGS', number_format($cogs, 2)],
            ['Gross Profit', number_format($grossProfit, 2)],
            ['Inventory Value', number_format($inventoryValue, 2)],
            ['', ''],
            ['Top Products by Revenue', ''],
        ];
        foreach ($topByRevenue as $row) {
            $rows[] = [$row->product_name, number_format($row->total_revenue, 2)];
        }

        $label = date('M Y', mktime(0, 0, 0, $month, 1, $year));
        $filename = 'KPI-Dashboard-' . $label;

        return $this->respondExport($format, $filename, $headings, $rows, 'app.reporting.exports.kpi', [
            'revenue' => $revenue, 'totalOrders' => $totalOrders, 'avgOrderValue' => $avgOrderValue,
            'cogs' => $cogs, 'grossProfit' => $grossProfit, 'inventoryValue' => $inventoryValue,
            'topByRevenue' => $topByRevenue, 'label' => $label,
        ]);
    }

    // ─── Stock Report Export ───────────────────────────────────────────────

    public function stockExport(Request $request)
    {
        $bid      = $this->bid();
        $category = $request->input('category');
        $status   = $request->input('status', 'all');
        $search   = $request->input('search');
        $format   = $request->input('format', 'excel');

        $query = Product::where('business_id', $bid)->orderBy('name');
        if ($category) $query->where('category', $category);
        if ($search)   $query->where(fn($q) => $q->where('name', 'like', "%$search%")->orWhere('sku', 'like', "%$search%"));
        if ($status === 'out')    $query->where('current_stock', '<=', 0);
        elseif ($status === 'low')  $query->whereRaw('current_stock > 0 AND current_stock <= minimum_stock AND minimum_stock > 0');
        elseif ($status === 'over') $query->whereRaw('minimum_stock > 0 AND current_stock > minimum_stock * 2');
        elseif ($status === 'normal') $query->whereRaw('current_stock > minimum_stock OR minimum_stock = 0');

        $products = $query->get();
        $headings = ['Product', 'SKU', 'Category', 'Current Stock', 'Min. Stock', 'Stock Value (Rp)', 'Status'];
        $rows = $products->map(fn($p) => [
            $p->name, $p->sku, $p->category,
            $p->current_stock, $p->minimum_stock,
            number_format((float)$p->current_stock * (float)$p->cost_price, 2),
            $p->current_stock <= 0 ? 'Out of Stock' : ($p->current_stock <= $p->minimum_stock && $p->minimum_stock > 0 ? 'Low Stock' : ($p->minimum_stock > 0 && $p->current_stock > $p->minimum_stock * 2 ? 'Over Stock' : 'Normal')),
        ])->toArray();

        return $this->respondExport($format, 'Stock-Report-' . now()->format('Y-m-d'), $headings, $rows, 'app.reporting.exports.stock', compact('products'));
    }

    // ─── Movement Export ───────────────────────────────────────────────────

    public function movementExport(Request $request)
    {
        $bid       = $this->bid();
        $startDate = $request->input('start_date', now()->subDays(29)->toDateString());
        $endDate   = $request->input('end_date',   now()->toDateString());
        $sort      = $request->input('sort', 'desc');
        $limit     = (int) $request->input('limit', 50);
        $format    = $request->input('format', 'excel');

        $products = DB::table('products')
            ->leftJoin('stock_movements', function ($join) use ($bid, $startDate, $endDate) {
                $join->on('stock_movements.product_id', '=', 'products.id')
                     ->where('stock_movements.type', '=', 'out')
                     ->where('stock_movements.business_id', '=', $bid)
                     ->whereBetween('stock_movements.moved_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
            })
            ->where('products.business_id', $bid)
            ->select('products.name', 'products.sku', 'products.current_stock',
                DB::raw('COALESCE(SUM(stock_movements.quantity), 0) as total_out'),
                DB::raw('COUNT(stock_movements.id) as movement_count'))
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.current_stock')
            ->orderBy('total_out', $sort)->limit($limit)->get();

        $headings = ['#', 'Product', 'SKU', 'Current Stock', 'Qty Out (Period)', 'Movements', 'Level'];
        $rows = $products->map(fn($p, $i) => [
            $i + 1, $p->name, $p->sku, $p->current_stock, $p->total_out, $p->movement_count,
            $p->total_out >= 50 ? 'Fast Moving' : ($p->total_out >= 10 ? 'Medium Moving' : ($p->total_out > 0 ? 'Slow Moving' : 'No Movement')),
        ])->toArray();

        return $this->respondExport($format, 'Movement-Report-' . $startDate . '-' . $endDate, $headings, $rows, 'app.reporting.exports.movement', compact('products', 'startDate', 'endDate'));
    }

    // ─── Dead Stock Export ─────────────────────────────────────────────────

    public function deadStockExport(Request $request)
    {
        $bid    = $this->bid();
        $days   = (int) $request->input('days', 90);
        $format = $request->input('format', 'excel');
        $cutoff = now()->subDays($days)->toDateTimeString();

        $products = DB::table('products')
            ->leftJoin('stock_movements', function ($join) use ($bid, $cutoff) {
                $join->on('stock_movements.product_id', '=', 'products.id')
                     ->where('stock_movements.business_id', '=', $bid)
                     ->where('stock_movements.type', '=', 'out')
                     ->where('stock_movements.moved_at', '>=', $cutoff);
            })
            ->where('products.business_id', $bid)->where('products.current_stock', '>', 0)
            ->whereNull('stock_movements.id')
            ->select('products.name', 'products.sku', 'products.category', 'products.current_stock',
                'products.cost_price', DB::raw('products.current_stock * products.cost_price as stock_value'))
            ->orderByDesc('stock_value')->get();

        $totalValue = $products->sum('stock_value');
        $headings   = ['Product', 'SKU', 'Category', 'Current Stock', 'Cost Price (Rp)', 'Stock Value (Rp)'];
        $rows = $products->map(fn($p) => [
            $p->name, $p->sku, $p->category, $p->current_stock,
            number_format((float)$p->cost_price, 2),
            number_format((float)$p->stock_value, 2),
        ])->toArray();

        return $this->respondExport($format, 'Dead-Stock-' . $days . 'days-' . now()->format('Y-m-d'), $headings, $rows, 'app.reporting.exports.dead-stock', compact('products', 'days', 'totalValue'));
    }

    // ─── Aging Export ──────────────────────────────────────────────────────

    public function agingExport(Request $request)
    {
        $bid    = $this->bid();
        $format = $request->input('format', 'excel');

        $lastIn = StockMovement::where('business_id', $bid)->where('type', 'in')
            ->select('product_id', DB::raw('MAX(moved_at) as last_in_at'))
            ->groupBy('product_id')->pluck('last_in_at', 'product_id');

        $products = Product::where('business_id', $bid)->where('current_stock', '>', 0)->orderBy('name')->get()
            ->map(function ($p) use ($lastIn) {
                $lastInAt = $lastIn[$p->id] ?? null;
                $days     = $lastInAt ? now()->diffInDays(Carbon::parse($lastInAt)) : null;
                $bucket   = match (true) {
                    $days === null => 'Unknown',
                    $days < 30    => 'Fresh (<30d)',
                    $days < 60    => 'Aging (30-60d)',
                    $days < 90    => 'Aging (60-90d)',
                    default       => 'Aged (>90d)',
                };
                return ['product' => $p, 'last_in_at' => $lastInAt, 'age_days' => $days ?? '—', 'bucket' => $bucket];
            });

        $headings = ['Product', 'SKU', 'Current Stock', 'Last Stock In', 'Age (days)', 'Bucket'];
        $rows = $products->map(fn($r) => [
            $r['product']->name, $r['product']->sku, $r['product']->current_stock,
            $r['last_in_at'] ? Carbon::parse($r['last_in_at'])->format('d M Y') : '—',
            $r['age_days'], $r['bucket'],
        ])->toArray();

        return $this->respondExport($format, 'Stock-Aging-' . now()->format('Y-m-d'), $headings, $rows, 'app.reporting.exports.aging', compact('products'));
    }

    // ─── Margin Export ─────────────────────────────────────────────────────

    public function marginExport(Request $request)
    {
        $bid      = $this->bid();
        $category = $request->input('category');
        $sort     = $request->input('sort', 'desc');
        $format   = $request->input('format', 'excel');

        $query = Product::where('business_id', $bid);
        if ($category) $query->where('category', $category);

        $products = $query->orderBy('name')->get()->map(function ($p) {
            $margin    = (float)$p->price - (float)$p->cost_price;
            $marginPct = $p->price > 0 ? round($margin / $p->price * 100, 1) : 0;
            return ['product' => $p, 'price' => (float)$p->price, 'cost_price' => (float)$p->cost_price, 'margin' => $margin, 'margin_pct' => $marginPct];
        });
        $products  = $sort === 'asc' ? $products->sortBy('margin_pct') : $products->sortByDesc('margin_pct');
        $avgMargin = $products->count() > 0 ? round($products->avg('margin_pct'), 1) : 0;

        $headings = ['Product', 'SKU', 'Category', 'Selling Price (Rp)', 'Cost Price (Rp)', 'Margin (Rp)', 'Margin %'];
        $rows = $products->map(fn($r) => [
            $r['product']->name, $r['product']->sku, $r['product']->category,
            number_format($r['price'], 2), number_format($r['cost_price'], 2),
            number_format($r['margin'], 2), $r['margin_pct'] . '%',
        ])->toArray();

        return $this->respondExport($format, 'Margin-Report-' . now()->format('Y-m-d'), $headings, $rows, 'app.reporting.exports.margin', compact('products', 'avgMargin'));
    }

    // ─── Purchase vs Sales Export ──────────────────────────────────────────

    public function purchaseVsSalesExport(Request $request)
    {
        $bid    = $this->bid();
        $format = $request->input('format', 'excel');

        $periods = [];
        for ($i = 11; $i >= 0; $i--) {
            $periods[] = now()->subMonths($i)->format('Y-m');
        }

        $salesRaw = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.so_id')
            ->where('sales_orders.business_id', $bid)->where('sales_orders.status', '!=', 'cancelled')
            ->where('sales_orders.ordered_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(sales_orders.ordered_at, '%Y-%m') as period, SUM(sales_order_items.subtotal) as total")
            ->groupBy('period')->pluck('total', 'period');

        $purchasesRaw = DB::table('grn_items')
            ->join('goods_receive_notes', 'goods_receive_notes.id', '=', 'grn_items.grn_id')
            ->where('goods_receive_notes.business_id', $bid)
            ->where('goods_receive_notes.received_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(goods_receive_notes.received_at, '%Y-%m') as period, SUM(grn_items.quantity * grn_items.unit_price) as total")
            ->groupBy('period')->pluck('total', 'period');

        $headings = ['Period', 'Sales (Rp)', 'Purchases (Rp)', 'Difference (Rp)'];
        $rows = array_map(function ($p) use ($salesRaw, $purchasesRaw) {
            $s = (float)($salesRaw[$p] ?? 0);
            $b = (float)($purchasesRaw[$p] ?? 0);
            return [Carbon::parse($p . '-01')->format('M Y'), number_format($s, 2), number_format($b, 2), number_format($s - $b, 2)];
        }, $periods);

        return $this->respondExport($format, 'Purchase-vs-Sales-' . now()->format('Y-m-d'), $headings, $rows, 'app.reporting.exports.purchase-vs-sales', compact('periods', 'salesRaw', 'purchasesRaw'));
    }

    // ─── Forecast Export ───────────────────────────────────────────────────

    public function forecastExport(Request $request)
    {
        $bid            = $this->bid();
        $monthsBack     = (int) $request->input('months_back', 3);
        $forecastMonths = (int) $request->input('forecast_months', 3);
        $format         = $request->input('format', 'excel');
        $since          = now()->subMonths($monthsBack)->startOfMonth();

        $outPerProduct = StockMovement::where('business_id', $bid)->where('type', 'out')
            ->where('moved_at', '>=', $since)
            ->select('product_id', DB::raw('SUM(quantity) as total_out'))
            ->groupBy('product_id')->pluck('total_out', 'product_id');

        $products = Product::where('business_id', $bid)->orderBy('name')->get()->map(function ($p) use ($outPerProduct, $monthsBack, $forecastMonths) {
            $totalOut   = (float)($outPerProduct[$p->id] ?? 0);
            $avg        = round($totalOut / $monthsBack, 2);
            $forecasted = round($avg * $forecastMonths, 2);
            $surplus    = (float)$p->current_stock - $forecasted;
            return [
                'product' => $p, 'current_stock' => (float)$p->current_stock,
                'avg_monthly' => $avg, 'forecasted_need' => $forecasted,
                'surplus' => $surplus, 'reorder_qty' => max(0, round((float)$p->minimum_stock + $forecasted - (float)$p->current_stock, 2)),
                'needs_reorder' => $surplus < 0,
            ];
        })->sortByDesc('needs_reorder')->values();

        $headings = ['Product', 'SKU', 'Current Stock', 'Avg Monthly Out', 'Forecasted Need', 'Surplus/Deficit', 'Reorder Qty'];
        $rows = $products->map(fn($r) => [
            $r['product']->name, $r['product']->sku, $r['current_stock'],
            $r['avg_monthly'], $r['forecasted_need'], $r['surplus'], $r['reorder_qty'],
        ])->toArray();

        return $this->respondExport($format, 'Forecast-' . $monthsBack . 'mo-' . now()->format('Y-m-d'), $headings, $rows, 'app.reporting.exports.forecast', compact('products', 'monthsBack', 'forecastMonths'));
    }

    // ─── Inventory Value Export ────────────────────────────────────────────

    public function inventoryValueExport(Request $request)
    {
        $bid      = $this->bid();
        $category = $request->input('category');
        $format   = $request->input('format', 'excel');

        $query = Product::where('business_id', $bid);
        if ($category) $query->where('category', $category);
        $products = $query->orderBy('category')->orderBy('name')->get();

        $grouped    = $products->groupBy('category')->map(fn($g, $c) => ['category' => $c ?: '(Uncategorized)', 'products' => $g, 'total_units' => $g->sum(fn($p) => (float)$p->current_stock), 'total_value' => $g->sum(fn($p) => (float)$p->current_stock * (float)$p->cost_price)])->sortByDesc('total_value');
        $grandTotal = $grouped->sum('total_value');

        $headings = ['Category', 'Product', 'SKU', 'Current Stock', 'Cost Price (Rp)', 'Value (Rp)'];
        $rows = [];
        foreach ($grouped as $group) {
            foreach ($group['products'] as $p) {
                $rows[] = [$group['category'], $p->name, $p->sku, (float)$p->current_stock, number_format((float)$p->cost_price, 2), number_format((float)$p->current_stock * (float)$p->cost_price, 2)];
            }
            $rows[] = ['', '-- Subtotal: ' . $group['category'] . ' --', '', number_format($group['total_units'], 2), '', number_format($group['total_value'], 2)];
        }

        return $this->respondExport($format, 'Inventory-Value-' . now()->format('Y-m-d'), $headings, $rows, 'app.reporting.exports.inventory-value', compact('grouped', 'grandTotal'));
    }
}
