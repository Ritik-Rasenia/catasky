<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class WholeSystemDummyDataSeeder extends Seeder
{
    private array $columns = [];
    private ?string $demoPasswordHash = null;

    public function run(): void
    {
        $this->call([
            DefaultPermissionsAndRolesSeeder::class,
            SubscriberRoleAndPlansSeeder::class,
        ]);

        $this->seedSettings();
        $users = $this->seedUsers();
        $catalogue = $this->seedCatalogue();
        $this->seedFrontOfficeData($catalogue);
        $this->seedSubscriberSystem($users, $catalogue);

        $this->command?->info('Whole-system realistic dummy data seeded successfully.');
    }

    private function seedSettings(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $this->row('settings', ['id' => 1], [
            'site_title' => 'CataSky',
            'site_description' => 'A curated B2B catalogue platform for branded merchandise, gifting kits, and subscriber storefronts.',
            'logo' => 'uploads/settings/1779270966_logo.png',
            'footer_logo' => 'uploads/settings/1779270966_footer_logo.png',
            'favicon' => 'uploads/settings/1779270966_favicon.png',
            'watermark' => 'uploads/settings/1779270966_watermark.png',
            'email' => 'hello@catasky.test',
            'phone' => '+91 98713 76205',
            'address' => '2nd Floor, Sector 63, Noida, Uttar Pradesh',
            'primary_color' => '#1F7A8C',
            'secondary_color' => '#F59E0B',
            'font_family' => 'Inter',
            'meta_title' => 'CataSky | Premium B2B Catalogue',
            'meta_description' => 'Discover practical, brandable corporate gifts and manage subscriber product catalogues.',
            'meta_keywords' => 'corporate gifting, B2B catalogue, employee welcome kits, branded merchandise',
        ]);
    }

    private function seedUsers(): array
    {
        $users = [
            'super_admin' => $this->user('Aarav Mehta', 'admin@catasky.com', 'Super Admin'),
            'catalogue_manager' => $this->user('Nisha Kapoor', 'manager@catasky.test', 'Super Admin'),
            'subscriber_a' => $this->user('Rohan Malhotra', 'rohan@urbanedge.test', 'Subscriber'),
            'subscriber_b' => $this->user('Priya Nair', 'priya@greenorbit.test', 'Subscriber'),
            'subscriber_c' => $this->user('Sameer Joshi', 'sameer@techcraft.test', 'Subscriber'),
        ];

        return $users;
    }

    private function user(string $name, string $email, string $roleName): int
    {
        $id = $this->row('users', ['email' => $email], [
            'name' => $name,
            'email_verified_at' => Carbon::now()->subDays(rand(5, 90)),
            'password' => $this->demoPasswordHash(),
            'profile_image' => 'uploads/profile/1778931517.png',
        ]);

        if (class_exists(Role::class) && Schema::hasTable('model_has_roles')) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->users()->syncWithoutDetaching([$id]);
        }

        return $id;
    }

    private function demoPasswordHash(): string
    {
        if (! $this->demoPasswordHash) {
            $this->demoPasswordHash = Hash::make('Passw0rd!', ['rounds' => 4]);
        }

        return $this->demoPasswordHash;
    }

    private function seedCatalogue(): array
    {
        $brands = [];
        foreach ([
            ['UrbanEdge', 'https://logo.clearbit.com/urbanoutfitters.com', 'Premium lifestyle merchandise for modern workplaces.'],
            ['HydraNest', 'https://logo.clearbit.com/stanley1913.com', 'Drinkware and daily-use steel products for teams.'],
            ['WorkForge', 'https://logo.clearbit.com/logitech.com', 'Technology accessories and desk productivity tools.'],
            ['EcoOrbit', 'https://logo.clearbit.com/ecovadis.com', 'Sustainable office essentials and reusable gift kits.'],
            ['PrintMint', 'https://logo.clearbit.com/canva.com', 'Custom print, stationery, and conference collateral.'],
            ['AwardCraft', 'https://logo.clearbit.com/apexgroup.com', 'Recognition awards, plaques, and milestone gifts.'],
        ] as [$name, $image, $description]) {
            $brands[$name] = $this->row('brands', ['slug' => Str::slug($name)], [
                'name' => $name,
                'image' => $image,
                'description' => $description,
                'status' => 1,
            ]);
        }

        $catalogue = [
            [
                'category' => ['Corporate Drinkware', 'https://images.unsplash.com/photo-1602143307185-84e030739987?auto=format&fit=crop&w=800&q=80'],
                'subcategory' => ['Vacuum Bottles', 'https://images.unsplash.com/photo-1602143307185-84e030739987?auto=format&fit=crop&w=800&q=80'],
                'child' => 'Steel Bottles',
                'products' => [
                    ['HydraNest Dori 500ml Vacuum Bottle', 'HydraNest', 'https://images.unsplash.com/photo-1602143307185-84e030739987?auto=format&fit=crop&w=800&q=80', 549, 'DRK-DORI-500', 'Double-wall bottle with a powder coated body and leak-proof lid.', 'Capacity: 500ml; Material: 304 stainless steel; Branding: laser engraving; MOQ: 50 pcs', 'drinkware, steel bottle, onboarding'],
                    ['HydraNest 1200ml Desk Tumbler', 'HydraNest', 'https://images.unsplash.com/photo-1574680077532-f2bf135987f6?auto=format&fit=crop&w=800&q=80', 799, 'DRK-TMB-1200', 'Large capacity tumbler with straw lid and handle grip for long desk days.', 'Capacity: 1200ml; Lid: splash resistant; Finish: matte; MOQ: 30 pcs', 'tumbler, hydration, employee wellness'],
                    ['EcoOrbit Bamboo Lid Glass Jar', 'EcoOrbit', 'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?auto=format&fit=crop&w=800&q=80', 349, 'DRK-GLS-370', 'Glass pantry jar with bamboo-style lid for premium snack hampers.', 'Capacity: 370ml; Material: clear glass; Packaging: kraft sleeve; MOQ: 100 pcs', 'glass jar, eco gift, pantry'],
                ],
            ],
            [
                'category' => ['Executive Bags', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80'],
                'subcategory' => ['Laptop Backpacks', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80'],
                'child' => 'Daily Commute',
                'products' => [
                    ['UrbanEdge Executive Laptop Backpack', 'UrbanEdge', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80', 1899, 'BAG-EXE-001', 'Structured 15.6 inch laptop backpack with water-resistant shell.', 'Laptop: 15.6 inch; Material: polyester; Branding: metal badge; MOQ: 25 pcs', 'backpack, laptop bag, executive gift'],
                    ['UrbanEdge Bottle Holder Sling', 'UrbanEdge', 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?auto=format&fit=crop&w=800&q=80', 199, 'BAG-BTL-001', 'Compact bottle sling for events, marathons, and outdoor engagement kits.', 'Fit: up to 1L bottles; Strap: adjustable; Print: single color; MOQ: 200 pcs', 'sling, event kit, bottle holder'],
                    ['PrintMint Blue Diary Travel Set', 'PrintMint', 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80', 699, 'KIT-DIA-2IN1', 'Premium diary and pen set for conferences and client meetings.', 'Includes: diary and pen; Box: rigid gift box; Branding: foil print; MOQ: 50 sets', 'diary set, onboarding, client gift'],
                ],
            ],
            [
                'category' => ['Tech Accessories', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80'],
                'subcategory' => ['Charging & Audio', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80'],
                'child' => 'Desk Tech',
                'products' => [
                    ['WorkForge 3-in-1 Charger Stand', 'WorkForge', 'https://images.unsplash.com/photo-1622445262465-2481c857535a?auto=format&fit=crop&w=800&q=80', 1499, 'TEC-CHG-3IN1', 'Desk charging stand for phone, earbuds, and watch.', 'Input: Type-C; Output: multi-device; Finish: iron grey; MOQ: 20 pcs', 'charger, desk setup, tech gift'],
                    ['WorkForge Wireless Earbuds Pro', 'WorkForge', 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&q=80', 1199, 'AUD-WF-01', 'True wireless earbuds with charging case and optional logo sleeve.', 'Playback: up to 20 hours; Bluetooth: 5.3; Branding: sleeve print; MOQ: 50 pcs', 'earbuds, audio, reward'],
                    ['WorkForge Mini Power Bank 10K', 'WorkForge', 'https://images.unsplash.com/photo-1609592424109-dd9892f1b17b?auto=format&fit=crop&w=800&q=80', 899, 'TEC-PB-10K', 'Pocket-friendly fast-charge power bank for business travel kits.', 'Capacity: 10000mAh; Ports: Type-C and USB-A; MOQ: 50 pcs', 'power bank, travel kit, tech'],
                ],
            ],
            [
                'category' => ['Awards & Recognition', 'https://images.unsplash.com/photo-1578269174936-2709b6aeb913?auto=format&fit=crop&w=800&q=80'],
                'subcategory' => ['Trophies & Plaques', 'https://images.unsplash.com/photo-1578269174936-2709b6aeb913?auto=format&fit=crop&w=800&q=80'],
                'child' => 'Crystal Awards',
                'products' => [
                    ['AwardCraft Crystal Star Trophy', 'AwardCraft', 'https://images.unsplash.com/photo-1578269174936-2709b6aeb913?auto=format&fit=crop&w=800&q=80', 1399, 'AWD-CRY-3354', 'Premium crystal award with laser engraving area.', 'Material: optical crystal; Engraving: laser; MOQ: 10 pcs', 'award, trophy, recognition'],
                    ['AwardCraft Gold Leadership Award', 'AwardCraft', 'https://images.unsplash.com/photo-1614036417651-efe5912149d8?auto=format&fit=crop&w=800&q=80', 1599, 'AWD-LDR-3356', 'Black and gold trophy for leadership and sales excellence.', 'Finish: black-gold; Base: weighted; MOQ: 10 pcs', 'leadership award, trophy, sales'],
                    ['PrintMint Acrylic Recognition Plaque', 'PrintMint', 'https://images.unsplash.com/photo-1589156280159-27698a70f29e?auto=format&fit=crop&w=800&q=80', 899, 'AWD-ACR-3374', 'Clean acrylic plaque with a high-contrast branding panel.', 'Material: acrylic; Printing: UV; MOQ: 25 pcs', 'plaque, acrylic award, appreciation'],
                ],
            ],
            [
                'category' => ['Welcome & Gift Sets', 'https://images.unsplash.com/photo-1586075010923-2dd4570fb338?auto=format&fit=crop&w=800&q=80'],
                'subcategory' => ['Employee Welcome Kits', 'https://images.unsplash.com/photo-1586075010923-2dd4570fb338?auto=format&fit=crop&w=800&q=80'],
                'child' => 'Joining Kits',
                'products' => [
                    ['UrbanEdge New Joiner Welcome Kit', 'UrbanEdge', 'https://images.unsplash.com/photo-1586075010923-2dd4570fb338?auto=format&fit=crop&w=800&q=80', 1499, 'KIT-NJ-360', 'Ready-to-ship kit with bottle, notebook, pen, and greeting card.', 'Items: 4; Packaging: rigid box; MOQ: 30 kits', 'welcome kit, HR, onboarding'],
                    ['EcoOrbit Sustainable Office Starter Pack', 'EcoOrbit', 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80', 999, 'KIT-ECO-638', 'Reusable products curated for sustainability campaigns.', 'Material: mixed eco materials; Packaging: kraft; MOQ: 50 packs', 'eco kit, sustainability, reusable'],
                    ['PrintMint Festive Corporate Hamper', 'PrintMint', 'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?auto=format&fit=crop&w=800&q=80', 1799, 'KIT-FST-600', 'Festive hamper with practical office products and a message card.', 'Items: assorted; Card: personalized; MOQ: 25 hampers', 'festive hamper, corporate gifting, Diwali'],
                ],
            ],
        ];

        $products = [];
        foreach ($catalogue as $group) {
            $categoryId = $this->row('categories', ['slug' => Str::slug($group['category'][0])], [
                'name' => $group['category'][0],
                'image' => $group['category'][1],
                'status' => 1,
            ]);

            $subcategoryId = $this->row('subcategories', ['slug' => Str::slug($group['subcategory'][0])], [
                'category_id' => $categoryId,
                'name' => $group['subcategory'][0],
                'image' => $group['subcategory'][1],
                'status' => 1,
            ]);

            $childId = null;
            if (Schema::hasTable('child_categories')) {
                $childId = $this->row('child_categories', ['slug' => Str::slug($group['child'])], [
                    'category_id' => $categoryId,
                    'subcategory_id' => $subcategoryId,
                    'name' => $group['child'],
                    'status' => 1,
                ]);
            }

            foreach ($group['products'] as $index => $item) {
                [$name, $brandName, $thumbnail, $price, $partCode, $short, $specs, $tags] = $item;
                $productId = $this->row('products', ['slug' => Str::slug($name)], [
                    'brand_id' => $brands[$brandName],
                    'category_id' => $categoryId,
                    'subcategory_id' => $subcategoryId,
                    'child_category_id' => $childId,
                    'name' => $name,
                    'part_code' => $partCode,
                    'part_number' => 'PN-' . $partCode,
                    'thumbnail' => str_starts_with($thumbnail, 'http') ? $thumbnail : 'uploads/products/' . $thumbnail,
                    'short_description' => $short,
                    'variant' => "Standard: Rs. {$price}\nLogo Printed: Rs. " . ($price + 90) . "\nPremium Gift Box: Rs. " . ($price + 220),
                    'price' => $price,
                    'specifications' => $specs,
                    'tags' => $tags,
                    'packaging' => 'Packed in export-grade cartons. Custom sleeves, inserts, and gift boxes available.',
                    'additional_info' => 'Demo product with indicative B2B pricing and realistic branding notes.',
                    'featured' => $index < 2 ? 1 : 0,
                    'is_future' => 0,
                    'status' => 1,
                    'meta_title' => $name . ' | CataSky Catalogue',
                    'meta_description' => $short,
                    'meta_keywords' => $tags,
                ]);

                $products[] = ['id' => $productId, 'brand_id' => $brands[$brandName], 'category_id' => $categoryId, 'subcategory_id' => $subcategoryId, 'name' => $name, 'price' => $price];

                if (Schema::hasTable('product_images')) {
                    $imgUrl = str_starts_with($thumbnail, 'http') ? $thumbnail : 'uploads/products/' . $thumbnail;
                    $this->row('product_images', ['product_id' => $productId, 'image' => $imgUrl], [
                        'product_id' => $productId,
                        'image' => $imgUrl,
                    ]);
                }
            }
        }

        $this->seedSolutions($products);

        return ['brands' => $brands, 'products' => $products];
    }

    private function seedSolutions(array $products): void
    {
        if (! Schema::hasTable('solutions')) {
            return;
        }

        $solutionIds = [];
        foreach (['New Joiner Kits', 'Client Appreciation', 'Annual Awards', 'Sustainable Campaigns'] as $name) {
            $solutionIds[] = $this->row('solutions', ['slug' => Str::slug($name)], [
                'name' => $name,
                'status' => 1,
            ]);
        }

        if (! Schema::hasTable('product_solution')) {
            return;
        }

        foreach ($products as $index => $product) {
            $this->row('product_solution', [
                'product_id' => $product['id'],
                'solution_id' => $solutionIds[$index % count($solutionIds)],
            ], [
                'product_id' => $product['id'],
                'solution_id' => $solutionIds[$index % count($solutionIds)],
            ]);
        }
    }

    private function seedFrontOfficeData(array $catalogue): void
    {
        $products = $catalogue['products'];

        if (Schema::hasTable('enquiries')) {
            foreach ([
                ['Ananya Sharma', 'ananya@northstar.test', '+91 98100 41001', 'Need 350 welcome kits for June onboarding.', 0],
                ['Kabir Sethi', 'kabir@finaxis.test', '+91 98220 43002', 'Please quote 120 crystal awards with engraving.', 9],
                ['Meera Iyer', 'meera@cloudnest.test', '+91 98330 44003', 'Looking for eco-friendly festive hampers under Rs. 1800.', 13],
                ['Vikram Rao', 'vikram@byteworks.test', '+91 98440 45004', 'Can we get tech desk kits delivered to Bengaluru?', 6],
            ] as [$name, $email, $phone, $message, $productIndex]) {
                $product = $products[$productIndex] ?? $products[0];
                $this->row('enquiries', ['email' => $email, 'product_id' => $product['id']], [
                    'product_id' => $product['id'],
                    'brand_id' => $product['brand_id'],
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'subject' => 'Catalogue quote request',
                    'message' => $message,
                    'status' => $productIndex % 2 === 0 ? 'new' : 'read',
                ]);
            }
        }

        if (Schema::hasTable('newsletters')) {
            foreach (['hr.ops@northstar.test', 'procurement@finaxis.test', 'events@cloudnest.test', 'admin@byteworks.test'] as $email) {
                $this->row('newsletters', ['email' => $email], [
                    'email' => $email,
                    'status' => 1,
                ]);
            }
        }

        if (Schema::hasTable('catalogue_shares')) {
            foreach ([
                ['NORTH26', '+919810041001', [0, 1, 12], 'delivered', 'read', 'yes'],
                ['FINAX26', '+919822043002', [9, 10, 11], 'sent', 'unread', 'no'],
            ] as [$code, $phone, $productIndexes, $delivery, $seen, $clicked]) {
                $productIds = collect($productIndexes)
                    ->map(fn (int $index) => $products[$index]['id'] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                $shareId = $this->row('catalogue_shares', ['catalogue_code' => $code], [
                    'user_id' => 1,
                    'catalogue_code' => $code,
                    'product_ids' => json_encode($productIds),
                    'customer_phone' => $phone,
                    'message_id' => 'msg_demo_' . Str::lower($code),
                    'delivery_status' => $delivery,
                    'seen_status' => $seen,
                    'clicked_status' => $clicked,
                    'opened_status' => $clicked,
                    'total_view_time' => rand(85, 620),
                    'visit_count' => rand(2, 14),
                    'pdf_url' => 'uploads/catalogues/' . Str::lower($code) . '.pdf',
                ]);

                if (Schema::hasTable('share_tracking_logs')) {
                    for ($i = 0; $i < 3; $i++) {
                        $eventType = ['delivery', 'open', 'click'][$i];
                        $this->row('share_tracking_logs', ['share_id' => $shareId, 'event_type' => $eventType], [
                            'share_id' => $shareId,
                            'event_type' => $eventType,
                            'event_time' => Carbon::now()->subHours(12 - ($i * 3)),
                            'metadata' => json_encode(['city' => ['Noida', 'Mumbai', 'Bengaluru'][$i], 'seeded' => true]),
                        ]);
                    }
                }
            }
        }
    }

    private function seedSubscriberSystem(array $users, array $catalogue): void
    {
        $subscriberData = [
            $users['subscriber_a'] => ['UrbanEdge Promotions', 'urbanedge-promotions', 'Noida', 'Uttar Pradesh', 'Business', '#0F766E', '#F59E0B', 'https://logo.clearbit.com/urbanoutfitters.com'],
            $users['subscriber_b'] => ['GreenOrbit Gifting Co.', 'greenorbit-gifting', 'Kochi', 'Kerala', 'Starter', '#15803D', '#84CC16', 'https://logo.clearbit.com/ecovadis.com'],
            $users['subscriber_c'] => ['TechCraft Rewards', 'techcraft-rewards', 'Bengaluru', 'Karnataka', 'Enterprise', '#2563EB', '#F97316', 'https://logo.clearbit.com/logitech.com'],
        ];

        foreach ($subscriberData as $userId => [$company, $slug, $city, $state, $planName, $primary, $secondary, $logoUrl]) {
            $this->row('subscriber_profiles', ['user_id' => $userId], [
                'user_id' => $userId,
                'company_name' => $company,
                'company_slug' => $slug,
                'phone' => '+91 ' . rand(90000, 99999) . ' ' . rand(10000, 99999),
                'website' => 'https://' . $slug . '.test',
                'address' => rand(21, 88) . ', Business Park, ' . $city,
                'city' => $city,
                'state' => $state,
                'country' => 'India',
                'pincode' => (string) rand(110001, 695999),
                'gst_number' => '09ABCDE' . rand(1000, 9999) . 'F1Z' . rand(1, 9),
                'logo' => $logoUrl,
                'bio' => 'Demo subscriber storefront focused on curated corporate gifting and branded merchandise.',
                'whatsapp_number' => '91' . rand(9000000000, 9999999999),
                'email_for_inquiries' => 'sales@' . $slug . '.test',
                'primary_color' => $primary,
                'secondary_color' => $secondary,
                'status' => 'approved',
                'approval_status' => 'approved',
                'is_verified' => 1,
            ]);

            $planId = $this->tableId('subscription_plans', ['name' => $planName]) ?: $this->tableId('subscription_plans', ['slug' => Str::slug($planName)]);
            if ($planId) {
                $subscriptionId = $this->row('subscriptions', ['user_id' => $userId, 'subscription_plan_id' => $planId], [
                    'user_id' => $userId,
                    'subscription_plan_id' => $planId,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subDays(rand(3, 45)),
                    'ends_at' => Carbon::now()->addDays(rand(18, 330)),
                    'trial_ends_at' => Carbon::now()->subDays(1),
                    'auto_renew' => 1,
                    'meta' => json_encode(['source' => 'demo seeder', 'sales_owner' => 'Nisha Kapoor']),
                ]);

                $paymentId = $this->row('payments', ['transaction_id' => 'DEMO-TXN-' . $userId], [
                    'user_id' => $userId,
                    'subscription_plan_id' => $planId,
                    'transaction_id' => 'DEMO-TXN-' . $userId,
                    'gateway' => 'dummy',
                    'gateway_payment_id' => 'pay_demo_' . Str::lower(Str::random(8)),
                    'amount' => DB::table('subscription_plans')->where('id', $planId)->value('price') ?: 499,
                    'currency' => 'INR',
                    'status' => 'success',
                    'gateway_response' => json_encode(['mode' => 'demo', 'captured' => true]),
                    'notes' => 'Seeded successful subscription payment.',
                    'paid_at' => Carbon::now()->subDays(rand(1, 10)),
                ]);

                $amount = (float) (DB::table('payments')->where('id', $paymentId)->value('amount') ?: 499);
                $tax = round($amount * 0.18, 2);
                $this->row('invoices', ['invoice_number' => 'INV-DEMO-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT)], [
                    'user_id' => $userId,
                    'subscription_id' => $subscriptionId,
                    'payment_id' => $paymentId,
                    'invoice_number' => 'INV-DEMO-' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT),
                    'subtotal' => $amount,
                    'tax' => $tax,
                    'total' => $amount + $tax,
                    'currency' => 'INR',
                    'status' => 'paid',
                    'due_date' => Carbon::now()->addDays(7)->toDateString(),
                    'paid_date' => Carbon::now()->subDays(1)->toDateString(),
                    'line_items' => json_encode([['description' => $planName . ' monthly subscription', 'amount' => $amount]]),
                    'notes' => 'Demo invoice generated by seeder.',
                ]);
            }

            $this->seedSubscriberProducts($userId, $company, $catalogue);
            $this->seedSubscriberExtras($userId, $slug, $primary, $secondary);
        }
    }

    private function seedSubscriberProducts(int $userId, string $company, array $catalogue): void
    {
        $groups = [
            ['Product Details', ['Material', 'Capacity', 'Branding Method', 'Lead Time']],
            ['Commercials', ['MOQ', 'Bulk Price Tier', 'Sample Availability']],
        ];

        $attributes = [];
        foreach ($groups as $groupIndex => [$groupName, $attributeNames]) {
            $groupId = $this->row('attribute_groups', ['user_id' => $userId, 'slug' => Str::slug($groupName)], [
                'user_id' => $userId,
                'name' => $groupName,
                'description' => $groupName . ' used by ' . $company,
                'sort_order' => $groupIndex,
                'is_active' => 1,
            ]);

            foreach ($attributeNames as $index => $name) {
                $type = $name === 'Branding Method' ? 'select' : ($name === 'Bulk Price Tier' ? 'textarea' : 'text');
                $attributeId = $this->row('attributes', ['user_id' => $userId, 'slug' => Str::slug($name)], [
                    'user_id' => $userId,
                    'attribute_group_id' => $groupId,
                    'name' => $name,
                    'type' => $type,
                    'placeholder' => 'Enter ' . Str::lower($name),
                    'is_required' => in_array($name, ['Material', 'MOQ'], true) ? 1 : 0,
                    'is_searchable' => in_array($name, ['Material', 'Capacity'], true) ? 1 : 0,
                    'show_in_pdf' => 1,
                    'show_in_share' => 1,
                    'is_active' => 1,
                    'is_global' => 0,
                    'approval_status' => 'approved',
                    'sort_order' => $index,
                ]);
                $attributes[$name] = $attributeId;

                if ($type === 'select') {
                    foreach (['Laser Engraving', 'Screen Print', 'UV Print', 'Embroidery'] as $optionIndex => $option) {
                        $this->row('attribute_options', ['attribute_id' => $attributeId, 'value' => Str::slug($option)], [
                            'attribute_id' => $attributeId,
                            'label' => $option,
                            'value' => Str::slug($option),
                            'sort_order' => $optionIndex,
                            'is_default' => $optionIndex === 0 ? 1 : 0,
                        ]);
                    }
                }
            }
        }

        $sourceProducts = array_slice($catalogue['products'], ($userId % 3) * 4, 6);
        if (count($sourceProducts) < 4) {
            $sourceProducts = array_slice($catalogue['products'], 0, 6);
        }

        foreach ($sourceProducts as $index => $source) {
            $name = $source['name'] . ' - ' . Str::before($company, ' ');
            $productId = $this->row('subscriber_products', ['user_id' => $userId, 'slug' => Str::slug($name)], [
                'user_id' => $userId,
                'category_id' => $source['category_id'],
                'subcategory_id' => $source['subcategory_id'],
                'name' => $name,
                'sku' => 'SUB-' . $userId . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'mrp' => $source['price'] + 250,
                'offer_price' => $source['price'] + rand(60, 180),
                'currency' => 'INR',
                'thumbnail' => DB::table('products')->where('id', $source['id'])->value('thumbnail'),
                'short_description' => 'Subscriber-ready listing for ' . Str::lower($source['name']) . '.',
                'full_description' => 'Realistic demo product configured with pricing, attributes, variants, images, and share visibility.',
                'tags' => json_encode(['corporate gifting', 'branded', 'demo']),
                'featured' => $index < 2 ? 1 : 0,
                'status' => $index === 5 ? 'draft' : 'active',
                'sort_order' => $index,
            ]);

            $imagePath = DB::table('products')->where('id', $source['id'])->value('thumbnail');
            $this->row('subscriber_product_images', ['subscriber_product_id' => $productId, 'image_path' => $imagePath], [
                'subscriber_product_id' => $productId,
                'image_path' => $imagePath,
                'alt_text' => $source['name'],
                'sort_order' => 0,
                'is_primary' => 1,
            ]);

            $values = [
                'Material' => str_contains($source['name'], 'Bottle') || str_contains($source['name'], 'Tumbler') ? '304 stainless steel' : 'Premium mixed materials',
                'Capacity' => str_contains($source['name'], '1200') ? '1200ml' : (str_contains($source['name'], '500') ? '500ml' : 'Standard'),
                'Branding Method' => 'Laser Engraving',
                'Lead Time' => rand(7, 18) . ' working days',
                'MOQ' => rand(25, 100) . ' pcs',
                'Bulk Price Tier' => '50 pcs: 5% off; 100 pcs: 9% off; 250 pcs: 14% off',
            ];

            foreach ($values as $attributeName => $value) {
                $this->row('subscriber_product_attribute_values', ['subscriber_product_id' => $productId, 'attribute_id' => $attributes[$attributeName]], [
                    'subscriber_product_id' => $productId,
                    'attribute_id' => $attributes[$attributeName],
                    'value' => $value,
                ]);
            }

            foreach ([['STD', 0, 120], ['BRD', 95, 80], ['BOX', 210, 45]] as [$suffix, $priceAdd, $stock]) {
                $variantId = $this->row('subscriber_product_variants', ['variant_sku' => 'SUB-' . $userId . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT) . '-' . $suffix], [
                    'subscriber_product_id' => $productId,
                    'variant_sku' => 'SUB-' . $userId . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT) . '-' . $suffix,
                    'price' => $source['price'] + $priceAdd,
                    'stock' => $stock,
                    'status' => 1,
                ]);

                $this->row('subscriber_product_variant_attributes', ['variant_id' => $variantId, 'attribute_id' => $attributes['Branding Method']], [
                    'variant_id' => $variantId,
                    'attribute_id' => $attributes['Branding Method'],
                    'attribute_value' => $suffix === 'STD' ? 'No Branding' : ($suffix === 'BRD' ? 'Logo Printed' : 'Premium Gift Box'),
                ]);
            }

            if ($index < 3) {
                $this->row('subscriber_share_links', ['token' => hash('sha256', 'subscriber-demo-' . $userId . '-' . $productId)], [
                    'user_id' => $userId,
                    'subscriber_product_id' => $productId,
                    'token' => hash('sha256', 'subscriber-demo-' . $userId . '-' . $productId),
                    'title' => $source['name'] . ' Client Share',
                    'type' => $index === 0 ? 'catalog' : ($index === 1 ? 'pdf' : 'whatsapp'),
                    'settings' => json_encode(['show_prices' => true, 'show_attributes' => true, 'theme' => 'clean']),
                    'expires_at' => Carbon::now()->addDays(45),
                    'view_count' => rand(4, 70),
                    'download_count' => rand(0, 12),
                    'is_active' => 1,
                    'pdf_path' => 'uploads/subscriber-pdfs/demo-' . $userId . '-' . $productId . '.pdf',
                ]);
            }
        }

        if (Schema::hasTable('category_attributes')) {
            foreach ($catalogue['products'] as $index => $product) {
                if ($index > 4) {
                    break;
                }
                foreach (array_slice($attributes, 0, 3) as $attributeId) {
                    $this->row('category_attributes', ['category_id' => $product['category_id'], 'attribute_id' => $attributeId], [
                        'category_id' => $product['category_id'],
                        'attribute_id' => $attributeId,
                        'attribute_group_id' => DB::table('attributes')->where('id', $attributeId)->value('attribute_group_id'),
                        'is_required' => 0,
                        'sort_order' => $index,
                    ]);
                }
            }
        }
    }

    private function seedSubscriberExtras(int $userId, string $companySlug, string $primary, string $secondary): void
    {
        $this->row('subscriber_pdf_templates', ['user_id' => $userId, 'name' => 'Premium Grid Catalogue'], [
            'user_id' => $userId,
            'name' => 'Premium Grid Catalogue',
            'show_logo' => 1,
            'show_watermark' => 0,
            'watermark_text' => 'Demo Catalogue',
            'show_qr_code' => 1,
            'show_page_numbers' => 1,
            'brand_color' => $primary,
            'accent_color' => $secondary,
            'layout' => 'grid',
            'paper_size' => 'A4',
            'orientation' => 'portrait',
            'header_text' => 'Curated Corporate Catalogue',
            'footer_text' => 'Generated by CataSky demo data',
            'is_default' => 1,
        ]);

        $this->row('custom_domains', ['domain' => $companySlug . '.catasky.test'], [
            'user_id' => $userId,
            'domain' => $companySlug . '.catasky.test',
            'status' => 'active',
            'verified_at' => Carbon::now()->subDays(rand(1, 12)),
            'ssl_status' => 'active',
            'dns_records' => json_encode(['CNAME' => 'app.catasky.test']),
        ]);

        foreach ([
            ['login', 'Subscriber logged in from dashboard.'],
            ['product_created', 'Added realistic demo products to catalogue.'],
            ['share_created', 'Created a client-facing share link.'],
            ['invoice_paid', 'Subscription invoice marked as paid.'],
        ] as $index => [$action, $description]) {
            $this->row('subscriber_activity_logs', ['user_id' => $userId, 'action' => $action], [
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => '192.168.1.' . (20 + $index),
                'user_agent' => 'Mozilla/5.0 Demo Browser',
                'metadata' => json_encode(['seeded' => true]),
                'created_at' => Carbon::now()->subDays(4 - $index),
            ]);
        }
    }

    private function row(string $table, array $where, array $values): ?int
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $now = Carbon::now();
        $payload = array_merge($where, $values);
        if (Schema::hasColumn($table, 'created_at') && ! array_key_exists('created_at', $payload)) {
            $payload['created_at'] = $now;
        }
        if (Schema::hasColumn($table, 'updated_at') && ! array_key_exists('updated_at', $payload)) {
            $payload['updated_at'] = $now;
        }

        DB::table($table)->updateOrInsert(
            $this->onlyColumns($table, $where),
            $this->onlyColumns($table, $payload)
        );

        return $this->tableId($table, $where);
    }

    private function tableId(string $table, array $where): ?int
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table);
        foreach ($this->onlyColumns($table, $where) as $column => $value) {
            $query->where($column, $value);
        }

        $id = $query->value('id');

        return $id ? (int) $id : null;
    }

    private function onlyColumns(string $table, array $data): array
    {
        if (! isset($this->columns[$table])) {
            $this->columns[$table] = Schema::getColumnListing($table);
        }

        return array_intersect_key($data, array_flip($this->columns[$table]));
    }
}
