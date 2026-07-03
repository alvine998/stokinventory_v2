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
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

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

        $business = Auth::user()->business;
        if ($business && $business->isTrialExpired()) {
            if (! $business->trial_expired_at) {
                $business->update(['trial_expired_at' => now()]);
            }

            return redirect()->route('expired');
        }

        return redirect()->intended(route(Auth::user()->business?->onboarding_completed_at ? 'dashboard' : 'onboarding.show'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
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

    // ── Forgot Password ───────────────────────────────────────────────────────

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __('messages.reset_link_sent'));
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __('messages.password_reset_success'));
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    // ── Logout ────────────────────────────────────────────────────────────────

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
