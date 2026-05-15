<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Exports\ReportExport;
use App\Models\GoodsReceiveNote;
use App\Models\GrnItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class PurchasingController extends Controller
{
    private function bid(): int
    {
        return Auth::user()->business_id;
    }

    private function nextNo(string $model, string $field, string $prefix): string
    {
        $count = $model::where('business_id', $this->bid())->count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function validItems(array $items, array $products): array
    {
        return collect($items)
            ->filter(fn($i) => !empty($i['product_id']) && (float) ($i['quantity'] ?? 0) > 0)
            ->map(function ($i) use ($products) {
                $product = $products[$i['product_id']] ?? null;
                return [
                    'product_id'   => $i['product_id'],
                    'product_name' => $product?->name ?? $i['product_name'] ?? '',
                    'quantity'     => (float) $i['quantity'],
                    'unit_price'   => (float) ($i['unit_price'] ?? 0),
                    'notes'        => $i['notes'] ?? null,
                ];
            })
            ->values()
            ->toArray();
    }

    private function productMap(): array
    {
        return Product::where('business_id', $this->bid())
            ->get()
            ->keyBy('id')
            ->toArray();
    }

    // ─── Purchase Request ────────────────────────────────────────────────

    public function pr()
    {
        $prs        = PurchaseRequest::where('business_id', $this->bid())
            ->with(['supplier', 'requestedBy', 'items'])
            ->latest('requested_at')
            ->paginate(30);
        $suppliers  = Supplier::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();
        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();

        return view('app.purchasing.pr', compact('prs', 'suppliers', 'warehouses', 'products'));
    }

    public function storePr(Request $request)
    {
        $data = $request->validate([
            'supplier_id'  => ['nullable', 'exists:suppliers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'notes'        => ['nullable', 'string', 'max:1000'],
            'requested_at' => ['nullable', 'date'],
            'items'        => ['required', 'array'],
            'items.*.product_id'  => ['required', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['nullable', 'numeric', 'min:0'],
            'items.*.notes'       => ['nullable', 'string', 'max:255'],
        ]);

        $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        $validItems = $this->validItems($data['items'], $products->toArray());

        if (empty($validItems)) {
            return back()->withErrors(['items' => 'At least one item is required.']);
        }

        DB::transaction(function () use ($data, $validItems) {
            $pr = PurchaseRequest::create([
                'business_id'  => $this->bid(),
                'pr_no'        => $this->nextNo(PurchaseRequest::class, 'pr_no', 'PR-'),
                'supplier_id'  => $data['supplier_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'requested_by' => Auth::id(),
                'status'       => 'pending',
                'notes'        => $data['notes'] ?? null,
                'requested_at' => $data['requested_at'] ?? now(),
            ]);

            foreach ($validItems as $item) {
                $pr->items()->create($item);
            }
        });

        return back()->with('status', __('messages.saved'));
    }

    public function updatePrStatus(Request $request, PurchaseRequest $purchaseRequest)
    {
        abort_if($purchaseRequest->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:draft,pending,approved,rejected,cancelled'],
        ]);

        $purchaseRequest->update($data);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyPr(PurchaseRequest $purchaseRequest)
    {
        abort_if($purchaseRequest->business_id !== $this->bid(), 403);
        $purchaseRequest->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ─── Purchase Order ──────────────────────────────────────────────────

    public function po()
    {
        $pos = PurchaseOrder::where('business_id', $this->bid())
            ->with(['supplier', 'purchaseRequest', 'createdBy', 'items'])
            ->latest('ordered_at')
            ->paginate(30);

        $suppliers  = Supplier::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();
        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $prs        = PurchaseRequest::where('business_id', $this->bid())
            ->where('status', 'approved')
            ->orderBy('pr_no')
            ->get();

        return view('app.purchasing.po', compact('pos', 'suppliers', 'warehouses', 'products', 'prs'));
    }

    public function storePo(Request $request)
    {
        $data = $request->validate([
            'purchase_request_id' => ['nullable', 'exists:purchase_requests,id'],
            'supplier_id'         => ['nullable', 'exists:suppliers,id'],
            'warehouse_id'        => ['nullable', 'exists:warehouses,id'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'ordered_at'          => ['nullable', 'date'],
            'expected_at'         => ['nullable', 'date'],
            'items'               => ['required', 'array'],
            'items.*.product_id'  => ['required', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        $validItems = $this->validItems($data['items'], $products->toArray());

        if (empty($validItems)) {
            return back()->withErrors(['items' => 'At least one item is required.']);
        }

        DB::transaction(function () use ($data, $validItems) {
            $po = PurchaseOrder::create([
                'business_id'         => $this->bid(),
                'po_no'               => $this->nextNo(PurchaseOrder::class, 'po_no', 'PO-'),
                'purchase_request_id' => $data['purchase_request_id'] ?? null,
                'supplier_id'         => $data['supplier_id'] ?? null,
                'warehouse_id'        => $data['warehouse_id'] ?? null,
                'created_by'          => Auth::id(),
                'status'              => 'pending_approval',
                'notes'               => $data['notes'] ?? null,
                'ordered_at'          => $data['ordered_at'] ?? now(),
                'expected_at'         => $data['expected_at'] ?? null,
            ]);

            foreach ($validItems as $item) {
                unset($item['notes']);
                $po->items()->create($item);
            }
        });

        return back()->with('status', __('messages.saved'));
    }

    public function destroyPo(PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->business_id !== $this->bid(), 403);
        abort_if(in_array($purchaseOrder->status, ['approved', 'partial', 'completed'], true), 422, 'Cannot delete an approved PO.');
        $purchaseOrder->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ─── PO Approvals ────────────────────────────────────────────────────

    public function poApprovals()
    {
        $pending = PurchaseOrder::where('business_id', $this->bid())
            ->whereIn('status', ['pending_approval', 'draft'])
            ->with(['supplier', 'purchaseRequest', 'createdBy', 'items'])
            ->latest('ordered_at')
            ->paginate(30);

        $all = PurchaseOrder::where('business_id', $this->bid())
            ->whereIn('status', ['approved', 'rejected', 'cancelled'])
            ->with(['supplier', 'approvedBy'])
            ->latest('updated_at')
            ->limit(20)
            ->get();

        return view('app.purchasing.po-approvals', compact('pending', 'all'));
    }

    public function approvePo(PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->business_id !== $this->bid(), 403);

        $purchaseOrder->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return back()->with('status', __('messages.po_approved'));
    }

    public function rejectPo(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_if($purchaseOrder->business_id !== $this->bid(), 403);

        $purchaseOrder->update([
            'status'  => 'rejected',
            'notes'   => ($purchaseOrder->notes ? $purchaseOrder->notes . "\n" : '') . '[Rejected] ' . ($request->reason ?? ''),
        ]);

        return back()->with('status', __('messages.po_rejected'));
    }

    // ─── Goods Receive Note ──────────────────────────────────────────────

    public function grn()
    {
        $grns = GoodsReceiveNote::where('business_id', $this->bid())
            ->with(['supplier', 'warehouse', 'receivedBy', 'purchaseOrder', 'items'])
            ->latest('received_at')
            ->paginate(30);

        $suppliers  = Supplier::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();
        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $pos        = PurchaseOrder::where('business_id', $this->bid())
            ->whereIn('status', ['approved', 'partial'])
            ->orderBy('po_no')
            ->get();

        return view('app.purchasing.grn', compact('grns', 'suppliers', 'warehouses', 'products', 'pos'));
    }

    public function storeGrn(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id'  => ['nullable', 'exists:purchase_orders,id'],
            'supplier_id'        => ['nullable', 'exists:suppliers,id'],
            'warehouse_id'       => ['nullable', 'exists:warehouses,id'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'received_at'        => ['nullable', 'date'],
            'items'              => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        $validItems = $this->validItems($data['items'], $products->toArray());

        if (empty($validItems)) {
            return back()->withErrors(['items' => 'At least one item is required.']);
        }

        DB::transaction(function () use ($data, $validItems, $products) {
            $grn = GoodsReceiveNote::create([
                'business_id'       => $this->bid(),
                'grn_no'            => $this->nextNo(GoodsReceiveNote::class, 'grn_no', 'GRN-'),
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'supplier_id'       => $data['supplier_id'] ?? null,
                'warehouse_id'      => $data['warehouse_id'] ?? null,
                'received_by'       => Auth::id(),
                'status'            => 'completed',
                'notes'             => $data['notes'] ?? null,
                'received_at'       => $data['received_at'] ?? now(),
            ]);

            foreach ($validItems as $item) {
                unset($item['notes']);
                $grn->items()->create($item);

                // Update stock IN
                Product::where('id', $item['product_id'])->increment('current_stock', (int) $item['quantity']);
                StockMovement::create([
                    'business_id'  => $this->bid(),
                    'product_id'   => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'] ?? null,
                    'type'         => 'in',
                    'quantity'     => (int) $item['quantity'],
                    'reference_no' => $grn->grn_no,
                    'notes'        => '[GRN] ' . ($data['notes'] ?? ''),
                    'moved_at'     => $data['received_at'] ?? now(),
                ]);
            }

            // Auto-create supplier debt
            if (!empty($data['supplier_id'])) {
                $total = collect($validItems)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
                if ($total > 0) {
                    SupplierDebt::create([
                        'business_id'       => $this->bid(),
                        'supplier_id'       => $data['supplier_id'],
                        'purchase_order_id' => $data['purchase_order_id'] ?? null,
                        'invoice_no'        => $grn->grn_no,
                        'amount'            => $total,
                        'paid_amount'       => 0,
                        'status'            => 'unpaid',
                    ]);
                }
            }

            // Update PO status
            if (!empty($data['purchase_order_id'])) {
                $po = PurchaseOrder::with('items')->find($data['purchase_order_id']);
                if ($po) {
                    $totalOrdered  = $po->items->sum('quantity');
                    $totalReceived = GrnItem::whereHas('grn', fn($q) => $q->where('purchase_order_id', $po->id))
                        ->sum('quantity');
                    $po->update([
                        'status' => $totalReceived >= $totalOrdered ? 'completed' : 'partial',
                    ]);
                }
            }
        });

        return back()->with('status', __('messages.saved'));
    }

    // ─── Purchase Returns ────────────────────────────────────────────────

    public function purchaseReturns()
    {
        $returns    = PurchaseReturn::where('business_id', $this->bid())
            ->with(['supplier', 'warehouse', 'returnedBy', 'items'])
            ->latest('returned_at')
            ->paginate(30);

        $suppliers  = Supplier::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('business_id', $this->bid())->orderBy('name')->get();
        $products   = Product::where('business_id', $this->bid())->orderBy('name')->get();
        $grns       = GoodsReceiveNote::where('business_id', $this->bid())->orderBy('grn_no')->get();

        return view('app.purchasing.returns', compact('returns', 'suppliers', 'warehouses', 'products', 'grns'));
    }

    public function storePurchaseReturn(Request $request)
    {
        $data = $request->validate([
            'grn_id'             => ['nullable', 'exists:goods_receive_notes,id'],
            'supplier_id'        => ['nullable', 'exists:suppliers,id'],
            'warehouse_id'       => ['nullable', 'exists:warehouses,id'],
            'reason'             => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'returned_at'        => ['nullable', 'date'],
            'items'              => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))->get()->keyBy('id');
        $validItems = $this->validItems($data['items'], $products->toArray());

        if (empty($validItems)) {
            return back()->withErrors(['items' => 'At least one item is required.']);
        }

        DB::transaction(function () use ($data, $validItems) {
            $ret = PurchaseReturn::create([
                'business_id'  => $this->bid(),
                'return_no'    => $this->nextNo(PurchaseReturn::class, 'return_no', 'RTN-'),
                'grn_id'       => $data['grn_id'] ?? null,
                'supplier_id'  => $data['supplier_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'returned_by'  => Auth::id(),
                'reason'       => $data['reason'] ?? null,
                'status'       => 'completed',
                'notes'        => $data['notes'] ?? null,
                'returned_at'  => $data['returned_at'] ?? now(),
            ]);

            foreach ($validItems as $item) {
                unset($item['notes']);
                $ret->items()->create($item);

                // Update stock OUT
                Product::where('id', $item['product_id'])->decrement('current_stock', (int) $item['quantity']);
                StockMovement::create([
                    'business_id'  => $this->bid(),
                    'product_id'   => $item['product_id'],
                    'warehouse_id' => $data['warehouse_id'] ?? null,
                    'type'         => 'out',
                    'quantity'     => (int) $item['quantity'],
                    'reference_no' => $ret->return_no,
                    'notes'        => '[Purchase Return] ' . ($data['reason'] ?? ''),
                    'moved_at'     => $data['returned_at'] ?? now(),
                ]);
            }
        });

        return back()->with('status', __('messages.saved'));
    }

    // ─── Supplier Debts ──────────────────────────────────────────────────

    public function supplierDebts()
    {
        $debts = SupplierDebt::where('business_id', $this->bid())
            ->with(['supplier', 'purchaseOrder'])
            ->latest()
            ->paginate(30);

        $suppliers = Supplier::where('business_id', $this->bid())->where('is_active', true)->orderBy('name')->get();
        $pos       = PurchaseOrder::where('business_id', $this->bid())->orderBy('po_no')->get();

        $summary = [
            'total'      => SupplierDebt::where('business_id', $this->bid())->sum('amount'),
            'paid'       => SupplierDebt::where('business_id', $this->bid())->sum('paid_amount'),
            'overdue'    => SupplierDebt::where('business_id', $this->bid())
                ->where('status', '!=', 'paid')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('app.purchasing.supplier-debts', compact('debts', 'suppliers', 'pos', 'summary'));
    }

    public function storeSupplierDebt(Request $request)
    {
        $data = $request->validate([
            'supplier_id'       => ['required', 'exists:suppliers,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'invoice_no'        => ['nullable', 'string', 'max:100'],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'paid_amount'       => ['nullable', 'numeric', 'min:0'],
            'due_date'          => ['nullable', 'date'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ]);

        $paid = (float) ($data['paid_amount'] ?? 0);
        $amount = (float) $data['amount'];
        $status = match (true) {
            $paid >= $amount => 'paid',
            $paid > 0        => 'partial',
            default          => 'unpaid',
        };

        SupplierDebt::create([
            'business_id'       => $this->bid(),
            'supplier_id'       => $data['supplier_id'],
            'purchase_order_id' => $data['purchase_order_id'] ?? null,
            'invoice_no'        => $data['invoice_no'] ?? null,
            'amount'            => $amount,
            'paid_amount'       => $paid,
            'due_date'          => $data['due_date'] ?? null,
            'status'            => $status,
            'notes'             => $data['notes'] ?? null,
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateSupplierDebt(Request $request, SupplierDebt $supplierDebt)
    {
        abort_if($supplierDebt->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);

        $paid   = (float) $data['paid_amount'];
        $amount = $supplierDebt->amount;
        $status = match (true) {
            $paid >= $amount => 'paid',
            $paid > 0        => 'partial',
            default          => 'unpaid',
        };

        $supplierDebt->update([
            'paid_amount' => $paid,
            'status'      => $status,
            'notes'       => $data['notes'] ?? $supplierDebt->notes,
        ]);

        return back()->with('status', __('messages.saved'));
    }

    // ─── Supplier Performance ────────────────────────────────────────────

    public function supplierPerformance()
    {
        $suppliers = Supplier::where('business_id', $this->bid())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $bid = $this->bid();

        $stats = $suppliers->map(function ($s) use ($bid) {
            $totalPos     = PurchaseOrder::where('business_id', $bid)->where('supplier_id', $s->id)->count();
            $completedPos = PurchaseOrder::where('business_id', $bid)->where('supplier_id', $s->id)
                ->where('status', 'completed')->count();
            $totalOrdered = PurchaseOrderItem::whereHas('purchaseOrder',
                fn($q) => $q->where('business_id', $bid)->where('supplier_id', $s->id)
            )->sum('quantity');
            $totalReceived = GrnItem::whereHas('grn',
                fn($q) => $q->where('business_id', $bid)->where('supplier_id', $s->id)
            )->sum('quantity');
            $totalReturned = PurchaseReturnItem::whereHas('purchaseReturn',
                fn($q) => $q->where('business_id', $bid)->where('supplier_id', $s->id)
            )->sum('quantity');
            $totalDebt = SupplierDebt::where('business_id', $bid)->where('supplier_id', $s->id)
                ->whereIn('status', ['unpaid', 'partial'])->sum(DB::raw('amount - paid_amount'));

            return [
                'supplier'       => $s,
                'total_pos'      => $totalPos,
                'completed_pos'  => $completedPos,
                'on_time_rate'   => $totalPos > 0 ? round($completedPos / $totalPos * 100) : 0,
                'total_ordered'  => $totalOrdered,
                'total_received' => $totalReceived,
                'total_returned' => $totalReturned,
                'return_rate'    => $totalReceived > 0 ? round($totalReturned / $totalReceived * 100, 1) : 0,
                'outstanding_debt' => $totalDebt,
            ];
        });

        return view('app.purchasing.supplier-performance', compact('stats'));
    }

    // ──────────────────────────────────────────────
    // EXCEL EXPORT (all purchasing lists are transactional — export only)
    // ──────────────────────────────────────────────

    public function exportPr()
    {
        $rows = PurchaseRequest::where('business_id', $this->bid())
            ->with(['supplier', 'requestedBy', 'items'])->latest('requested_at')->get()
            ->map(fn ($r) => [$r->pr_no, $r->supplier?->name, $r->status, $r->items->sum('total_price'), $r->notes, $r->requested_at])->toArray();
        return Excel::download(new ReportExport(['PR No', 'Supplier', 'Status', 'Total', 'Notes', 'Date'], $rows, 'Purchase Requests'), 'purchase-requests.xlsx');
    }

    public function exportPo()
    {
        $rows = PurchaseOrder::where('business_id', $this->bid())
            ->with(['supplier', 'createdBy', 'items'])->latest('ordered_at')->get()
            ->map(fn ($o) => [$o->po_no, $o->supplier?->name, $o->status, $o->items->sum('total_price'), $o->notes, $o->ordered_at])->toArray();
        return Excel::download(new ReportExport(['PO No', 'Supplier', 'Status', 'Total', 'Notes', 'Date'], $rows, 'Purchase Orders'), 'purchase-orders.xlsx');
    }

    public function exportGrn()
    {
        $rows = GoodsReceiveNote::where('business_id', $this->bid())
            ->with(['supplier', 'warehouse', 'receivedBy'])->latest('received_at')->get()
            ->map(fn ($g) => [$g->grn_no, $g->supplier?->name, $g->warehouse?->name, $g->status, $g->receivedBy?->name, $g->received_at])->toArray();
        return Excel::download(new ReportExport(['GRN No', 'Supplier', 'Warehouse', 'Status', 'Received By', 'Date'], $rows, 'GRN'), 'grn.xlsx');
    }

    public function exportPurchaseReturns()
    {
        $rows = PurchaseReturn::where('business_id', $this->bid())
            ->with(['supplier', 'returnedBy'])->latest('returned_at')->get()
            ->map(fn ($r) => [$r->return_no, $r->supplier?->name, $r->status, $r->reason, $r->returnedBy?->name, $r->returned_at])->toArray();
        return Excel::download(new ReportExport(['Return No', 'Supplier', 'Status', 'Reason', 'Returned By', 'Date'], $rows, 'Purchase Returns'), 'purchase-returns.xlsx');
    }

    public function exportSupplierDebts()
    {
        $rows = SupplierDebt::where('business_id', $this->bid())
            ->with(['supplier'])->latest()->get()
            ->map(fn ($d) => [$d->supplier?->name, $d->amount, $d->paid_amount, $d->amount - $d->paid_amount, $d->status, $d->due_at])->toArray();
        return Excel::download(new ReportExport(['Supplier', 'Amount', 'Paid', 'Balance', 'Status', 'Due Date'], $rows, 'Supplier Debts'), 'supplier-debts.xlsx');
    }

    public function exportSupplierPerformance()
    {
        $bid  = $this->bid();
        $suppliers = Supplier::where('business_id', $bid)->where('is_active', true)->orderBy('name')->get();
        $rows = $suppliers->map(function ($s) use ($bid) {
            $totalPos    = PurchaseOrder::where('business_id', $bid)->where('supplier_id', $s->id)->count();
            $completedPos = PurchaseOrder::where('business_id', $bid)->where('supplier_id', $s->id)->where('status', 'completed')->count();
            $totalReceived = GrnItem::whereHas('grn', fn ($q) => $q->where('business_id', $bid)->where('supplier_id', $s->id))->sum('quantity');
            $totalReturned = PurchaseReturnItem::whereHas('purchaseReturn', fn ($q) => $q->where('business_id', $bid)->where('supplier_id', $s->id))->sum('quantity');
            return [$s->name, $totalPos, $completedPos, $totalPos > 0 ? round($completedPos / $totalPos * 100) . '%' : '-', $totalReceived, $totalReturned];
        })->toArray();
        return Excel::download(new ReportExport(['Supplier', 'Total POs', 'Completed POs', 'On-Time Rate', 'Total Received', 'Total Returned'], $rows, 'Supplier Performance'), 'supplier-performance.xlsx');
    }
}
