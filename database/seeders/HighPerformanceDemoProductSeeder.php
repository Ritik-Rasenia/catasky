<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class HighPerformanceDemoProductSeeder extends Seeder
{
    private array $columns = [];

    public function run(): void
    {
        if (! Schema::hasTable('subscriber_products')) {
            return;
        }

        $subscriberId = $this->seedSubscriber();
        $attributes = $this->seedAttributes($subscriberId);
        $categories = $this->seedCategories();

        $products = [
            ['electronics', 'WorkForge 3-in-1 Magnetic Charger Dock', 'WorkForge', 'TEC-MAG-301', 3299, 2499, 'In Stock', '15 x 9 x 8 cm', 'Graphite, Silver', '4.7', ['wireless charger', 'desk gadget', 'employee reward'], 'Fast wireless charging dock for phone, watch, and earbuds with a compact metal body.', 'https://images.unsplash.com/photo-1603539444875-76e7684265f6'],
            ['fashion', 'UrbanEdge Commuter Laptop Backpack', 'UrbanEdge', 'BAG-COM-118', 2899, 2199, 'In Stock', '46 x 31 x 17 cm', 'Charcoal, Navy, Olive', '4.8', ['backpack', 'laptop bag', 'travel'], 'Water-resistant backpack with 15.6 inch laptop sleeve, front organizer, and luggage strap.', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62'],
            ['corporate gifts', 'Apex Executive Welcome Kit', 'Apex Gifts', 'KIT-EXE-510', 3999, 3199, 'In Stock', '34 x 25 x 10 cm', 'Black, Tan', '4.9', ['welcome kit', 'corporate gifts', 'onboarding'], 'Premium joining kit with drinkware, notebook, pen, cable organizer, and branded box.', 'https://images.unsplash.com/photo-1607083206968-13611e3d76db'],
            ['bottles', 'HydraNest 750ml Vacuum Steel Bottle', 'HydraNest', 'BOT-VAC-750', 1199, 849, 'In Stock', '27 x 7 x 7 cm', 'Black, White, Teal, Copper', '4.6', ['bottle', 'drinkware', 'laser engraving'], 'Double-wall insulated bottle with powder coated finish and leak-proof cap for all-day use.', 'https://images.unsplash.com/photo-1602143407151-7111542de6e8'],
            ['backpacks', 'UrbanEdge Anti-Theft Tech Backpack', 'UrbanEdge', 'BAG-SEC-220', 3499, 2699, 'Limited Stock', '48 x 32 x 18 cm', 'Black, Grey', '4.7', ['anti theft', 'backpack', 'gadgets'], 'Structured anti-theft backpack with USB pass-through, hidden pocket, and padded laptop zone.', 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3'],
            ['gadgets', 'WorkForge 10000mAh Slim Power Bank', 'WorkForge', 'PWR-SLM-10K', 1799, 1299, 'In Stock', '14 x 7 x 1.6 cm', 'Black, White', '4.5', ['power bank', 'travel kit', 'tech gift'], 'Compact fast-charge power bank with Type-C input/output and branded sleeve packaging.', 'https://images.unsplash.com/photo-1609592806596-b43bada2f601'],
            ['office accessories', 'EcoOrbit Bamboo Desk Organizer', 'EcoOrbit', 'DSK-BAM-044', 1499, 1099, 'In Stock', '28 x 14 x 9 cm', 'Natural Bamboo', '4.6', ['desk organizer', 'office', 'eco'], 'Sustainable desktop organizer with phone stand, pen tray, and cable slots for hybrid teams.', 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd'],
            ['smart devices', 'WorkForge Smart Fitness Band', 'WorkForge', 'SMD-FIT-089', 2499, 1799, 'In Stock', '25 x 2 x 1 cm', 'Black, Blue, Rose', '4.4', ['smart band', 'wellness', 'employee benefit'], 'Wellness band with step tracking, heart-rate monitoring, and five-day battery life.', 'https://images.unsplash.com/photo-1576243345690-4e4b79b63288'],
            ['corporate gifts', 'PrintMint Conference Diary Set', 'PrintMint', 'OFC-DIA-202', 999, 749, 'In Stock', '24 x 18 x 4 cm', 'Royal Blue, Black', '4.5', ['diary', 'pen set', 'conference'], 'Hardbound A5 diary and metal pen set packed in a rigid presentation sleeve.', 'https://images.unsplash.com/photo-1517842645767-c639042777db'],
            ['electronics', 'LogiStyle Compact Wireless Keyboard', 'LogiStyle', 'KEY-WRL-064', 2299, 1699, 'Limited Stock', '29 x 13 x 2 cm', 'White, Graphite', '4.7', ['keyboard', 'office accessories', 'remote work'], 'Slim wireless keyboard with quiet keys, multi-device pairing, and long battery life.', 'https://images.unsplash.com/photo-1587829741301-dc798b83add3'],
            ['bottles', 'HydraNest Copper Finish Tumbler 900ml', 'HydraNest', 'BOT-TMB-900', 1599, 1199, 'In Stock', '22 x 10 x 10 cm', 'Copper, Steel', '4.8', ['tumbler', 'drinkware', 'premium gift'], 'Large insulated tumbler with handle grip, straw lid, and premium copper finish.', 'https://images.unsplash.com/photo-1523362628745-0c100150b504'],
            ['office accessories', 'EcoOrbit Recycled Notebook Pack', 'EcoOrbit', 'OFC-ECO-160', 699, 499, 'In Stock', '21 x 14 x 2 cm', 'Kraft, Forest Green', '4.4', ['notebook', 'sustainable', 'office'], 'Recycled paper notebook pack with elastic closure and custom logo print area.', 'https://images.unsplash.com/photo-1531346878377-a5be20888e57'],
        ];

        foreach ($products as $index => $product) {
            [$categoryKey, $name, $brand, $sku, $mrp, $offer, $stock, $dimensions, $colors, $rating, $tags, $description, $image] = $product;
            $productId = $this->row('subscriber_products', ['user_id' => $subscriberId, 'sku' => $sku], [
                'user_id' => $subscriberId,
                'category_id' => $categories[$categoryKey],
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::lower($sku),
                'sku' => $sku,
                'mrp' => $mrp,
                'offer_price' => $offer,
                'currency' => 'INR',
                'thumbnail' => $image,
                'short_description' => $description,
                'full_description' => $description . ' Optimized demo listing with responsive images, realistic B2B pricing, attributes, and export-ready metadata.',
                'tags' => json_encode($tags),
                'featured' => $index < 4,
                'status' => 'active',
                'approval_status' => 'approved',
                'sort_order' => $index,
            ]);

            foreach ([$image, $image . '?crop=entropy', $image . '?fit=max'] as $galleryIndex => $galleryImage) {
                $this->row('subscriber_product_images', ['subscriber_product_id' => $productId, 'sort_order' => $galleryIndex], [
                    'subscriber_product_id' => $productId,
                    'image_path' => $galleryImage,
                    'alt_text' => $name . ' view ' . ($galleryIndex + 1),
                    'is_primary' => $galleryIndex === 0,
                    'sort_order' => $galleryIndex,
                ]);
            }

            foreach ([
                'Brand' => $brand,
                'Category' => Str::headline($categoryKey),
                'Stock Status' => $stock,
                'Dimensions' => $dimensions,
                'Color Variants' => $colors,
                'Rating' => $rating . ' / 5',
                'MOQ' => ($index % 3 === 0 ? 25 : 50) . ' pcs',
                'Branding Method' => $index % 2 === 0 ? 'Laser Engraving' : 'UV Print',
            ] as $attributeName => $value) {
                $this->row('subscriber_product_attribute_values', ['subscriber_product_id' => $productId, 'attribute_id' => $attributes[$attributeName]], [
                    'subscriber_product_id' => $productId,
                    'attribute_id' => $attributes[$attributeName],
                    'value' => $value,
                ]);
            }
        }
    }

    private function seedSubscriber(): int
    {
        $userId = $this->row('users', ['email' => 'demo@catasky.test'], [
            'name' => 'CATASKY Demo Subscriber',
            'password' => Hash::make('password', ['rounds' => 4]),
            'email_verified_at' => Carbon::now(),
        ]);

        if (class_exists(Role::class)) {
            $role = Role::firstOrCreate(['name' => 'Subscriber', 'guard_name' => 'web']);
            $role->users()->syncWithoutDetaching([$userId]);
        }

        if (Schema::hasTable('subscriber_profiles')) {
            $this->row('subscriber_profiles', ['user_id' => $userId], [
                'user_id' => $userId,
                'company_name' => 'CATASKY Performance Demo',
                'company_slug' => 'catasky-performance-demo',
                'phone' => '+91 98765 43210',
                'whatsapp_number' => '919876543210',
                'email_for_inquiries' => 'sales@catasky.test',
                'website' => 'https://demo.catasky.test',
                'address' => 'Demo Business Park, Sector 63',
                'city' => 'Noida',
                'state' => 'Uttar Pradesh',
                'country' => 'India',
                'pincode' => '201301',
                'bio' => 'High-performance realistic demo catalogue for preview, PDF, and image sharing flows.',
                'primary_color' => '#0F766E',
                'secondary_color' => '#F59E0B',
                'status' => 'approved',
                'approval_status' => 'approved',
                'is_verified' => 1,
            ]);
        }

        return $userId;
    }

    private function seedAttributes(int $userId): array
    {
        $groupId = $this->row('attribute_groups', ['user_id' => $userId, 'slug' => 'demo-commerce-specs'], [
            'user_id' => $userId,
            'name' => 'Demo Commerce Specs',
            'slug' => 'demo-commerce-specs',
            'description' => 'Realistic preview and export attributes.',
            'sort_order' => 0,
            'is_active' => 1,
        ]);

        $ids = [];
        foreach (['Brand', 'Category', 'Stock Status', 'Dimensions', 'Color Variants', 'Rating', 'MOQ', 'Branding Method'] as $index => $name) {
            $ids[$name] = $this->row('attributes', ['user_id' => $userId, 'slug' => Str::slug($name)], [
                'user_id' => $userId,
                'attribute_group_id' => $groupId,
                'name' => $name,
                'slug' => Str::slug($name),
                'type' => $name === 'Rating' ? 'text' : 'text',
                'is_searchable' => in_array($name, ['Brand', 'Category', 'Stock Status'], true),
                'show_in_pdf' => 1,
                'show_in_share' => 1,
                'is_active' => 1,
                'approval_status' => 'approved',
                'sort_order' => $index,
            ]);
        }

        return $ids;
    }

    private function seedCategories(): array
    {
        $categories = [];
        foreach (['electronics', 'fashion', 'corporate gifts', 'bottles', 'backpacks', 'gadgets', 'office accessories', 'smart devices'] as $index => $name) {
            $categories[$name] = $this->row('categories', ['slug' => Str::slug($name)], [
                'name' => Str::headline($name),
                'slug' => Str::slug($name),
                'status' => 1,
                'sort_order' => $index,
            ]);
        }

        return $categories;
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
        $query = DB::table($table);
        foreach ($this->onlyColumns($table, $where) as $column => $value) {
            $query->where($column, $value);
        }

        return ($id = $query->value('id')) ? (int) $id : null;
    }

    private function onlyColumns(string $table, array $data): array
    {
        if (! isset($this->columns[$table])) {
            $this->columns[$table] = Schema::getColumnListing($table);
        }

        return array_intersect_key($data, array_flip($this->columns[$table]));
    }
}
