<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\BatchLot;
use App\Models\BinLocation;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Exports\ReportExport;
use App\Imports\GenericImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class MasterDataController extends Controller
{
    private function bid(): int
    {
        return Auth::user()->business_id;
    }

    private function authorizeBusinessRecord($record): void
    {
        if ($record->business_id !== $this->bid()) {
            abort(403);
        }
    }

    // ──────────────────────────────────────────────
    // CATEGORIES
    // ──────────────────────────────────────────────

    public function categories()
    {
        return view('app.master-data.categories', [
            'categories' => Category::where('business_id', $this->bid())->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        Category::create(['business_id' => $this->bid()] + $data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateCategory(Request $request, Category $category)
    {
        $this->authorizeBusinessRecord($category);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyCategory(Category $category)
    {
        $this->authorizeBusinessRecord($category);
        $category->delete();

        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // BRANDS
    // ──────────────────────────────────────────────

    public function brands()
    {
        return view('app.master-data.brands', [
            'brands' => Brand::where('business_id', $this->bid())->orderBy('name')->get(),
        ]);
    }

    public function storeBrand(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        Brand::create(['business_id' => $this->bid()] + $data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateBrand(Request $request, Brand $brand)
    {
        $this->authorizeBusinessRecord($brand);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $brand->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyBrand(Brand $brand)
    {
        $this->authorizeBusinessRecord($brand);
        $brand->delete();

        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // SUPPLIERS
    // ──────────────────────────────────────────────

    public function suppliers()
    {
        return view('app.master-data.suppliers', [
            'suppliers' => Supplier::where('business_id', $this->bid())->orderBy('name')->get(),
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'address'        => ['nullable', 'string'],
        ]);

        Supplier::create(['business_id' => $this->bid()] + $data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $this->authorizeBusinessRecord($supplier);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'address'        => ['nullable', 'string'],
        ]);

        $supplier->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroySupplier(Supplier $supplier)
    {
        $this->authorizeBusinessRecord($supplier);
        $supplier->delete();

        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // CUSTOMERS (inventory contacts)
    // ──────────────────────────────────────────────

    public function inventoryCustomers()
    {
        return view('app.master-data.customers', [
            'customers' => Customer::where('business_id', $this->bid())->orderBy('name')->get(),
        ]);
    }

    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'address'        => ['nullable', 'string'],
        ]);

        Customer::create(['business_id' => $this->bid()] + $data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateCustomer(Request $request, Customer $customer)
    {
        $this->authorizeBusinessRecord($customer);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'code'           => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'address'        => ['nullable', 'string'],
        ]);

        $customer->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyCustomer(Customer $customer)
    {
        $this->authorizeBusinessRecord($customer);
        $customer->delete();

        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // UNITS
    // ──────────────────────────────────────────────

    public function units()
    {
        return view('app.master-data.units', [
            'units' => Unit::where('business_id', $this->bid())->orderBy('name')->get(),
        ]);
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'symbol'      => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        Unit::create(['business_id' => $this->bid()] + $data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        $this->authorizeBusinessRecord($unit);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'symbol'      => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $unit->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyUnit(Unit $unit)
    {
        $this->authorizeBusinessRecord($unit);
        $unit->delete();

        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // BARCODES
    // ──────────────────────────────────────────────

    public function barcodes()
    {
        return view('app.master-data.barcodes', [
            'barcodes' => ProductBarcode::where('business_id', $this->bid())
                ->with('product')
                ->orderBy('created_at', 'desc')
                ->get(),
            'products' => Product::where('business_id', $this->bid())->orderBy('name')->get(),
        ]);
    }

    public function storeBarcode(Request $request)
    {
        $data = $request->validate([
            'product_id'   => ['nullable', 'exists:products,id'],
            'barcode_type' => ['required', 'string', 'in:barcode,qr_code,ean13,upc,code128'],
            'value'        => ['required', 'string', 'max:255'],
            'is_primary'   => ['nullable', 'boolean'],
        ]);

        ProductBarcode::create([
            'business_id' => $this->bid(),
            'is_primary'  => false,
        ] + $data + ['is_primary' => $request->boolean('is_primary')]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateBarcode(Request $request, ProductBarcode $productBarcode)
    {
        $this->authorizeBusinessRecord($productBarcode);

        $data = $request->validate([
            'product_id'   => ['nullable', 'exists:products,id'],
            'barcode_type' => ['required', 'string', 'in:barcode,qr_code,ean13,upc,code128'],
            'value'        => ['required', 'string', 'max:255'],
        ]);

        $productBarcode->update($data + ['is_primary' => $request->boolean('is_primary')]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyBarcode(ProductBarcode $productBarcode)
    {
        $this->authorizeBusinessRecord($productBarcode);
        $productBarcode->delete();

        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // BATCH / LOT NUMBERS
    // ──────────────────────────────────────────────

    public function batches()
    {
        return view('app.master-data.batches', [
            'batches'  => BatchLot::where('business_id', $this->bid())
                ->with('product')
                ->orderBy('created_at', 'desc')
                ->get(),
            'products' => Product::where('business_id', $this->bid())->orderBy('name')->get(),
        ]);
    }

    public function storeBatch(Request $request)
    {
        $data = $request->validate([
            'product_id'     => ['nullable', 'exists:products,id'],
            'batch_no'       => ['required', 'string', 'max:100'],
            'lot_no'         => ['nullable', 'string', 'max:100'],
            'quantity'       => ['nullable', 'integer', 'min:0'],
            'manufactured_at'=> ['nullable', 'date'],
            'expires_at'     => ['nullable', 'date'],
            'notes'          => ['nullable', 'string'],
        ]);

        BatchLot::create(['business_id' => $this->bid()] + $data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateBatch(Request $request, BatchLot $batchLot)
    {
        $this->authorizeBusinessRecord($batchLot);

        $data = $request->validate([
            'product_id'     => ['nullable', 'exists:products,id'],
            'batch_no'       => ['required', 'string', 'max:100'],
            'lot_no'         => ['nullable', 'string', 'max:100'],
            'quantity'       => ['nullable', 'integer', 'min:0'],
            'manufactured_at'=> ['nullable', 'date'],
            'expires_at'     => ['nullable', 'date'],
            'notes'          => ['nullable', 'string'],
        ]);

        $batchLot->update($data);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyBatch(BatchLot $batchLot)
    {
        $this->authorizeBusinessRecord($batchLot);
        $batchLot->delete();

        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // EXPIRED PRODUCTS (derived view of batch_lots)
    // ──────────────────────────────────────────────

    public function expired(Request $request)
    {
        $threshold = (int) $request->query('days', 30);

        $expiredBatches = BatchLot::where('business_id', $this->bid())
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->with('product')
            ->orderBy('expires_at')
            ->get();

        $expiringSoon = BatchLot::where('business_id', $this->bid())
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($threshold)])
            ->with('product')
            ->orderBy('expires_at')
            ->get();

        return view('app.master-data.expired', compact('expiredBatches', 'expiringSoon', 'threshold'));
    }

    // ──────────────────────────────────────────────
    // BIN LOCATIONS
    // ──────────────────────────────────────────────

    public function binLocations()
    {
        return view('app.master-data.bin-locations', [
            'binLocations' => BinLocation::where('business_id', $this->bid())
                ->with('warehouse')
                ->orderBy('code')
                ->get(),
            'warehouses' => Warehouse::where('business_id', $this->bid())->orderBy('name')->get(),
        ]);
    }

    public function storeBinLocation(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'code'         => ['required', 'string', 'max:50'],
            'aisle'        => ['nullable', 'string', 'max:50'],
            'rack'         => ['nullable', 'string', 'max:50'],
            'level'        => ['nullable', 'string', 'max:50'],
            'bin'          => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string'],
        ]);

        BinLocation::create(['business_id' => $this->bid()] + $data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateBinLocation(Request $request, BinLocation $binLocation)
    {
        $this->authorizeBusinessRecord($binLocation);

        $data = $request->validate([
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'code'         => ['required', 'string', 'max:50'],
            'aisle'        => ['nullable', 'string', 'max:50'],
            'rack'         => ['nullable', 'string', 'max:50'],
            'level'        => ['nullable', 'string', 'max:50'],
            'bin'          => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string'],
        ]);

        $binLocation->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyBinLocation(BinLocation $binLocation)
    {
        $this->authorizeBusinessRecord($binLocation);
        $binLocation->delete();

        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // EXCEL EXPORT / IMPORT
    // ──────────────────────────────────────────────

    private function xlDownload(string $filename, array $headings, array $rows): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new ReportExport($headings, $rows, $filename), $filename . '.xlsx');
    }

    private function xlImport(Request $request, callable $handler): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);
        Excel::import(new GenericImport($handler), $request->file('file'));
        return back()->with('status', __('messages.saved'));
    }

    private function boolFromRow(mixed $value, bool $default = true): bool
    {
        if ($value === null || $value === '') return $default;
        return in_array(strtolower((string) $value), ['1', 'yes', 'true', 'active', 'ya']);
    }

    // — Categories —
    public function exportCategories()
    {
        $rows = Category::where('business_id', $this->bid())->orderBy('name')->get()
            ->map(fn ($c) => [$c->name, $c->code, $c->description, $c->is_active ? 'Yes' : 'No'])->toArray();
        return $this->xlDownload('Categories', ['Name', 'Code', 'Description', 'Is Active'], $rows);
    }

    public function importCategories(Request $request)
    {
        $bid = $this->bid();
        return $this->xlImport($request, function ($row) use ($bid) {
            $name = trim($row['name'] ?? '');
            if (!$name) return;
            Category::updateOrCreate(
                ['business_id' => $bid, 'name' => $name],
                ['code' => $row['code'] ?? null, 'description' => $row['description'] ?? null,
                 'is_active' => $this->boolFromRow($row['is_active'] ?? null)]
            );
        });
    }

    // — Brands —
    public function exportBrands()
    {
        $rows = Brand::where('business_id', $this->bid())->orderBy('name')->get()
            ->map(fn ($b) => [$b->name, $b->code, $b->description, $b->is_active ? 'Yes' : 'No'])->toArray();
        return $this->xlDownload('Brands', ['Name', 'Code', 'Description', 'Is Active'], $rows);
    }

    public function importBrands(Request $request)
    {
        $bid = $this->bid();
        return $this->xlImport($request, function ($row) use ($bid) {
            $name = trim($row['name'] ?? '');
            if (!$name) return;
            Brand::updateOrCreate(
                ['business_id' => $bid, 'name' => $name],
                ['code' => $row['code'] ?? null, 'description' => $row['description'] ?? null,
                 'is_active' => $this->boolFromRow($row['is_active'] ?? null)]
            );
        });
    }

    // — Suppliers —
    public function exportSuppliers()
    {
        $rows = Supplier::where('business_id', $this->bid())->orderBy('name')->get()
            ->map(fn ($s) => [$s->name, $s->code, $s->contact_person, $s->phone, $s->email, $s->address, $s->is_active ? 'Yes' : 'No'])->toArray();
        return $this->xlDownload('Suppliers', ['Name', 'Code', 'Contact Person', 'Phone', 'Email', 'Address', 'Is Active'], $rows);
    }

    public function importSuppliers(Request $request)
    {
        $bid = $this->bid();
        return $this->xlImport($request, function ($row) use ($bid) {
            $name = trim($row['name'] ?? '');
            if (!$name) return;
            Supplier::updateOrCreate(
                ['business_id' => $bid, 'name' => $name],
                ['code' => $row['code'] ?? null, 'contact_person' => $row['contact_person'] ?? null,
                 'phone' => $row['phone'] ?? null, 'email' => $row['email'] ?? null,
                 'address' => $row['address'] ?? null,
                 'is_active' => $this->boolFromRow($row['is_active'] ?? null)]
            );
        });
    }

    // — Customers —
    public function exportCustomers()
    {
        $rows = Customer::where('business_id', $this->bid())->orderBy('name')->get()
            ->map(fn ($c) => [$c->name, $c->code, $c->contact_person, $c->phone, $c->email, $c->address, $c->is_active ? 'Yes' : 'No'])->toArray();
        return $this->xlDownload('Customers', ['Name', 'Code', 'Contact Person', 'Phone', 'Email', 'Address', 'Is Active'], $rows);
    }

    public function importCustomers(Request $request)
    {
        $bid = $this->bid();
        return $this->xlImport($request, function ($row) use ($bid) {
            $name = trim($row['name'] ?? $row['customer_name'] ?? '');
            if (!$name) return;
            Customer::updateOrCreate(
                ['business_id' => $bid, 'name' => $name],
                ['code' => $row['code'] ?? null, 'contact_person' => $row['contact_person'] ?? null,
                 'phone' => $row['phone'] ?? null, 'email' => $row['email'] ?? null,
                 'address' => $row['address'] ?? null,
                 'is_active' => $this->boolFromRow($row['is_active'] ?? null)]
            );
        });
    }

    // — Units —
    public function exportUnits()
    {
        $rows = Unit::where('business_id', $this->bid())->orderBy('name')->get()
            ->map(fn ($u) => [$u->name, $u->symbol, $u->description, $u->is_active ? 'Yes' : 'No'])->toArray();
        return $this->xlDownload('Units', ['Name', 'Symbol', 'Description', 'Is Active'], $rows);
    }

    public function importUnits(Request $request)
    {
        $bid = $this->bid();
        return $this->xlImport($request, function ($row) use ($bid) {
            $name = trim($row['name'] ?? '');
            if (!$name || empty($row['symbol'])) return;
            Unit::updateOrCreate(
                ['business_id' => $bid, 'name' => $name],
                ['symbol' => trim($row['symbol']), 'description' => $row['description'] ?? null,
                 'is_active' => $this->boolFromRow($row['is_active'] ?? null)]
            );
        });
    }

    // — Barcodes (export only — product FK makes import complex) —
    public function exportBarcodes()
    {
        $rows = ProductBarcode::where('business_id', $this->bid())->with('product')
            ->orderBy('created_at', 'desc')->get()
            ->map(fn ($b) => [$b->product?->name, $b->barcode_type, $b->value, $b->is_primary ? 'Yes' : 'No'])->toArray();
        return $this->xlDownload('Barcodes', ['Product', 'Type', 'Value', 'Is Primary'], $rows);
    }

    // — Batches —
    public function exportBatches()
    {
        $rows = BatchLot::where('business_id', $this->bid())->with('product')
            ->orderBy('created_at', 'desc')->get()
            ->map(fn ($b) => [$b->product?->name, $b->batch_no, $b->lot_no, $b->quantity,
                $b->manufactured_at, $b->expires_at, $b->notes])->toArray();
        return $this->xlDownload('Batches', ['Product', 'Batch No', 'Lot No', 'Quantity', 'Manufactured At', 'Expires At', 'Notes'], $rows);
    }

    public function importBatches(Request $request)
    {
        $bid = $this->bid();
        return $this->xlImport($request, function ($row) use ($bid) {
            $batchNo = trim($row['batch_no'] ?? '');
            if (!$batchNo) return;
            $product = null;
            if (!empty($row['product'])) {
                $product = Product::where('business_id', $bid)->where('name', trim($row['product']))->first();
            }
            BatchLot::updateOrCreate(
                ['business_id' => $bid, 'batch_no' => $batchNo],
                ['product_id' => $product?->id, 'lot_no' => $row['lot_no'] ?? null,
                 'quantity' => isset($row['quantity']) ? (int) $row['quantity'] : null,
                 'manufactured_at' => $row['manufactured_at'] ?: null,
                 'expires_at' => $row['expires_at'] ?: null,
                 'notes' => $row['notes'] ?? null]
            );
        });
    }

    // — Bin Locations —
    public function exportBinLocations()
    {
        $rows = BinLocation::where('business_id', $this->bid())->with('warehouse')
            ->orderBy('code')->get()
            ->map(fn ($b) => [$b->warehouse?->name, $b->code, $b->aisle, $b->rack, $b->level, $b->bin, $b->description, $b->is_active ? 'Yes' : 'No'])->toArray();
        return $this->xlDownload('Bin Locations', ['Warehouse', 'Code', 'Aisle', 'Rack', 'Level', 'Bin', 'Description', 'Is Active'], $rows);
    }

    public function importBinLocations(Request $request)
    {
        $bid = $this->bid();
        return $this->xlImport($request, function ($row) use ($bid) {
            $code = trim($row['code'] ?? '');
            if (!$code) return;
            $warehouse = null;
            if (!empty($row['warehouse'])) {
                $warehouse = Warehouse::where('business_id', $bid)->where('name', trim($row['warehouse']))->first();
            }
            BinLocation::updateOrCreate(
                ['business_id' => $bid, 'code' => $code],
                ['warehouse_id' => $warehouse?->id, 'aisle' => $row['aisle'] ?? null,
                 'rack' => $row['rack'] ?? null, 'level' => $row['level'] ?? null,
                 'bin' => $row['bin'] ?? null, 'description' => $row['description'] ?? null,
                 'is_active' => $this->boolFromRow($row['is_active'] ?? null)]
            );
        });
    }

    // — Expired (export only — derived view) —
    public function exportExpired(Request $request)
    {
        $threshold = (int) $request->query('days', 30);
        $expired = BatchLot::where('business_id', $this->bid())->whereNotNull('expires_at')
            ->where('expires_at', '<', now())->with('product')->orderBy('expires_at')->get();
        $soon = BatchLot::where('business_id', $this->bid())->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($threshold)])->with('product')->orderBy('expires_at')->get();
        $rows = $expired->merge($soon)
            ->map(fn ($b) => [$b->product?->name, $b->batch_no, $b->quantity, $b->expires_at, $b->expires_at < now() ? 'Expired' : 'Expiring Soon'])->toArray();
        return $this->xlDownload('Expired Batches', ['Product', 'Batch No', 'Quantity', 'Expires At', 'Status'], $rows);
    }
}
