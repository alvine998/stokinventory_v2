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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
