<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryOpsController extends Controller
{
    private function bid(): int
    {
        return Auth::user()->business_id;
    }

    // ─── Stock Adjustment ───────────────────────────────────────────────

    public function adjustments()
    {
        $adjustments = StockAdjustment::where('business_id', $this->bid())
            ->with(['product', 'warehouse', 'adjustedBy'])
            ->latest('adjusted_at')
            ->paginate(30);

        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();

        return view('app.inventory-ops.adjustments', compact('adjustments', 'products', 'warehouses'));
    }

    public function storeAdjustment(Request $request)
    {
        $data = $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type'         => ['required', 'in:add,remove'],
            'quantity'     => ['required', 'integer', 'min:1'],
            'reason'       => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'adjusted_at'  => ['nullable', 'date'],
        ]);

        $data['business_id']  = $this->bid();
        $data['adjusted_by']  = Auth::id();
        $data['adjusted_at']  = $data['adjusted_at'] ?? now();

        DB::transaction(function () use ($data) {
            StockAdjustment::create($data);

            $delta = $data['type'] === 'add' ? $data['quantity'] : -$data['quantity'];
            Product::where('id', $data['product_id'])->increment('current_stock', $delta);

            // Mirror into stock_movements for unified history
            StockMovement::create([
                'business_id'  => $data['business_id'],
                'product_id'   => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type'         => $data['type'] === 'add' ? 'in' : 'out',
                'quantity'     => $data['quantity'],
                'reference_no' => $data['reference_no'],
                'notes'        => '[Adjustment] ' . ($data['reason'] ?? ''),
                'moved_at'     => $data['adjusted_at'],
            ]);
        });

        return back()->with('status', __('messages.saved'));
    }

    // ─── Warehouse Transfer ──────────────────────────────────────────────

    public function transfers()
    {
        $transfers  = WarehouseTransfer::where('business_id', $this->bid())
            ->with(['product', 'fromWarehouse', 'toWarehouse', 'transferredBy'])
            ->latest('transferred_at')
            ->paginate(30);

        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();

        return view('app.inventory-ops.transfers', compact('transfers', 'products', 'warehouses'));
    }

    public function storeTransfer(Request $request)
    {
        $data = $request->validate([
            'product_id'        => ['required', 'exists:products,id'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id'   => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'quantity'          => ['required', 'integer', 'min:1'],
            'reference_no'      => ['nullable', 'string', 'max:100'],
            'notes'             => ['nullable', 'string', 'max:500'],
            'transferred_at'    => ['nullable', 'date'],
        ]);

        $data['business_id']     = $this->bid();
        $data['transferred_by']  = Auth::id();
        $data['transferred_at']  = $data['transferred_at'] ?? now();
        $data['status']          = 'completed';

        DB::transaction(function () use ($data) {
            WarehouseTransfer::create($data);

            // Mirror two stock movements: out from source, in to destination
            $base = ['business_id' => $data['business_id'], 'product_id' => $data['product_id'],
                     'quantity' => $data['quantity'], 'reference_no' => $data['reference_no'],
                     'moved_at' => $data['transferred_at'],
                     'notes' => '[Transfer] ' . ($data['notes'] ?? '')];

            StockMovement::create(array_merge($base, ['warehouse_id' => $data['from_warehouse_id'], 'type' => 'out']));
            StockMovement::create(array_merge($base, ['warehouse_id' => $data['to_warehouse_id'],   'type' => 'in']));
        });

        return back()->with('status', __('messages.saved'));
    }

    // ─── Stock History ───────────────────────────────────────────────────

    public function history(Request $request)
    {
        $query = StockMovement::where('business_id', $this->bid())
            ->with(['product', 'warehouse'])
            ->latest('moved_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('moved_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('moved_at', '<=', $request->to);
        }

        $movements  = $query->paginate(50)->withQueryString();
        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();

        return view('app.inventory-ops.history', compact('movements', 'products', 'warehouses'));
    }

    // ─── Minimum Stock Alert ─────────────────────────────────────────────

    public function minStockAlert()
    {
        $products = Product::where('business_id', $this->bid())
            ->whereRaw('current_stock <= minimum_stock')
            ->orderByRaw('current_stock - minimum_stock')
            ->get();

        return view('app.inventory-ops.min-stock', compact('products'));
    }

    // ─── Reorder Point ───────────────────────────────────────────────────

    public function reorderPoint()
    {
        $products = Product::where('business_id', $this->bid())->orderBy('name')->get();
        return view('app.inventory-ops.reorder-point', compact('products'));
    }

    public function updateReorderPoint(Request $request, Product $product)
    {
        abort_if($product->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'reorder_point' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($data);

        return back()->with('status', __('messages.saved'));
    }

    // ─── Safety Stock ────────────────────────────────────────────────────

    public function safetyStock()
    {
        $products = Product::where('business_id', $this->bid())->orderBy('name')->get();
        return view('app.inventory-ops.safety-stock', compact('products'));
    }

    public function updateSafetyStock(Request $request, Product $product)
    {
        abort_if($product->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'safety_stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($data);

        return back()->with('status', __('messages.saved'));
    }

    // ─── Costing Method ──────────────────────────────────────────────────

    public function costingMethod()
    {
        $products = Product::where('business_id', $this->bid())->orderBy('name')->get();
        return view('app.inventory-ops.costing-method', compact('products'));
    }

    public function updateCostingMethod(Request $request, Product $product)
    {
        abort_if($product->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'costing_method' => ['required', 'in:fifo,fefo,average'],
        ]);

        $product->update($data);

        return back()->with('status', __('messages.saved'));
    }

    // ─── Serial Numbers ──────────────────────────────────────────────────

    public function serialNumbers(Request $request)
    {
        $query = SerialNumber::where('business_id', $this->bid())
            ->with(['product', 'warehouse'])
            ->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $serials    = $query->paginate(50)->withQueryString();
        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();

        return view('app.inventory-ops.serial-numbers', compact('serials', 'products', 'warehouses'));
    }

    public function storeSerialNumber(Request $request)
    {
        $data = $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'serial_no'    => ['required', 'string', 'max:100'],
            'status'       => ['required', 'in:in_stock,sold,returned,damaged'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $data['business_id'] = $this->bid();

        SerialNumber::create($data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateSerialNumber(Request $request, SerialNumber $serialNumber)
    {
        abort_if($serialNumber->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'product_id'   => ['required', 'exists:products,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'serial_no'    => ['required', 'string', 'max:100'],
            'status'       => ['required', 'in:in_stock,sold,returned,damaged'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $serialNumber->update($data);

        return back()->with('status', __('messages.saved'));
    }

    public function destroySerialNumber(SerialNumber $serialNumber)
    {
        abort_if($serialNumber->business_id !== $this->bid(), 403);
        $serialNumber->delete();
        return back()->with('status', __('messages.deleted'));
    }
}
