<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Support\Facades\Auth;

class ExpiredController extends Controller
{
    public function show()
    {
        $business = Auth::user()->business;
        $packages = SubscriptionPackage::where('is_active', true)->orderByDesc('is_featured')->get();

        $daysUntilDeletion = $business ? $business->daysUntilDataDeletion() : 0;

        return view('app.expired', compact('business', 'packages', 'daysUntilDeletion'));
    }
}
