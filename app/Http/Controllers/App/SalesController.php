<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderItem;
use App\Models\Expedition;
use App\Models\PriceLevel;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\ShipmentTracking;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    private function bid(): int
    {
        return Auth::user()->business_id;
    }

    private function nextNo(string $model, string $prefix): string
    {
        $count = $model::where('business_id', $this->bid())->count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function validItems(array $items, $products): array
    {
        return collect($items)
            ->filter(fn($i) => !empty($i['product_id']) && (float) ($i['quantity'] ?? 0) > 0)
            ->map(function ($i) use ($products) {
                $product = $products[$i['product_id']] ?? null;
                return [
                    'product_id'       => $i['product_id'],
                    'product_name'     => $product?->name ?? ($i['product_name'] ?? ''),
                    'quantity'         => (float) $i['quantity'],
                    'unit_price'       => (float) ($i['unit_price'] ?? 0),
                    'discount_percent' => (float) ($i['discount_percent'] ?? 0),
                ];
            })
            ->values()
            ->toArray();
    }

    // ─── Sales Orders ────────────────────────────────────────────────────

    public function salesOrders()
    {
        $orders      = SalesOrder::where('business_id', $this->bid())
            ->with(['customer', 'warehouse', 'priceLevel', 'items'])
            ->latest('ordered_at')
            ->paginate(30);
        $customers   = Customer::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $warehouses  = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();
        $products    = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $priceLevels = PriceLevel::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();

        return view('app.sales.orders', compact('orders', 'customers', 'warehouses', 'products', 'priceLevels'));
    }

    public function storeSalesOrder(Request $request)
    {
        $data = $request->validate([
            'customer_id'            => ['nullable', 'exists:customers,id'],
            'warehouse_id'           => ['nullable', 'exists:warehouses,id'],
            'price_level_id'         => ['nullable', 'exists:price_levels,id'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
            'ordered_at'             => ['nullable', 'date'],
            'items'                  => ['required', 'array'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'     => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $products   = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        $validItems = $this->validItems($data['items'], $products);

        if (empty($validItems)) {
            return back()->withErrors(['items' => 'At least one item is required.']);
        }

        DB::transaction(function () use ($data, $validItems) {
            $so = SalesOrder::create([
                'business_id'    => $this->bid(),
                'so_no'          => $this->nextNo(SalesOrder::class, 'SO-'),
                'customer_id'    => $data['customer_id'] ?? null,
                'warehouse_id'   => $data['warehouse_id'] ?? null,
                'price_level_id' => $data['price_level_id'] ?? null,
                'created_by'     => Auth::id(),
                'status'         => 'confirmed',
                'notes'          => $data['notes'] ?? null,
                'ordered_at'     => $data['ordered_at'] ?? now(),
            ]);

            foreach ($validItems as $item) {
                $discount = $item['discount_percent'];
                $subtotal = $item['quantity'] * $item['unit_price'] * (1 - $discount / 100);
                $so->items()->create([
                    'product_id'       => $item['product_id'],
                    'product_name'     => $item['product_name'],
                    'quantity'         => $item['quantity'],
                    'unit_price'       => $item['unit_price'],
                    'discount_percent' => $discount,
                    'subtotal'         => round($subtotal, 2),
                ]);
            }

            // Auto-create sales invoice
            $total = SalesOrderItem::where('so_id', $so->id)->sum('subtotal');
            if ($total > 0) {
                SalesInvoice::create([
                    'business_id' => $this->bid(),
                    'invoice_no'  => $this->nextNo(SalesInvoice::class, 'SINV-'),
                    'so_id'       => $so->id,
                    'customer_id' => $data['customer_id'] ?? null,
                    'status'      => 'unpaid',
                    'amount'      => $total,
                    'paid_amount' => 0,
                    'issued_at'   => $data['ordered_at'] ?? now(),
                ]);
            }
        });

        return back()->with('status', __('messages.saved'));
    }

    public function updateSoStatus(Request $request, SalesOrder $salesOrder)
    {
        abort_if($salesOrder->business_id !== $this->bid(), 403);
        $data = $request->validate([
            'status' => ['required', 'in:draft,confirmed,processing,partially_delivered,delivered,cancelled'],
        ]);
        $salesOrder->update($data);
        return back()->with('status', __('messages.saved'));
    }

    public function destroySo(SalesOrder $salesOrder)
    {
        abort_if($salesOrder->business_id !== $this->bid(), 403);
        abort_if(in_array($salesOrder->status, ['processing', 'partially_delivered', 'delivered'], true), 422, 'Cannot delete an in-progress order.');
        $salesOrder->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ─── Delivery Orders ─────────────────────────────────────────────────

    public function deliveryOrders()
    {
        $dos        = DeliveryOrder::where('business_id', $this->bid())
            ->with(['salesOrder', 'customer', 'expedition', 'warehouse', 'items'])
            ->latest('shipped_at')
            ->paginate(30);
        $customers  = Customer::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();
        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $expeditions = Expedition::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $sos        = SalesOrder::where('business_id', $this->bid())
            ->whereIn('status', ['confirmed', 'processing', 'partially_delivered'])
            ->orderBy('so_no')
            ->get();

        return view('app.sales.delivery-orders', compact('dos', 'customers', 'warehouses', 'products', 'expeditions', 'sos'));
    }

    public function storeDo(Request $request)
    {
        $data = $request->validate([
            'so_id'              => ['nullable', 'exists:sales_orders,id'],
            'customer_id'        => ['nullable', 'exists:customers,id'],
            'warehouse_id'       => ['nullable', 'exists:warehouses,id'],
            'expedition_id'      => ['nullable', 'exists:expeditions,id'],
            'tracking_no'        => ['nullable', 'string', 'max:100'],
            'shipping_address'   => ['nullable', 'string', 'max:500'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'shipped_at'         => ['nullable', 'date'],
            'estimated_delivery_at' => ['nullable', 'date'],
            'items'              => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
        ]);

        $products   = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        $validItems = collect($data['items'])
            ->filter(fn($i) => !empty($i['product_id']) && (float)($i['quantity'] ?? 0) > 0)
            ->map(fn($i) => [
                'product_id'   => $i['product_id'],
                'product_name' => $products[$i['product_id']]?->name ?? '',
                'quantity'     => (float) $i['quantity'],
            ])->values()->toArray();

        if (empty($validItems)) {
            return back()->withErrors(['items' => 'At least one item is required.']);
        }

        DB::transaction(function () use ($data, $validItems) {
            $do = DeliveryOrder::create([
                'business_id'          => $this->bid(),
                'do_no'                => $this->nextNo(DeliveryOrder::class, 'DO-'),
                'so_id'                => $data['so_id'] ?? null,
                'customer_id'          => $data['customer_id'] ?? null,
                'warehouse_id'         => $data['warehouse_id'] ?? null,
                'expedition_id'        => $data['expedition_id'] ?? null,
                'tracking_no'          => $data['tracking_no'] ?? null,
                'shipping_address'     => $data['shipping_address'] ?? null,
                'status'               => 'shipped',
                'notes'                => $data['notes'] ?? null,
                'shipped_at'           => $data['shipped_at'] ?? now(),
                'estimated_delivery_at' => $data['estimated_delivery_at'] ?? null,
            ]);

            foreach ($validItems as $item) {
                $do->items()->create($item);

                // Decrement stock on delivery
                Product::where('id', $item['product_id'])->decrement('current_stock', (int) $item['quantity']);
                StockMovement::create([
                    'business_id'  => $this->bid(),
                    'product_id'   => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'] ?? null,
                    'type'         => 'out',
                    'quantity'     => (int) $item['quantity'],
                    'reference_no' => $do->do_no,
                    'notes'        => '[DO] ' . ($data['notes'] ?? ''),
                    'moved_at'     => $data['shipped_at'] ?? now(),
                ]);
            }

            // Add initial tracking event
            ShipmentTracking::create([
                'do_id'      => $do->id,
                'status'     => 'shipped',
                'location'   => null,
                'description' => 'Shipment created — ' . ($do->tracking_no ?? 'no tracking number'),
                'tracked_at' => $data['shipped_at'] ?? now(),
            ]);

            // Update SO status
            if (!empty($data['so_id'])) {
                $so = SalesOrder::find($data['so_id']);
                if ($so && $so->status === 'confirmed') {
                    $so->update(['status' => 'processing']);
                }
            }
        });

        return back()->with('status', __('messages.saved'));
    }

    public function updateDoStatus(Request $request, DeliveryOrder $deliveryOrder)
    {
        abort_if($deliveryOrder->business_id !== $this->bid(), 403);
        $data = $request->validate([
            'status'      => ['required', 'in:draft,shipped,in_transit,delivered,failed,returned'],
            'location'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'tracked_at'  => ['nullable', 'date'],
        ]);

        $deliveryOrder->update([
            'status'       => $data['status'],
            'delivered_at' => $data['status'] === 'delivered' ? ($data['tracked_at'] ?? now()) : $deliveryOrder->delivered_at,
        ]);

        ShipmentTracking::create([
            'do_id'       => $deliveryOrder->id,
            'status'      => $data['status'],
            'location'    => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'tracked_at'  => $data['tracked_at'] ?? now(),
        ]);

        // Update SO to delivered if all DOs are delivered
        if ($data['status'] === 'delivered' && $deliveryOrder->so_id) {
            $so = SalesOrder::with('deliveryOrders')->find($deliveryOrder->so_id);
            if ($so && $so->deliveryOrders->every(fn($d) => $d->status === 'delivered')) {
                $so->update(['status' => 'delivered']);
            } elseif ($so) {
                $so->update(['status' => 'partially_delivered']);
            }
        }

        return back()->with('status', __('messages.saved'));
    }

    // ─── Sales Invoices ──────────────────────────────────────────────────

    public function salesInvoices()
    {
        $invoices  = SalesInvoice::where('business_id', $this->bid())
            ->with(['salesOrder', 'customer'])
            ->latest('issued_at')
            ->paginate(30);
        $customers = Customer::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $sos       = SalesOrder::where('business_id', $this->bid())->orderBy('so_no')->get();

        $summary = [
            'total'      => SalesInvoice::where('business_id', $this->bid())->sum('amount'),
            'paid'       => SalesInvoice::where('business_id', $this->bid())->sum('paid_amount'),
            'overdue'    => SalesInvoice::where('business_id', $this->bid())
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->count(),
        ];

        return view('app.sales.invoices', compact('invoices', 'customers', 'sos', 'summary'));
    }

    public function storeSalesInvoice(Request $request)
    {
        $data = $request->validate([
            'so_id'       => ['nullable', 'exists:sales_orders,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'due_at'      => ['nullable', 'date'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $paid   = (float) ($data['paid_amount'] ?? 0);
        $amount = (float) $data['amount'];
        $status = match(true) {
            $paid >= $amount => 'paid',
            $paid > 0        => 'partial',
            default          => 'unpaid',
        };

        SalesInvoice::create([
            'business_id' => $this->bid(),
            'invoice_no'  => $this->nextNo(SalesInvoice::class, 'SINV-'),
            'so_id'       => $data['so_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'status'      => $status,
            'amount'      => $amount,
            'paid_amount' => $paid,
            'due_at'      => $data['due_at'] ?? null,
            'issued_at'   => now(),
            'notes'       => $data['notes'] ?? null,
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateSalesInvoice(Request $request, SalesInvoice $salesInvoice)
    {
        abort_if($salesInvoice->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $paid   = (float) $data['paid_amount'];
        $amount = $salesInvoice->amount;
        $status = match(true) {
            $paid >= $amount => 'paid',
            $paid > 0        => 'partial',
            default          => 'unpaid',
        };

        $salesInvoice->update([
            'paid_amount' => $paid,
            'status'      => $status,
            'notes'       => $data['notes'] ?? $salesInvoice->notes,
        ]);

        return back()->with('status', __('messages.saved'));
    }

    // ─── Sales Returns ───────────────────────────────────────────────────

    public function salesReturns()
    {
        $returns    = SalesReturn::where('business_id', $this->bid())
            ->with(['customer', 'salesOrder', 'returnedBy', 'items'])
            ->latest('returned_at')
            ->paginate(30);
        $customers  = Customer::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();
        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $sos        = SalesOrder::where('business_id', $this->bid())->orderBy('so_no')->get();
        $dos        = DeliveryOrder::where('business_id', $this->bid())->orderBy('do_no')->get();

        return view('app.sales.returns', compact('returns', 'customers', 'warehouses', 'products', 'sos', 'dos'));
    }

    public function storeSalesReturn(Request $request)
    {
        $data = $request->validate([
            'so_id'              => ['nullable', 'exists:sales_orders,id'],
            'do_id'              => ['nullable', 'exists:delivery_orders,id'],
            'customer_id'        => ['nullable', 'exists:customers,id'],
            'warehouse_id'       => ['nullable', 'exists:warehouses,id'],
            'reason'             => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'returned_at'        => ['nullable', 'date'],
            'items'              => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $products   = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        $validItems = $this->validItems($data['items'], $products);

        if (empty($validItems)) {
            return back()->withErrors(['items' => 'At least one item is required.']);
        }

        DB::transaction(function () use ($data, $validItems) {
            $ret = SalesReturn::create([
                'business_id'  => $this->bid(),
                'return_no'    => $this->nextNo(SalesReturn::class, 'SRET-'),
                'so_id'        => $data['so_id'] ?? null,
                'do_id'        => $data['do_id'] ?? null,
                'customer_id'  => $data['customer_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'returned_by'  => Auth::id(),
                'reason'       => $data['reason'] ?? null,
                'status'       => 'completed',
                'notes'        => $data['notes'] ?? null,
                'returned_at'  => $data['returned_at'] ?? now(),
            ]);

            foreach ($validItems as $item) {
                $ret->items()->create([
                    'product_id'   => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                ]);

                // Return stock IN
                Product::where('id', $item['product_id'])->increment('current_stock', (int) $item['quantity']);
                StockMovement::create([
                    'business_id'  => $this->bid(),
                    'product_id'   => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'] ?? null,
                    'type'         => 'in',
                    'quantity'     => (int) $item['quantity'],
                    'reference_no' => $ret->return_no,
                    'notes'        => '[Sales Return] ' . ($data['reason'] ?? ''),
                    'moved_at'     => $data['returned_at'] ?? now(),
                ]);
            }
        });

        return back()->with('status', __('messages.saved'));
    }

    // ─── Shipment Tracking ───────────────────────────────────────────────

    public function shipmentTracking()
    {
        $dos = DeliveryOrder::where('business_id', $this->bid())
            ->with(['customer', 'expedition', 'salesOrder', 'trackings'])
            ->latest('shipped_at')
            ->paginate(30);

        return view('app.sales.shipment-tracking', compact('dos'));
    }

    public function storeTracking(Request $request, DeliveryOrder $deliveryOrder)
    {
        abort_if($deliveryOrder->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'status'      => ['required', 'string', 'max:100'],
            'location'    => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'tracked_at'  => ['nullable', 'date'],
        ]);

        ShipmentTracking::create([
            'do_id'       => $deliveryOrder->id,
            'status'      => $data['status'],
            'location'    => $data['location'] ?? null,
            'description' => $data['description'] ?? null,
            'tracked_at'  => $data['tracked_at'] ?? now(),
        ]);

        $deliveryOrder->update(['status' => $data['status']]);

        return back()->with('status', __('messages.saved'));
    }

    // ─── Expeditions ─────────────────────────────────────────────────────

    public function expeditions()
    {
        $expeditions = Expedition::where('business_id', $this->bid())->orderBy('name')->paginate(30);
        return view('app.sales.expeditions', compact('expeditions'));
    }

    public function storeExpedition(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'code'                  => ['nullable', 'string', 'max:30'],
            'tracking_url_template' => ['nullable', 'url', 'max:500'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        Expedition::create([
            'business_id'           => $this->bid(),
            'name'                  => $data['name'],
            'code'                  => $data['code'] ?? null,
            'tracking_url_template' => $data['tracking_url_template'] ?? null,
            'is_active'             => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateExpedition(Request $request, Expedition $expedition)
    {
        abort_if($expedition->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'code'                  => ['nullable', 'string', 'max:30'],
            'tracking_url_template' => ['nullable', 'url', 'max:500'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        $expedition->update([
            'name'                  => $data['name'],
            'code'                  => $data['code'] ?? null,
            'tracking_url_template' => $data['tracking_url_template'] ?? null,
            'is_active'             => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyExpedition(Expedition $expedition)
    {
        abort_if($expedition->business_id !== $this->bid(), 403);
        $expedition->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ─── Price Levels ────────────────────────────────────────────────────

    public function priceLevels()
    {
        $priceLevels = PriceLevel::where('business_id', $this->bid())->orderBy('name')->paginate(30);
        return view('app.sales.price-levels', compact('priceLevels'));
    }

    public function storePriceLevel(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:255'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_default'       => ['nullable', 'boolean'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        if (!empty($data['is_default'])) {
            PriceLevel::where('business_id', $this->bid())->update(['is_default' => false]);
        }

        PriceLevel::create([
            'business_id'      => $this->bid(),
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'discount_percent' => (float) ($data['discount_percent'] ?? 0),
            'is_default'       => (bool) ($data['is_default'] ?? false),
            'is_active'        => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updatePriceLevel(Request $request, PriceLevel $priceLevel)
    {
        abort_if($priceLevel->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:255'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_default'       => ['nullable', 'boolean'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        if (!empty($data['is_default'])) {
            PriceLevel::where('business_id', $this->bid())->where('id', '!=', $priceLevel->id)->update(['is_default' => false]);
        }

        $priceLevel->update([
            'name'             => $data['name'],
            'description'      => $data['description'] ?? null,
            'discount_percent' => (float) ($data['discount_percent'] ?? 0),
            'is_default'       => (bool) ($data['is_default'] ?? false),
            'is_active'        => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyPriceLevel(PriceLevel $priceLevel)
    {
        abort_if($priceLevel->business_id !== $this->bid(), 403);
        $priceLevel->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ─── Customer Outstanding ────────────────────────────────────────────

    public function customerOutstanding()
    {
        $bid = $this->bid();

        $customers = Customer::where('business_id', $bid)->where('is_active', true)->orderBy('name')->get();

        $stats = $customers->map(function ($c) use ($bid) {
            $invoices = SalesInvoice::where('business_id', $bid)->where('customer_id', $c->id);

            $totalAmount = (clone $invoices)->sum('amount');
            $totalPaid   = (clone $invoices)->sum('paid_amount');
            $outstanding = max(0, $totalAmount - $totalPaid);
            $overdueCount = (clone $invoices)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->count();
            $totalOrders  = SalesOrder::where('business_id', $bid)->where('customer_id', $c->id)->count();
            $totalReturns = SalesReturn::where('business_id', $bid)->where('customer_id', $c->id)->count();

            return [
                'customer'      => $c,
                'total_invoiced' => $totalAmount,
                'total_paid'    => $totalPaid,
                'outstanding'   => $outstanding,
                'overdue_count' => $overdueCount,
                'total_orders'  => $totalOrders,
                'total_returns' => $totalReturns,
            ];
        })->filter(fn($r) => $r['total_invoiced'] > 0 || $r['total_orders'] > 0);

        return view('app.sales.customer-outstanding', compact('stats'));
    }
}
