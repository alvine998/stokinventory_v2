<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        return view('app.company', ['company' => Auth::user()->business->companyProfile]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'call_center' => ['nullable', 'string', 'max:100'],
            'field'       => ['nullable', 'string', 'max:150'],
            'address'     => ['nullable', 'string'],
            'about'       => ['nullable', 'string'],
            'vision'      => ['nullable', 'string'],
            'mission'     => ['nullable', 'string'],
            'organization'=> ['nullable', 'string'],
            'why_us'      => ['nullable', 'string'],
        ]);

        $profile = Auth::user()->business->companyProfile;

        if ($request->hasFile('logo')) {
            if ($profile && $profile->logo) {
                Storage::disk('public')->delete($profile->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        } else {
            unset($data['logo']);
        }

        Auth::user()->business->companyProfile()->updateOrCreate(
            ['business_id' => Auth::user()->business_id],
            $data
        );

        return back()->with('status', __('messages.saved'));
    }
}
