<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\SubscriptionPackage;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function landing()
    {
        return view('welcome', [
            'promoBanners' => PromoBanner::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->latest()
                ->take(2)
                ->get(),
            'packages' => SubscriptionPackage::where('is_active', true)
                ->orderByDesc('is_featured')
                ->orderBy('price')
                ->get(),
            'aboutUs' => CmsPage::where('slug', 'landing-about-us')
                ->where('is_published', true)
                ->first(),
            'testimonials' => Testimonial::where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get(),
            'waNumber'  => config('services.whatsapp.number'),
            'waMessage' => config('services.whatsapp.message'),
        ]);
    }

    public function dashboard()
    {
        $businessId = Auth::user()->business_id;

        // Stock movements by type (last 30 days)
        $movementTypes = StockMovement::where('business_id', $businessId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        // Products by warehouse
        $productsByWarehouse = Product::where('business_id', $businessId)
            ->selectRaw('COUNT(*) as count')
            ->first()
            ->count ?? 0;

        // Stock movements trend (last 7 days)
        $movementsTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = StockMovement::where('business_id', $businessId)
                ->whereDate('created_at', $date)
                ->count();
            $movementsTrend[now()->subDays($i)->format('M d')] = $count;
        }

        // Movement types for pie chart
        $movementTypeLabels = [];
        $movementTypeData = [];
        foreach ($movementTypes as $type => $count) {
            $movementTypeLabels[] = ucfirst($type);
            $movementTypeData[] = $count;
        }

        return view('app.dashboard', [
            'stats' => [
                'products' => Product::where('business_id', $businessId)->count(),
                'stores' => Store::where('business_id', $businessId)->count(),
                'warehouses' => Warehouse::where('business_id', $businessId)->count(),
                'users' => User::where('business_id', $businessId)->count(),
            ],
            'movements' => StockMovement::where('business_id', $businessId)->latest()->take(5)->get(),
            'chartData' => [
                'movementsTrend' => $movementsTrend,
                'movementTypes' => [
                    'labels' => $movementTypeLabels ?: ['No data'],
                    'data' => $movementTypeData ?: [0],
                ],
                'storeCount' => Store::where('business_id', $businessId)->count(),
                'warehouseCount' => Warehouse::where('business_id', $businessId)->count(),
            ],
        ]);
    }
}
