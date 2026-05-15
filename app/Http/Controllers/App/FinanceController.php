<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Exports\ReportExport;
use App\Imports\GenericImport;
use App\Models\AccountingIntegration;
use App\Models\ChartOfAccount;
use App\Models\HppConfig;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\TaxConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    private function bid(): int
    {
        return Auth::user()->business_id;
    }

    private function nextJournalNo(): string
    {
        $count = JournalEntry::where('business_id', $this->bid())->count() + 1;
        return 'JRN-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    // ─── HPP (Cost of Goods) ──────────────────────────────────────────────

    public function hpp()
    {
        $config   = HppConfig::forBusiness($this->bid());
        $products = Product::where('business_id', $this->bid())
            ->orderBy('name')
            ->paginate(30);

        // HPP summary: cost_price × current_stock per product
        return view('app.finance.hpp', compact('config', 'products'));
    }

    public function updateHpp(Request $request)
    {
        $data = $request->validate([
            'method'  => ['required', 'in:fifo,weighted_average,lifo'],
            'is_auto' => ['nullable', 'boolean'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $config = HppConfig::forBusiness($this->bid());
        $config->update([
            'method'  => $data['method'],
            'is_auto' => (bool) ($data['is_auto'] ?? false),
            'notes'   => $data['notes'] ?? null,
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateProductCost(Request $request, Product $product)
    {
        abort_if($product->business_id !== $this->bid(), 403);
        $data = $request->validate(['cost_price' => ['required', 'numeric', 'min:0']]);
        $product->update(['cost_price' => $data['cost_price']]);
        return back()->with('status', __('messages.saved'));
    }

    // ─── Jurnal Otomatis ──────────────────────────────────────────────────

    public function journals()
    {
        $journals = JournalEntry::where('business_id', $this->bid())
            ->with(['lines.account', 'creator'])
            ->latest('entry_date')
            ->paginate(30);

        $accounts = ChartOfAccount::where('business_id', $this->bid())
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('app.finance.journals', compact('journals', 'accounts'));
    }

    public function storeJournal(Request $request)
    {
        $data = $request->validate([
            'description'           => ['required', 'string', 'max:255'],
            'entry_date'            => ['required', 'date'],
            'reference_no'         => ['nullable', 'string', 'max:100'],
            'lines'                 => ['required', 'array', 'min:2'],
            'lines.*.account_id'   => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.description'  => ['nullable', 'string', 'max:255'],
            'lines.*.debit'        => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit'       => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalDebit  = collect($data['lines'])->sum(fn($l) => (float)($l['debit'] ?? 0));
        $totalCredit = collect($data['lines'])->sum(fn($l) => (float)($l['credit'] ?? 0));

        if (abs($totalDebit - $totalCredit) > 0.01 || $totalDebit <= 0) {
            return back()->withErrors(['lines' => __('messages.journal_unbalanced')])->withInput();
        }

        DB::transaction(function () use ($data) {
            $entry = JournalEntry::create([
                'business_id'    => $this->bid(),
                'entry_no'       => $this->nextJournalNo(),
                'reference_no'   => $data['reference_no'] ?? null,
                'reference_type' => 'manual',
                'description'    => $data['description'],
                'entry_date'     => $data['entry_date'],
                'is_auto'        => false,
                'created_by'     => Auth::id(),
            ]);

            foreach ($data['lines'] as $line) {
                $debit  = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);
                if ($debit <= 0 && $credit <= 0) {
                    continue;
                }
                $entry->lines()->create([
                    'account_id'  => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit'       => $debit,
                    'credit'      => $credit,
                ]);
            }
        });

        return back()->with('status', __('messages.saved'));
    }

    public function destroyJournal(JournalEntry $journalEntry)
    {
        abort_if($journalEntry->business_id !== $this->bid(), 403);
        abort_if($journalEntry->is_auto, 403, 'Cannot delete auto-generated journal entries.');
        $journalEntry->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ─── Chart of Accounts ────────────────────────────────────────────────

    public function accounts()
    {
        // Seed defaults if empty
        $existing = ChartOfAccount::where('business_id', $this->bid())->count();
        if ($existing === 0) {
            ChartOfAccount::seedDefaults($this->bid());
        }

        $accounts = ChartOfAccount::where('business_id', $this->bid())
            ->orderBy('code')
            ->get();

        return view('app.finance.accounts', compact('accounts'));
    }

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'code'      => ['required', 'string', 'max:20'],
            'name'      => ['required', 'string', 'max:150'],
            'type'      => ['required', 'in:asset,liability,equity,revenue,cogs,expense'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ChartOfAccount::create([
            'business_id' => $this->bid(),
            'code'        => $data['code'],
            'name'        => $data['name'],
            'type'        => $data['type'],
            'parent_id'   => $data['parent_id'] ?? null,
            'is_system'   => false,
            'is_active'   => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateAccount(Request $request, ChartOfAccount $chartOfAccount)
    {
        abort_if($chartOfAccount->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $chartOfAccount->update([
            'name'      => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyAccount(ChartOfAccount $chartOfAccount)
    {
        abort_if($chartOfAccount->business_id !== $this->bid(), 403);
        abort_if($chartOfAccount->is_system, 403, 'Cannot delete system accounts.');
        $chartOfAccount->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ─── Accounting Integration ───────────────────────────────────────────

    public function integration()
    {
        $integration = AccountingIntegration::where('business_id', $this->bid())->first();
        return view('app.finance.integration', compact('integration'));
    }

    public function saveIntegration(Request $request)
    {
        $data = $request->validate([
            'provider'  => ['required', 'in:accurate,jurnal,zahir,beecloud,custom'],
            'api_key'   => ['nullable', 'string', 'max:500'],
            'endpoint'  => ['nullable', 'url', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        AccountingIntegration::updateOrCreate(
            ['business_id' => $this->bid()],
            [
                'provider'  => $data['provider'],
                // Only overwrite api_key if user submitted a non-masked value
                'api_key'   => (!empty($data['api_key']) && !str_contains($data['api_key'], '****'))
                                ? $data['api_key']
                                : AccountingIntegration::where('business_id', $this->bid())->value('api_key'),
                'endpoint'  => $data['endpoint'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]
        );

        return back()->with('status', __('messages.saved'));
    }

    // ─── Cashflow Inventory ───────────────────────────────────────────────

    public function cashflow()
    {
        $bid = $this->bid();

        // Inflows: sum of paid sales invoices per month (last 12 months)
        $inflows = SalesInvoice::where('business_id', $bid)
            ->where('issued_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(issued_at, '%Y-%m') as period, SUM(paid_amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        // Outflows: stock-in movements (purchases) valued at cost_price × qty
        $outflows = StockMovement::where('stock_movements.business_id', $bid)
            ->where('stock_movements.type', 'in')
            ->where('stock_movements.moved_at', '>=', now()->subMonths(11)->startOfMonth())
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->selectRaw("DATE_FORMAT(stock_movements.moved_at, '%Y-%m') as period, SUM(stock_movements.quantity * products.cost_price) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        // Build 12-month period array
        $periods = [];
        for ($i = 11; $i >= 0; $i--) {
            $periods[] = now()->subMonths($i)->format('Y-m');
        }

        return view('app.finance.cashflow', compact('periods', 'inflows', 'outflows'));
    }

    // ─── Inventory Valuation ──────────────────────────────────────────────

    public function valuation()
    {
        $products = Product::where('business_id', $this->bid())
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'product'       => $p,
                'cost_price'    => (float) $p->cost_price,
                'current_stock' => (float) $p->current_stock,
                'value'         => round((float) $p->cost_price * (float) $p->current_stock, 2),
            ]);

        $totalValue = $products->sum('value');

        return view('app.finance.valuation', compact('products', 'totalValue'));
    }

    // ─── Laporan Laba Rugi (P&L) ──────────────────────────────────────────

    public function profitLoss(Request $request)
    {
        $bid       = $this->bid();
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        // Revenue: sum of paid sales invoice amounts in period
        $revenue = SalesInvoice::where('business_id', $bid)
            ->whereBetween(DB::raw('DATE(issued_at)'), [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled'])
            ->sum('amount');

        // COGS: stock-out movements × cost_price in period
        $cogs = StockMovement::where('stock_movements.business_id', $bid)
            ->where('stock_movements.type', 'out')
            ->whereBetween(DB::raw('DATE(stock_movements.moved_at)'), [$startDate, $endDate])
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_movements.quantity * products.cost_price'));

        // Expenses: journal debit lines on expense accounts in period
        $expenses = JournalEntryLine::whereHas('journalEntry', function ($q) use ($bid, $startDate, $endDate) {
            $q->where('business_id', $bid)
              ->whereBetween('entry_date', [$startDate, $endDate]);
        })
        ->whereHas('account', fn($q) => $q->where('type', 'expense'))
        ->sum('debit');

        $grossProfit = $revenue - $cogs;
        $netProfit   = $grossProfit - $expenses;

        return view('app.finance.profit-loss', compact(
            'revenue', 'cogs', 'expenses', 'grossProfit', 'netProfit', 'startDate', 'endDate'
        ));
    }

    // ─── Pajak PPN ───────────────────────────────────────────────────────

    public function tax()
    {
        $taxes = TaxConfig::where('business_id', $this->bid())->orderBy('name')->get();
        return view('app.finance.tax', compact('taxes'));
    }

    public function storeTax(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'code'         => ['required', 'string', 'max:20'],
            'rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_type'     => ['required', 'in:ppn,pph,other'],
            'is_inclusive' => ['nullable', 'boolean'],
            'applies_to'   => ['required', 'in:sales,purchases,all'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        TaxConfig::create([
            'business_id'  => $this->bid(),
            'name'         => $data['name'],
            'code'         => $data['code'],
            'rate_percent' => $data['rate_percent'],
            'tax_type'     => $data['tax_type'],
            'is_inclusive' => (bool) ($data['is_inclusive'] ?? false),
            'applies_to'   => $data['applies_to'],
            'is_active'    => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateTax(Request $request, TaxConfig $taxConfig)
    {
        abort_if($taxConfig->business_id !== $this->bid(), 403);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'code'         => ['required', 'string', 'max:20'],
            'rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_type'     => ['required', 'in:ppn,pph,other'],
            'is_inclusive' => ['nullable', 'boolean'],
            'applies_to'   => ['required', 'in:sales,purchases,all'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        $taxConfig->update([
            'name'         => $data['name'],
            'code'         => $data['code'],
            'rate_percent' => $data['rate_percent'],
            'tax_type'     => $data['tax_type'],
            'is_inclusive' => (bool) ($data['is_inclusive'] ?? false),
            'applies_to'   => $data['applies_to'],
            'is_active'    => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyTax(TaxConfig $taxConfig)
    {
        abort_if($taxConfig->business_id !== $this->bid(), 403);
        $taxConfig->delete();
        return back()->with('status', __('messages.deleted'));
    }

    // ──────────────────────────────────────────────
    // EXCEL EXPORT / IMPORT
    // ──────────────────────────────────────────────

    // — Chart of Accounts —
    public function exportAccounts()
    {
        $rows = ChartOfAccount::where('business_id', $this->bid())->orderBy('code')->get()
            ->map(fn ($a) => [$a->code, $a->name, $a->type, $a->is_active ? 'Yes' : 'No'])->toArray();
        return Excel::download(new ReportExport(['Code', 'Name', 'Type', 'Is Active'], $rows, 'Chart of Accounts'), 'accounts.xlsx');
    }

    public function importAccounts(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);
        $bid = $this->bid();
        $validTypes = ['asset', 'liability', 'equity', 'revenue', 'cogs', 'expense'];
        Excel::import(new GenericImport(function ($row) use ($bid, $validTypes) {
            $code = trim($row['code'] ?? '');
            $name = trim($row['name'] ?? '');
            $type = strtolower(trim($row['type'] ?? ''));
            if (!$code || !$name || !in_array($type, $validTypes)) return;
            ChartOfAccount::updateOrCreate(
                ['business_id' => $bid, 'code' => $code],
                ['name' => $name, 'type' => $type, 'is_system' => false,
                 'is_active' => in_array(strtolower((string)($row['is_active'] ?? 'yes')), ['1', 'yes', 'true', 'ya'])]
            );
        }), $request->file('file'));
        return back()->with('status', __('messages.saved'));
    }

    // — Journals (export only — complex double-entry) —
    public function exportJournals()
    {
        $rows = JournalEntry::where('business_id', $this->bid())
            ->with(['lines.account', 'creator'])->latest('entry_date')->get()
            ->flatMap(fn ($j) => $j->lines->map(fn ($l) => [
                $j->entry_no, $j->entry_date, $j->description, $j->creator?->name,
                $l->account?->code, $l->account?->name, $l->debit, $l->credit, $l->notes,
            ]))->toArray();
        return Excel::download(new ReportExport(['Entry No', 'Date', 'Description', 'Created By', 'Account Code', 'Account Name', 'Debit', 'Credit', 'Notes'], $rows, 'Journal Entries'), 'journals.xlsx');
    }

    // — Tax —
    public function exportTax()
    {
        $rows = TaxConfig::where('business_id', $this->bid())->orderBy('name')->get()
            ->map(fn ($t) => [$t->name, $t->code, $t->rate_percent, $t->tax_type, $t->is_inclusive ? 'Yes' : 'No', $t->applies_to, $t->is_active ? 'Yes' : 'No'])->toArray();
        return Excel::download(new ReportExport(['Name', 'Code', 'Rate %', 'Tax Type', 'Is Inclusive', 'Applies To', 'Is Active'], $rows, 'Tax'), 'tax.xlsx');
    }

    public function importTax(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120']]);
        $bid = $this->bid();
        Excel::import(new GenericImport(function ($row) use ($bid) {
            $name = trim($row['name'] ?? '');
            $code = trim($row['code'] ?? '');
            if (!$name || !$code) return;
            $taxType  = strtolower(trim($row['tax_type'] ?? 'other'));
            $appliesTo = strtolower(trim($row['applies_to'] ?? 'all'));
            if (!in_array($taxType, ['ppn', 'pph', 'other'])) $taxType = 'other';
            if (!in_array($appliesTo, ['sales', 'purchases', 'all'])) $appliesTo = 'all';
            TaxConfig::updateOrCreate(
                ['business_id' => $bid, 'code' => $code],
                ['name' => $name, 'rate_percent' => (float)($row['rate_percent'] ?? 0),
                 'tax_type' => $taxType,
                 'is_inclusive' => in_array(strtolower((string)($row['is_inclusive'] ?? 'no')), ['1', 'yes', 'true', 'ya']),
                 'applies_to' => $appliesTo,
                 'is_active'  => in_array(strtolower((string)($row['is_active'] ?? 'yes')), ['1', 'yes', 'true', 'ya'])]
            );
        }), $request->file('file'));
        return back()->with('status', __('messages.saved'));
    }

    // — Valuation (export only — computed) —
    public function exportValuation()
    {
        $rows = Product::where('business_id', $this->bid())->orderBy('name')->get()
            ->map(fn ($p) => [$p->name, $p->sku, $p->category, $p->current_stock, $p->cost_price, $p->current_stock * $p->cost_price])->toArray();
        return Excel::download(new ReportExport(['Product', 'SKU', 'Category', 'Stock', 'Cost Price', 'Value'], $rows, 'Stock Valuation'), 'valuation.xlsx');
    }
}
