<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        return view('app.billing', [
            'invoices' => Invoice::with(['bankAccount', 'subscriptionPackage'])->where('business_id', Auth::user()->business_id)->latest()->get(),
        ]);
    }
}
