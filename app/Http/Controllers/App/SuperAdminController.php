<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Business;
use App\Models\CmsPage;
use App\Models\Invoice;
use App\Models\PlatformNotification;
use App\Models\PromoBanner;
use App\Models\SupportMessage;
use App\Models\SupportRoom;
use App\Models\SubscriptionPackage;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        return view('app.super-admin.dashboard', [
            'stats' => [
                'customers' => Business::count(),
                'packages' => SubscriptionPackage::count(),
                'invoices' => Invoice::count(),
                'open_support' => SupportRoom::where('status', 'open')->count(),
                'notifications' => PlatformNotification::count(),
            ],
            'rooms' => SupportRoom::with('business')->latest()->take(5)->get(),
        ]);
    }

    public function customers()
    {
        return view('app.super-admin.customers', [
            'customers' => Business::withCount(['companyProfile'])->latest()->get(),
            'invoices' => Invoice::with('business')->latest()->take(10)->get(),
        ]);
    }

    public function commerce()
    {
        return view('app.super-admin.commerce', [
            'packages' => SubscriptionPackage::latest()->get(),
            'banners' => PromoBanner::latest()->get(),
        ]);
    }

    public function cms()
    {
        return view('app.super-admin.cms', [
            'pages' => CmsPage::latest()->get(),
        ]);
    }

    public function storeCms(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'section' => ['required', 'string', 'max:80'],
            'body' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        CmsPage::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . Str::lower(Str::random(4)),
            'section' => $data['section'],
            'body' => $data['body'] ?? null,
            'is_published' => $request->boolean('is_published', true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateCms(Request $request, CmsPage $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'section' => ['required', 'string', 'max:80'],
            'body' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $page->update([
            'title' => $data['title'],
            'section' => $data['section'],
            'body' => $data['body'] ?? null,
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function notifications()
    {
        return view('app.super-admin.notifications', [
            'customers' => Business::orderBy('name')->get(),
            'notifications' => PlatformNotification::latest()->get(),
        ]);
    }

    public function billingPayments()
    {
        return view('app.super-admin.billing-payments', [
            'invoices' => Invoice::with(['business', 'bankAccount'])->latest()->get(),
        ]);
    }

    public function updateBillingPayment(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'status' => ['required', 'in:unpaid,paid,pending'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        $invoice->update([
            'status' => $data['status'],
            'payment_notes' => $data['payment_notes'] ?? $invoice->payment_notes,
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function bankAccounts()
    {
        return view('app.super-admin.bank-accounts', [
            'bankAccounts' => BankAccount::latest()->get(),
        ]);
    }

    public function storeBankAccount(Request $request)
    {
        BankAccount::create($request->validate([
            'bank_name' => ['required', 'string', 'max:120'],
            'account_name' => ['required', 'string', 'max:160'],
            'account_number' => ['required', 'string', 'max:80'],
            'branch' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('status', __('messages.saved'));
    }

    public function toggleBankAccount(BankAccount $bankAccount)
    {
        $bankAccount->update(['is_active' => ! $bankAccount->is_active]);

        return back()->with('status', __('messages.saved'));
    }

    public function storeNotification(Request $request)
    {
        $data = $request->validate([
            'audience' => ['required', 'in:all,specific'],
            'business_id' => ['nullable', 'exists:businesses,id'],
            'title' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string'],
            'channel' => ['required', 'string', 'max:60'],
        ]);

        PlatformNotification::create([
            'audience' => $data['audience'],
            'business_id' => $data['audience'] === 'specific' ? $data['business_id'] : null,
            'title' => $data['title'],
            'message' => $data['message'],
            'channel' => $data['channel'],
            'sent_at' => now(),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function supportRooms()
    {
        return view('app.super-admin.support-rooms', [
            'customers' => Business::orderBy('name')->get(),
            'agents' => User::whereIn('platform_role', ['customer_service', 'platform_admin', 'super_admin'])->orWhere('is_super_admin', true)->get(),
            'rooms' => SupportRoom::with(['business', 'messages'])->latest()->get(),
        ]);
    }

    public function storeSupportRoom(Request $request)
    {
        $data = $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'support_type' => ['required', 'in:billing,technical,onboarding,general'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string'],
        ]);

        $room = SupportRoom::create($data + ['status' => 'open']);
        SupportMessage::create([
            'support_room_id' => $room->id,
            'user_id' => Auth::id(),
            'message' => $data['message'],
            'is_staff_reply' => true,
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function replySupportRoom(Request $request, SupportRoom $room)
    {
        $data = $request->validate(['message' => ['required', 'string']]);

        SupportMessage::create([
            'support_room_id' => $room->id,
            'user_id' => Auth::id(),
            'message' => $data['message'],
            'is_staff_reply' => true,
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function closeSupportRoom(SupportRoom $room)
    {
        $room->update(['status' => 'closed']);

        return back()->with('status', __('messages.ticket_closed'));
    }

    public function reopenSupportRoom(SupportRoom $room)
    {
        $room->update(['status' => 'open']);

        return back()->with('status', __('messages.ticket_reopened'));
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['required', 'integer', 'min:0', 'max:95'],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'features' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'period_months' => ['nullable', 'array'],
            'period_months.*' => ['nullable', 'integer', 'min:1', 'max:120'],
            'period_discount' => ['nullable', 'array'],
            'period_discount.*' => ['nullable', 'integer', 'min:0', 'max:95'],
        ]);

        SubscriptionPackage::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(5)),
            'tagline' => $data['tagline'] ?? null,
            'price' => $data['price'],
            'discount_percent' => $data['discount_percent'],
            'billing_periods' => $this->parseBillingPeriods($request->input('period_months', []), $request->input('period_discount', [])),
            'trial_days' => $data['trial_days'],
            'features' => collect(explode("\n", $data['features'] ?? ''))->map(fn ($f) => trim($f))->filter()->values()->all(),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    public function updatePackage(Request $request, SubscriptionPackage $package)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['required', 'integer', 'min:0', 'max:95'],
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'features' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'period_months' => ['nullable', 'array'],
            'period_months.*' => ['nullable', 'integer', 'min:1', 'max:120'],
            'period_discount' => ['nullable', 'array'],
            'period_discount.*' => ['nullable', 'integer', 'min:0', 'max:95'],
        ]);

        $package->update([
            'name' => $data['name'],
            'tagline' => $data['tagline'] ?? null,
            'price' => $data['price'],
            'discount_percent' => $data['discount_percent'],
            'billing_periods' => $this->parseBillingPeriods($request->input('period_months', []), $request->input('period_discount', [])),
            'trial_days' => $data['trial_days'],
            'features' => collect(explode("\n", $data['features'] ?? ''))->map(fn ($f) => trim($f))->filter()->values()->all(),
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', __('messages.saved'));
    }

    private function parseBillingPeriods(array $months, array $discounts): array
    {
        $periods = [];
        foreach ($months as $i => $m) {
            $m = (int) $m;
            $d = (int) ($discounts[$i] ?? 0);
            if ($m > 0) {
                $periods[] = ['months' => $m, 'discount_percent' => $d];
            }
        }

        usort($periods, fn ($a, $b) => $a['months'] <=> $b['months']);

        return $periods;
    }

    public function togglePackage(SubscriptionPackage $package)
    {
        $package->update(['is_active' => ! $package->is_active]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyPackage(SubscriptionPackage $package)
    {
        $package->delete();

        return back()->with('status', __('messages.deleted'));
    }

    public function storeBanner(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:80'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'background' => ['required', 'string', 'max:40'],
            'image' => ['nullable', 'image', 'max:2048'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        PromoBanner::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('status', __('messages.saved'));
    }

    public function updateBanner(Request $request, PromoBanner $banner)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:80'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'background' => ['required', 'string', 'max:40'],
            'image' => ['nullable', 'image', 'max:2048'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('messages.saved'));
    }

    public function toggleBanner(PromoBanner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyBanner(PromoBanner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return back()->with('status', __('messages.deleted'));
    }

    public function testimonials()
    {
        return view('app.super-admin.testimonials', [
            'testimonials' => Testimonial::orderBy('sort_order')->orderByDesc('created_at')->get(),
        ]);
    }

    public function storeTestimonial(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'role'       => ['nullable', 'string', 'max:255'],
            'company'    => ['nullable', 'string', 'max:255'],
            'body'       => ['required', 'string', 'max:1000'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'is_active'  => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'avatar'     => ['nullable', 'image', 'max:1024'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('testimonials', 'public');
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        Testimonial::create($data);

        return back()->with('status', __('messages.saved'));
    }

    public function updateTestimonial(Request $request, Testimonial $testimonial)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'role'       => ['nullable', 'string', 'max:255'],
            'company'    => ['nullable', 'string', 'max:255'],
            'body'       => ['required', 'string', 'max:1000'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'is_active'  => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'avatar'     => ['nullable', 'image', 'max:1024'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('testimonials', 'public');
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $testimonial->update($data);

        return back()->with('status', __('messages.saved'));
    }

    public function destroyTestimonial(Testimonial $testimonial)
    {
        $testimonial->delete();

        return back()->with('status', __('messages.deleted'));
    }
}
