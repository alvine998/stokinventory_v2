<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\BankAccount;
use App\Models\CmsPage;
use App\Models\Testimonial;
use App\Models\CompanyProfile;
use App\Models\DiscountCode;
use App\Models\Invoice;
use App\Models\PlatformNotification;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\SupportMessage;
use App\Models\Business;use App\Models\SupportRoom;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $business = Business::firstOrCreate(
            ['name' => 'Demo Retail Nusantara'],
            [
                'industry' => 'Retail',
                'business_size' => '11-50',
                'inventory_goal' => 'Improve daily stock visibility',
                'has_multiple_locations' => true,
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addMonth(),
                'onboarding_completed_at' => now(),
            ]
        );

        CompanyProfile::firstOrCreate(
            ['business_id' => $business->id],
            [
                'name' => $business->name,
                'vision' => 'Become the most reliable local inventory operation.',
                'mission' => 'Keep stock accurate, visible, and ready for every channel.',
                'organization' => 'Owner, Operations, Warehouse, Store Team',
                'why_us' => 'Simple workflows, clear reports, and controlled access.',
            ]
        );

        $ownerRole = Role::firstOrCreate(
            ['business_id' => $business->id, 'slug' => 'owner'],
            ['name' => 'Owner', 'permissions' => Role::defaultOwnerPermissions()]
        );

        $user = User::firstOrCreate(
            ['email' => 'owner@stokinventory.test'],
            [
                'business_id' => $business->id,
                'name' => 'Demo Owner',
                'password' => Hash::make('password'),
            ]
        );
        $user->roles()->syncWithoutDetaching([$ownerRole->id]);

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@stokinventory.test'],
            [
                'business_id' => $business->id,
                'name' => 'Platform Super Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'platform_role' => 'super_admin',
            ]
        );
        $superAdmin->forceFill(['is_super_admin' => true, 'platform_role' => 'super_admin'])->save();
        $superAdmin->roles()->syncWithoutDetaching([$ownerRole->id]);

        $platformAdmin = User::firstOrCreate(
            ['email' => 'admin@stokinventory.test'],
            [
                'business_id' => $business->id,
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'platform_role' => 'platform_admin',
            ]
        );
        $platformAdmin->forceFill(['platform_role' => 'platform_admin'])->save();
        $platformAdmin->roles()->syncWithoutDetaching([$ownerRole->id]);

        $customerService = User::firstOrCreate(
            ['email' => 'cs@stokinventory.test'],
            [
                'business_id' => $business->id,
                'name' => 'Customer Service',
                'password' => Hash::make('password'),
                'platform_role' => 'customer_service',
            ]
        );
        $customerService->forceFill(['platform_role' => 'customer_service'])->save();
        $customerService->roles()->syncWithoutDetaching([$ownerRole->id]);

        $store = Store::firstOrCreate(['business_id' => $business->id, 'code' => 'STR-01'], ['name' => 'Main Store', 'address' => 'Jakarta', 'status' => 'active']);
        $warehouse = Warehouse::firstOrCreate(['business_id' => $business->id, 'code' => 'WH-01'], ['store_id' => $store->id, 'name' => 'Central Warehouse', 'address' => 'Jakarta', 'status' => 'active']);
        $product = Product::firstOrCreate(['business_id' => $business->id, 'sku' => 'SKU-001'], ['name' => 'Starter Product', 'category' => 'General', 'unit' => 'pcs', 'price' => 25000, 'minimum_stock' => 10, 'current_stock' => 120]);
        StockMovement::firstOrCreate(['business_id' => $business->id, 'reference_no' => 'IN-0001'], ['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'type' => 'in', 'quantity' => 120, 'moved_at' => now()]);

        SubscriptionPackage::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'tagline' => 'For small shops starting clean stock control.',
                'price' => 149000,
                'discount_percent' => 0,
                'billing_periods' => [
                    ['months' => 6,  'discount_percent' => 5],
                    ['months' => 12, 'discount_percent' => 10],
                    ['months' => 24, 'discount_percent' => 20],
                ],
                'trial_days' => 30,
                'features' => ['1 store', '2 warehouses', '500 products', 'Printable stock receipts'],
                'is_featured' => false,
                'is_active' => true,
            ]
        );

        SubscriptionPackage::firstOrCreate(
            ['slug' => 'growth'],
            [
                'name' => 'Growth',
                'tagline' => 'For growing teams with daily stock movement.',
                'price' => 349000,
                'discount_percent' => 0,
                'billing_periods' => [
                    ['months' => 6,  'discount_percent' => 10],
                    ['months' => 12, 'discount_percent' => 20],
                    ['months' => 24, 'discount_percent' => 35],
                ],
                'trial_days' => 30,
                'features' => ['5 stores', '10 warehouses', 'Unlimited products', 'RBAC and audit-ready reports'],
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        SubscriptionPackage::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'tagline' => 'For multi-branch operations that need control.',
                'price' => 899000,
                'discount_percent' => 0,
                'billing_periods' => [
                    ['months' => 6,  'discount_percent' => 10],
                    ['months' => 12, 'discount_percent' => 20],
                    ['months' => 24, 'discount_percent' => 30],
                ],
                'trial_days' => 30,
                'features' => ['Unlimited stores', 'Advanced role management', 'Priority support', 'Company profile and organization settings'],
                'is_featured' => false,
                'is_active' => true,
            ]
        );

        PromoBanner::firstOrCreate(
            ['title' => 'Ramadan Inventory Promo'],
            [
                'subtitle' => 'Save up to 35% for Growth package orders this month.',
                'badge' => 'Limited promo',
                'button_label' => 'Claim discount',
                'button_url' => '/register',
                'background' => '#0f766e',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'is_active' => true,
            ]
        );

        PromoBanner::firstOrCreate(
            ['title' => 'Free onboarding for new teams'],
            [
                'subtitle' => 'Set up store, warehouse, product, and RBAC structure with a guided trial.',
                'badge' => 'New user',
                'button_label' => 'Start trial',
                'button_url' => '/register',
                'background' => '#2563eb',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addWeeks(6),
                'is_active' => true,
            ]
        );

        CmsPage::firstOrCreate(
            ['slug' => 'landing-about-us'],
            [
                'title' => 'About StokInventory',
                'section' => 'landing_about',
                'body' => 'StokInventory is an inventory management platform for growing Indonesian businesses that need accurate stock, controlled team access, printable receipts, and clear reporting across stores and warehouses.',
                'is_published' => true,
            ]
        );

        CmsPage::firstOrCreate(
            ['slug' => 'landing-why-stokinventory'],
            [
                'title' => 'Why StokInventory',
                'section' => 'landing',
                'body' => 'Accurate stock, clear roles, printable reports, and faster daily operations.',
                'is_published' => true,
            ]
        );

        PlatformNotification::firstOrCreate(
            ['title' => 'Welcome to the 30-day trial'],
            [
                'business_id' => $business->id,
                'audience' => 'specific',
                'message' => 'Your StokInventory workspace is ready. Complete onboarding and invite your team.',
                'channel' => 'in_app',
                'sent_at' => now(),
            ]
        );

        $primaryBank = BankAccount::firstOrCreate(
            ['account_number' => '1234567890'],
            [
                'bank_name' => 'BCA',
                'account_name' => 'PT StokInventory Teknologi',
                'branch' => 'Jakarta Sudirman',
                'is_active' => true,
            ]
        );

        BankAccount::firstOrCreate(
            ['account_number' => '8800123456'],
            [
                'bank_name' => 'Mandiri',
                'account_name' => 'PT StokInventory Teknologi',
                'branch' => 'Jakarta Thamrin',
                'is_active' => true,
            ]
        );

        $growth = SubscriptionPackage::where('slug', 'growth')->first();
        $starter = SubscriptionPackage::where('slug', 'starter')->first();

        DiscountCode::firstOrCreate(
            ['code' => 'GROWTH10'],
            [
                'subscription_package_id' => $growth?->id,
                'type' => 'percent',
                'value' => 10,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'is_active' => true,
            ]
        );

        DiscountCode::firstOrCreate(
            ['code' => 'WELCOME50000'],
            [
                'subscription_package_id' => null,
                'type' => 'fixed',
                'value' => 50000,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
                'is_active' => true,
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_no' => 'INV-DEMO-0001'],
            [
                'business_id' => $business->id,
                'subscription_package_id' => $growth?->id,
                'bank_account_id' => $primaryBank->id,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'amount' => 349000,
                'discount_amount' => 122150,
                'total_amount' => 226850,
                'status' => 'paid',
                'payment_method' => 'bank_transfer',
                'payment_notes' => 'Demo payment confirmed by platform owner.',
                'paid_at' => now()->subDay(),
                'issued_at' => now()->subDays(3)->toDateString(),
                'due_at' => now()->addDays(27)->toDateString(),
            ]
        );

        $room = SupportRoom::firstOrCreate(
            ['business_id' => $business->id, 'subject' => 'Need help reading invoice discount'],
            [
                'assigned_user_id' => $customerService->id,
                'support_type' => 'billing',
                'status' => 'open',
            ]
        );

        SupportMessage::firstOrCreate(
            ['support_room_id' => $room->id, 'message' => 'Can you explain the Growth package discount on our invoice?'],
            ['user_id' => $user->id, 'is_staff_reply' => false]
        );

        SupportMessage::firstOrCreate(
            ['support_room_id' => $room->id, 'message' => 'Sure. The current promo applies a 35% discount before tax and fees.'],
            ['user_id' => $customerService->id, 'is_staff_reply' => true]
        );

        // Testimonials
        foreach ([
            [
                'name' => 'Rina Setiawati', 'role' => 'Operations Manager', 'company' => 'Toko Maju Bersama',
                'body' => 'StokInventory changed how we manage our 3 stores. Stock discrepancies dropped to almost zero within the first month.',
                'rating' => 5, 'sort_order' => 1, 'is_active' => true,
            ],
            [
                'name' => 'Budi Hartono', 'role' => 'Owner', 'company' => 'Distributor Elektronik Jaya',
                'body' => 'The role-based access control is exactly what we needed. Staff see only what they need, and I can audit everything.',
                'rating' => 5, 'sort_order' => 2, 'is_active' => true,
            ],
            [
                'name' => 'Sari Dewi', 'role' => 'Finance & Admin', 'company' => 'CV Karya Mandiri',
                'body' => 'Printable stock receipts and clear reports make our monthly closing much faster. Support is also very responsive.',
                'rating' => 4, 'sort_order' => 3, 'is_active' => true,
            ],
            [
                'name' => 'Dimas Prasetyo', 'role' => 'Warehouse Supervisor', 'company' => 'PT Logistik Nusantara',
                'body' => 'Multi-warehouse tracking used to be a headache. With StokInventory, we always know exactly where every item is.',
                'rating' => 5, 'sort_order' => 4, 'is_active' => true,
            ],
            [
                'name' => 'Anita Kusuma', 'role' => 'Business Owner', 'company' => 'Batik Kusuma Store',
                'body' => 'We started with the Starter plan and upgraded within 2 months. The value is real — our stock accuracy is now above 98%.',
                'rating' => 5, 'sort_order' => 5, 'is_active' => true,
            ],
        ] as $t) {
            Testimonial::firstOrCreate(['name' => $t['name'], 'company' => $t['company']], $t);
        }
    }
}
