<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Business;
use App\Models\CompanyProfile;
use App\Models\DiscountCode;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class OrderController extends Controller
{
    public function show(SubscriptionPackage $package)
    {
        abort_unless($package->is_active, 404);

        return view('order.show', [
            'package' => $package,
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
        ]);
    }

    public function store(Request $request, SubscriptionPackage $package)
    {
        abort_unless($package->is_active, 404);

        $rules = [
            'billing_months'    => ['nullable', 'integer', 'min:1'],
            'bank_account_id'   => ['required', 'exists:bank_accounts,id'],
            'discount_code'     => ['nullable', 'string', 'max:80'],
            'payment_notes'     => ['nullable', 'string'],
            'payment_evidence'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if (! Auth::check()) {
            $rules += [
                'name' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ];
        }

        $data = $request->validate($rules);
        $billingMonths = isset($data['billing_months']) ? (int) $data['billing_months'] : null;
        $user = Auth::user();

        if (! $user) {
            $business = Business::create([
                'name' => $data['company_name'],
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays($package->trial_days),
            ]);

            CompanyProfile::create([
                'business_id' => $business->id,
                'name' => $data['company_name'],
            ]);

            $ownerRole = Role::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'owner'],
                ['name' => 'Owner', 'permissions' => Role::defaultOwnerPermissions()]
            );

            $user = User::create([
                'business_id' => $business->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->roles()->attach($ownerRole);
            Auth::login($user);
        }

        $amount = (float) $package->price;
        $packageTotal = $package->priceForMonths($billingMonths);
        $discountCode = null;
        $codeDiscount = 0;

        if (! empty($data['discount_code'])) {
            $discountCode = DiscountCode::where('code', strtoupper($data['discount_code']))->first();

            if (! $discountCode || ! $discountCode->appliesTo($package)) {
                return back()->withErrors(['discount_code' => __('messages.invalid_discount_code')])->withInput();
            }

            $codeDiscount = $discountCode->amountFor($packageTotal);
        }

        $total = max(0, $packageTotal - $codeDiscount);

        $evidencePath = null;
        if ($request->hasFile('payment_evidence')) {
            $evidencePath = $request->file('payment_evidence')->store('payment-evidence', 'public');
        }

        Invoice::create([
            'business_id' => $user->business_id,
            'subscription_package_id' => $package->id,
            'billing_months' => $billingMonths,
            'bank_account_id' => $data['bank_account_id'],
            'discount_code_id' => $discountCode?->id,
            'discount_code' => $discountCode?->code,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'invoice_no' => 'INV-' . now()->format('YmdHis') . '-' . $user->id,
            'amount' => $amount,
            'discount_amount' => $amount - $total,
            'total_amount' => $total,
            'status' => 'pending',
            'payment_method'    => 'bank_transfer',
            'payment_notes'     => $data['payment_notes'] ?? null,
            'payment_evidence'  => $evidencePath,
            'issued_at'         => now()->toDateString(),
            'due_at'            => now()->addDays(3)->toDateString(),
        ]);

        // New users go to onboarding; returning users who already completed go to dashboard
        if (! $user->business->onboarding_completed_at) {
            return redirect()->route('onboarding.show')->with('status', __('messages.order_created'));
        }

        return redirect()->route('dashboard')->with('status', __('messages.order_created'));
    }
}
