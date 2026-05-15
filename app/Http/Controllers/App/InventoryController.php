<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Exports\ReportExport;
use App\Imports\GenericImport;
use App\Models\Package;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function users()
    {
        return $this->resource('users', User::where('business_id', Auth::user()->business_id)->with('roles')->get());
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'platform_role' => ['nullable', 'string', 'in:customer,staff,manager'],
            'photo_path' => ['nullable', 'image', 'max:2048'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $photoPath = $request->file('photo_path')?->store('users', 'public');
        unset($data['photo_path']);

        $user = User::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'platform_role' => $data['platform_role'] ?? 'customer',
            'photo_path' => $photoPath,
        ]);

        if (!empty($data['roles'])) {
            $user->roles()->attach($data['roles']);
        }

        return back()->with('status', __('messages.saved'));
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorizeBusinessRecord($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'platform_role' => ['nullable', 'string', 'in:customer,staff,manager'],
            'photo_path' => ['nullable', 'image', 'max:2048'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'platform_role' => $data['platform_role'] ?? 'customer',
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('photo_path')) {
            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }

            $updateData['photo_path'] = $request->file('photo_path')->store('users', 'public');
        }

        $user->update($updateData);

        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        return back()->with('status', __('messages.saved'));
    }

    public function roles()
    {
        return view('app.roles.index', [
            'roles' => Role::where('business_id', Auth::user()->business_id)->latest()->get(),
            'permissionGroups' => Role::permissionGroups(),
        ]);
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'permissions' => ['nullable', 'array'],
        ]);

        Role::create([
            'business_id' => Auth::user()->business_id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'permissions' => $data['permissions'] ?? [],
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function userRoles()
    {
        return $this->resource('user_roles', User::where('business_id', Auth::user()->business_id)->with('roles')->get());
    }

    public function storeUserRole(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        User::where('business_id', Auth::user()->business_id)->findOrFail($data['user_id'])->roles()->syncWithoutDetaching([$data['role_id']]);

        return back()->with('status', __('messages.saved'));
    }

    public function reports()
    {
        $businessId = Auth::user()->business_id;

        // Stock card - products and quantities
        $products = Product::where('business_id', $businessId)
            ->selectRaw('name, current_stock')
            ->orderByDesc('current_stock')
            ->take(10)
            ->get();

        // Low stock products
        $lowStockProducts = Product::where('business_id', $businessId)
            ->whereRaw('current_stock <= minimum_stock')
            ->count();

        // Movement summary by type
        $movementsByType = StockMovement::where('business_id', $businessId)
            ->selectRaw('type, COUNT(*) as count, SUM(quantity) as total_quantity')
            ->groupBy('type')
            ->get();

        // Top products by movement
        $topProductsByMovement = StockMovement::where('business_id', $businessId)
            ->selectRaw('product_id, COUNT(*) as count, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('count')
            ->take(8)
            ->with('product')
            ->get()
            ->map(function ($movement) {
                return [
                    'name' => $movement->product->name,
                    'count' => $movement->count,
                    'quantity' => $movement->total_qty,
                ];
            });

        // Stock valuation (product price * quantity)
        $stockValuation = Product::where('business_id', $businessId)
            ->selectRaw('SUM(price * current_stock) as total_value')
            ->first()
            ->total_value ?? 0;

        // Monthly movements
        $monthlyMovements = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('M Y');
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();
            $count = StockMovement::where('business_id', $businessId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            $monthlyMovements[$month] = $count;
        }

        $chartLabels = [];
        $chartData = [];
        foreach ($movementsByType as $movement) {
            $chartLabels[] = ucfirst($movement->type);
            $chartData[] = $movement->total_quantity;
        }

        $topProductLabels = $topProductsByMovement->pluck('name')->toArray();
        $topProductValues = $topProductsByMovement->pluck('quantity')->toArray();

        return view('app.reports', [
            'stats' => [
                'total_products' => Product::where('business_id', $businessId)->count(),
                'low_stock_count' => $lowStockProducts,
                'total_stock_value' => number_format($stockValuation, 2),
                'total_movements' => StockMovement::where('business_id', $businessId)->count(),
            ],
            'chartData' => [
                'movementSummary' => [
                    'labels' => $chartLabels ?: ['No data'],
                    'data' => $chartData ?: [0],
                ],
                'monthlyMovements' => $monthlyMovements,
                'topProducts' => [
                    'labels' => $topProductLabels ?: ['No data'],
                    'data' => $topProductValues ?: [0],
                ],
            ],
        ]);
    }

    public function stockOpname()
    {
        return $this->resource('stock_opname', StockOpname::where('business_id', Auth::user()->business_id)->latest()->get());
    }

    public function storeStockOpname(Request $request)
    {
        $data = $this->validateStockOpname($request);

        $evidencePath = $request->file('evidence_image')?->store('stock-opname', 'public');
        unset($data['evidence_image']);

        StockOpname::create($data + [
            'business_id' => Auth::user()->business_id,
            'evidence_image_path' => $evidencePath,
            'status' => 'draft',
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateStockOpname(Request $request, StockOpname $stockOpname)
    {
        $this->authorizeBusinessRecord($stockOpname);

        $data = $this->validateStockOpname($request);
        unset($data['evidence_image']);

        if ($request->hasFile('evidence_image')) {
            if ($stockOpname->evidence_image_path) {
                Storage::disk('public')->delete($stockOpname->evidence_image_path);
            }

            $data['evidence_image_path'] = $request->file('evidence_image')->store('stock-opname', 'public');
        }

        $stockOpname->update($data + ['status' => $request->input('status', $stockOpname->status)]);

        return back()->with('status', __('messages.saved'));
    }

    public function stores()
    {
        return $this->resource('stores', Store::where('business_id', Auth::user()->business_id)->get());
    }

    public function createStore()
    {
        return view('app.stores.form', [
            'store' => new Store(['status' => 'active']),
            'mode' => 'create',
        ]);
    }

    public function storeStore(Request $request)
    {
        Store::create($this->validateStore($request) + ['business_id' => Auth::user()->business_id]);

        return redirect()->route('stores.index')->with('status', __('messages.saved'));
    }

    public function editStore(Store $store)
    {
        $this->authorizeBusinessRecord($store);

        return view('app.stores.form', [
            'store' => $store,
            'mode' => 'edit',
        ]);
    }

    public function updateStore(Request $request, Store $store)
    {
        $this->authorizeBusinessRecord($store);

        $store->update($this->validateStore($request));

        return redirect()->route('stores.index')->with('status', __('messages.saved'));
    }

    public function warehouses()
    {
        return $this->resource('warehouses', Warehouse::where('business_id', Auth::user()->business_id)->get());
    }

    public function createWarehouse()
    {
        return view('app.warehouses.form', [
            'warehouse' => new Warehouse(['status' => 'active']),
            'stores' => Store::where('business_id', Auth::user()->business_id)->get(),
            'mode' => 'create',
        ]);
    }

    public function storeWarehouse(Request $request)
    {
        Warehouse::create($this->validateWarehouse($request) + ['business_id' => Auth::user()->business_id]);

        return redirect()->route('warehouses.index')->with('status', __('messages.saved'));
    }

    public function editWarehouse(Warehouse $warehouse)
    {
        $this->authorizeBusinessRecord($warehouse);

        return view('app.warehouses.form', [
            'warehouse' => $warehouse,
            'stores' => Store::where('business_id', Auth::user()->business_id)->get(),
            'mode' => 'edit',
        ]);
    }

    public function updateWarehouse(Request $request, Warehouse $warehouse)
    {
        $this->authorizeBusinessRecord($warehouse);

        $warehouse->update($this->validateWarehouse($request));

        return redirect()->route('warehouses.index')->with('status', __('messages.saved'));
    }

    public function products()
    {
        return $this->resource('products', Product::where('business_id', Auth::user()->business_id)->get());
    }

    public function createProduct()
    {
        return view('app.products.form', [
            'product' => new Product([
                'unit' => 'pcs',
                'price' => 0,
                'minimum_stock' => 0,
                'current_stock' => 0,
            ]),
            'mode' => 'create',
        ]);
    }

    public function storeProduct(Request $request)
    {
        $data = $this->validateProduct($request);

        $photoPath = $request->file('photo')?->store('products', 'public');
        unset($data['photo']);

        Product::create($data + [
            'business_id' => Auth::user()->business_id,
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('products.index')->with('status', __('messages.saved'));
    }

    public function editProduct(Product $product)
    {
        $this->authorizeProduct($product);

        return view('app.products.form', [
            'product' => $product,
            'mode' => 'edit',
        ]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $data = $this->validateProduct($request);
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            if ($product->photo_path) {
                Storage::disk('public')->delete($product->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')->with('status', __('messages.saved'));
    }

    public function stockMovements()
    {
        return $this->resource('stock_movements', StockMovement::where('business_id', Auth::user()->business_id)->latest()->get());
    }

    public function storeStockMovement(Request $request)
    {
        $data = $this->validateStockMovement($request);

        $evidencePath = $request->file('evidence_image')?->store('stock-movements', 'public');
        unset($data['evidence_image']);

        StockMovement::create($data + [
            'business_id' => Auth::user()->business_id,
            'evidence_image_path' => $evidencePath,
            'moved_at' => now(),
        ]);

        $this->applyStockMovement($data['product_id'], $data['type'], $data['quantity']);

        return back()->with('status', __('messages.saved'));
    }

    public function updateStockMovement(Request $request, StockMovement $stockMovement)
    {
        $this->authorizeBusinessRecord($stockMovement);

        $data = $this->validateStockMovement($request);
        unset($data['evidence_image']);

        $this->applyStockMovement($stockMovement->product_id, $stockMovement->type, $stockMovement->quantity, -1);

        if ($request->hasFile('evidence_image')) {
            if ($stockMovement->evidence_image_path) {
                Storage::disk('public')->delete($stockMovement->evidence_image_path);
            }

            $data['evidence_image_path'] = $request->file('evidence_image')->store('stock-movements', 'public');
        }

        $stockMovement->update($data);
        $this->applyStockMovement($data['product_id'], $data['type'], $data['quantity']);

        return back()->with('status', __('messages.saved'));
    }

    public function packages()
    {
        return $this->resource('packages', Package::where('business_id', Auth::user()->business_id)->get());
    }

    public function storePackage(Request $request)
    {
        Package::create($this->validatePackage($request) + ['business_id' => Auth::user()->business_id]);

        return back()->with('status', __('messages.saved'));
    }

    public function updatePackage(Request $request, Package $package)
    {
        $this->authorizeBusinessRecord($package);

        $package->update($this->validatePackage($request));

        return back()->with('status', __('messages.saved'));
    }

    private function resource(string $resource, $items)
    {
        $businessId = Auth::user()->business_id;

        return view('app.resources.index', [
            'resource' => $resource,
            'items' => $items,
            'users' => User::where('business_id', $businessId)->get(),
            'roles' => Role::where('business_id', $businessId)->get(),
            'stores' => Store::where('business_id', $businessId)->get(),
            'warehouses' => Warehouse::where('business_id', $businessId)->get(),
            'products' => Product::where('business_id', $businessId)->get(),
            'permissions' => Role::defaultOwnerPermissions(),
        ]);
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'sku' => ['nullable', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'max:1024'],
        ]);
    }

    private function authorizeProduct(Product $product): void
    {
        abort_if((int) $product->business_id !== (int) Auth::user()->business_id, 404);
    }

    private function validateStore(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function validateWarehouse(Request $request): array
    {
        $businessId = Auth::user()->business_id;

        return $request->validate([
            'store_id' => ['nullable', Rule::exists('stores', 'id')->where(fn ($query) => $query->where('business_id', $businessId))],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function validateStockMovement(Request $request): array
    {
        $businessId = Auth::user()->business_id;

        return $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')->where(fn ($query) => $query->where('business_id', $businessId))],
            'warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('business_id', $businessId))],
            'type' => ['required', Rule::in(['in', 'out', 'transfer'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference_no' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'evidence_image' => ['nullable', 'image', 'max:1024'],
        ]);
    }

    private function validateStockOpname(Request $request): array
    {
        $businessId = Auth::user()->business_id;

        return $request->validate([
            'warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('business_id', $businessId))],
            'reference_no' => ['required', 'string', 'max:120'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['draft', 'in_progress', 'completed'])],
            'notes' => ['nullable', 'string'],
            'evidence_image' => ['nullable', 'image', 'max:1024'],
        ]);
    }

    private function validatePackage(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:60'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    private function authorizeBusinessRecord($record): void
    {
        abort_if((int) $record->business_id !== (int) Auth::user()->business_id, 404);
    }

    private function applyStockMovement($productId, string $type, int $quantity, int $direction = 1): void
    {
        if (! in_array($type, ['in', 'out'], true)) {
            return;
        }

        $product = Product::where('business_id', Auth::user()->business_id)->find($productId);

        if ($product) {
            $product->increment('current_stock', ($type === 'in' ? $quantity : -$quantity) * $direction);
        }
    }

    // ──────────────────────────────────────────────
    // PRODUCTS EXCEL EXPORT / IMPORT
    // ──────────────────────────────────────────────

    public function exportProducts()
    {
        $bid = Auth::user()->business_id;
        $rows = Product::where('business_id', $bid)->orderBy('name')->get()
            ->map(fn ($p) => [$p->name, $p->sku, $p->category, $p->unit, $p->price, $p->minimum_stock, $p->current_stock])->toArray();
        return Excel::download(new ReportExport(['Name', 'SKU', 'Category', 'Unit', 'Price', 'Minimum Stock', 'Current Stock'], $rows, 'Products'), 'products.xlsx');
    }

    public function importProducts(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);
        $bid = Auth::user()->business_id;
        Excel::import(new GenericImport(function ($row) use ($bid) {
            $name = trim($row['name'] ?? '');
            if (!$name) return;
            Product::updateOrCreate(
                ['business_id' => $bid, 'name' => $name],
                array_filter([
                    'sku'           => $row['sku'] ?? null,
                    'category'      => $row['category'] ?? null,
                    'unit'          => $row['unit'] ?? 'pcs',
                    'price'         => isset($row['price']) ? (float) $row['price'] : 0,
                    'minimum_stock' => isset($row['minimum_stock']) ? (int) $row['minimum_stock'] : 0,
                ], fn ($v) => $v !== null)
            );
        }), $request->file('file'));
        return back()->with('status', __('messages.saved'));
    }
}
