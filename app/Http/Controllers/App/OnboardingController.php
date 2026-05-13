<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function show()
    {
        return view('app.onboarding');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'industry' => ['required', 'string', 'max:120'],
            'business_size' => ['required', 'string', 'max:120'],
            'inventory_goal' => ['required', 'string', 'max:255'],
            'has_multiple_locations' => ['nullable', 'boolean'],
        ]);

        Auth::user()->business->update([
            'industry' => $data['industry'],
            'business_size' => $data['business_size'],
            'inventory_goal' => $data['inventory_goal'],
            'has_multiple_locations' => $request->boolean('has_multiple_locations'),
            'onboarding_completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', __('messages.onboarding_done'));
    }
}
