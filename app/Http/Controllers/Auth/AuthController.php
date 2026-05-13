<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\CompanyProfile;
use App\Models\LoginHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (request()->filled('redirect')) {
            $redirect = request('redirect');

            if (str_starts_with($redirect, url('/')) || str_starts_with($redirect, '/')) {
                session(['url.intended' => $redirect]);
            }
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Record failed login attempt if user exists
            $failedUser = User::where('email', $credentials['email'])->first();
            if ($failedUser) {
                LoginHistory::create([
                    'user_id'       => $failedUser->id,
                    'business_id'   => $failedUser->business_id,
                    'ip_address'    => $request->ip(),
                    'user_agent'    => $request->userAgent(),
                    'is_successful' => false,
                    'login_at'      => now(),
                ]);
            }
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();

        $loginRecord = LoginHistory::create([
            'user_id'       => Auth::id(),
            'business_id'   => Auth::user()->business_id,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'is_successful' => true,
            'login_at'      => now(),
        ]);
        session(['login_history_id' => $loginRecord->id]);

        ActivityLog::create([
            'business_id' => Auth::user()->business_id,
            'user_id'     => Auth::id(),
            'action'      => 'login',
            'description' => Auth::user()->name . ' logged in',
            'ip_address'  => $request->ip(),
        ]);

        if (Auth::user()->isPlatformStaff()) {
            return redirect()->intended(route('super-admin.dashboard'));
        }

        return redirect()->intended(route(Auth::user()->business?->onboarding_completed_at ? 'dashboard' : 'onboarding.show'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $business = Business::create([
            'name' => $data['company_name'],
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addMonth(),
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

        return redirect()->route('onboarding.show');
    }

    public function logout(Request $request)
    {
        $loginHistoryId = session('login_history_id');
        if ($loginHistoryId) {
            LoginHistory::where('id', $loginHistoryId)->whereNull('logout_at')->update(['logout_at' => now()]);
        }

        ActivityLog::create([
            'business_id' => Auth::user()?->business_id,
            'user_id'     => Auth::id(),
            'action'      => 'logout',
            'description' => Auth::user()?->name . ' logged out',
            'ip_address'  => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
