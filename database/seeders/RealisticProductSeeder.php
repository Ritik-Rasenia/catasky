<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealisticProductSeeder extends Seeder
{
    public function run(): void
    {
        $brands = collect([
            ['name' => 'Apex Gifts', 'image' => 'https://logo.clearbit.com/apexgroup.com'],
            ['name' => 'Urban Utility', 'image' => 'https://logo.clearbit.com/urbanoutfitters.com'],
            ['name' => 'HydraPro', 'image' => 'https://logo.clearbit.com/stanley1913.com'],
            ['name' => 'WorkMate', 'image' => 'https://logo.clearbit.com/logitech.com'],
            ['name' => 'EcoDesk', 'image' => 'https://logo.clearbit.com/ecovadis.com'],
            ['name' => 'PrintCraft', 'image' => 'https://logo.clearbit.com/canva.com'],
        ])->mapWithKeys(function (array $brand) {
            $model = Brand::updateOrCreate(
                ['slug' => Str::slug($brand['name'])],
                ['name' => $brand['name'], 'image' => $brand['image'], 'status' => 1]
            );

            return [$brand['name'] => $model];
        });

        $catalogue = [
            [
                'category' => 'Corporate Drinkware',
                'subcategory' => 'Vacuum Bottles',
                'child' => 'Steel Bottles',
                'products' => [
                    ['HydraPro Dori 500ml Vacuum Bottle', 'HydraPro', 'dori-bottle-ss-vacuum-500ml_18052026_thumbnail.jpg', 549, 'DRK-DORI-500', 'Double-wall stainless steel bottle with powder coated body and leak-proof carry lid.', 'Capacity: 500ml; Material: 304 stainless steel; Branding: laser engraving; MOQ: 50 pcs', 'drinkware, steel bottle, employee gift'],
                    ['HydraPro 1200ml Office Tumbler', 'HydraPro', '1200ml-tumbler_18052026_thumbnail.jpg', 799, 'DRK-TMB-1200', 'Large capacity desk tumbler with straw lid, handle grip, and all-day cold retention.', 'Capacity: 1200ml; Lid: splash resistant; Finish: matte; MOQ: 30 pcs', 'tumbler, hydration, corporate gifting'],
                    ['Coffee Warmer Smart Mug', 'Urban Utility', 'coffee-warmer-mug_18052026_thumbnail.jpg', 1299, 'DRK-WRM-001', 'USB powered coffee warmer mug set designed for office desks and remote work kits.', 'Power: USB; Temperature: warm hold mode; Gift box: included; MOQ: 20 pcs', 'coffee mug, desk gadget, welcome kit'],
                    ['Dribble Glass Container 370ml', 'Apex Gifts', 'dribble-glass-container-370_18052026_thumbnail.jpg', 349, 'DRK-GLS-370', 'Clear glass snack container with bamboo-style lid for premium pantry gifting.', 'Capacity: 370ml; Material: glass; Lid: sealed top; MOQ: 100 pcs', 'glass container, eco gift, pantry'],
                ],
            ],
            [
                'category' => 'Executive Bags',
                'subcategory' => 'Laptop Backpacks',
                'child' => 'Daily Commute',
                'products' => [
                    ['Urban Utility Executive Backpack', 'Urban Utility', '1779255229_-OqLEqMBfvBRsa1_zDYp.jpg', 1899, 'BAG-EXE-001', 'Structured laptop backpack with padded sleeves, organizer pockets, and water-resistant shell.', 'Laptop: up to 15.6 inch; Material: polyester; Branding: metal badge; MOQ: 25 pcs', 'backpack, laptop bag, executive gift'],
                    ['Easy Bottle Holder Sling', 'Apex Gifts', 'easy-bottle-holder_18052026_thumbnail.jpg', 199, 'BAG-BTL-001', 'Compact bottle holder sling for events, marathons, and outdoor employee engagement kits.', 'Fit: up to 1L bottles; Strap: adjustable; Print: single color; MOQ: 200 pcs', 'sling, event kit, bottle holder'],
                    ['Blue Diary Travel Set 2-in-1', 'PrintCraft', '2-in-1-blue-diary-set_18052026_thumbnail.jpg', 699, 'KIT-DIA-2IN1', 'Premium diary and pen set packed for onboarding, conferences, and client meetings.', 'Includes: diary and pen; Box: rigid gift box; Branding: foil print; MOQ: 50 sets', 'diary set, onboarding, client gift'],
                    ['Corporate Blue Combo 3-in-1', 'PrintCraft', '3-in-1-blue-set_18052026_thumbnail.jpg', 999, 'KIT-BLU-3IN1', 'Curated corporate gift set with diary, pen, and bottle in a presentation box.', 'Includes: diary, pen, bottle; Branding: logo print; MOQ: 40 sets', 'gift set, corporate kit, premium'],
                ],
            ],
            [
                'category' => 'Tech Accessories',
                'subcategory' => 'Charging & Audio',
                'child' => 'Desk Tech',
                'products' => [
                    ['3-in-1 Iron Charger Stand', 'WorkMate', '3-in-1-iron-charger_18052026_thumbnail.jpg', 1499, 'TEC-CHG-3IN1', 'Desk charging stand for phone, earbuds, and watch with a compact metal finish.', 'Input: Type-C; Output: multi-device; Finish: iron grey; MOQ: 20 pcs', 'charger, desk setup, tech gift'],
                    ['WorkMate Wireless Earbuds Pro', 'WorkMate', 'jp-01_18052026_thumbnail.jpg', 1199, 'AUD-JP-01', 'Compact true wireless earbuds with charging case and optional logo print sleeve.', 'Playback: up to 20 hours; Bluetooth: 5.3; Branding: sleeve print; MOQ: 50 pcs', 'earbuds, audio, employee reward'],
                    ['WorkMate Mini Power Bank', 'WorkMate', 'jp-02_18052026_thumbnail.jpg', 899, 'TEC-PB-10K', 'Pocket-friendly 10000mAh power bank with fast charge support for business travel kits.', 'Capacity: 10000mAh; Ports: Type-C and USB-A; MOQ: 50 pcs', 'power bank, travel kit, tech'],
                    ['Smart Desk Cable Organizer', 'EcoDesk', 'jp-03_18052026_thumbnail.jpg', 249, 'DSK-CBL-003', 'Reusable magnetic cable organizer for tidy workstations and remote work hampers.', 'Material: silicone magnet; Pack: 3 pieces; MOQ: 100 packs', 'desk, cable organizer, remote work'],
                ],
            ],
            [
                'category' => 'Awards & Recognition',
                'subcategory' => 'Trophies',
                'child' => 'Crystal Awards',
                'products' => [
                    ['Crystal Star Achievement Trophy', 'Apex Gifts', '3354_18052026_thumbnail.jpg', 1399, 'AWD-CRY-3354', 'Premium crystal award with laser engraving area for quarterly recognition programs.', 'Material: optical crystal; Engraving: laser; MOQ: 10 pcs', 'award, trophy, recognition'],
                    ['Gold Accent Leadership Award', 'Apex Gifts', '3356-black_18052026_thumbnail.jpg', 1599, 'AWD-LDR-3356', 'Black and gold finish trophy for leadership, sales excellence, and annual awards.', 'Finish: black-gold; Base: weighted; MOQ: 10 pcs', 'leadership award, trophy, sales'],
                    ['Modern Acrylic Recognition Plaque', 'PrintCraft', '3374-black_18052026_thumbnail.jpg', 899, 'AWD-ACR-3374', 'Clean acrylic plaque with high-contrast branding panel for team appreciation.', 'Material: acrylic; Printing: UV; MOQ: 25 pcs', 'plaque, acrylic award, appreciation'],
                    ['Wood Finish Appreciation Trophy', 'PrintCraft', '3374-brown_18052026_thumbnail.jpg', 1099, 'AWD-WOD-3374', 'Warm wood finish appreciation trophy suited for long service and partner awards.', 'Finish: wood texture; Plate: engraved metal; MOQ: 15 pcs', 'wood trophy, partner award, service'],
                ],
            ],
            [
                'category' => 'Office Essentials',
                'subcategory' => 'Desk Organizers',
                'child' => 'Productivity Kits',
                'products' => [
                    ['EcoDesk Premium Notebook', 'EcoDesk', '98_18052026_thumbnail.jpg', 299, 'OFC-NBK-098', 'Hardbound notebook with smooth ruled pages for conferences and employee onboarding.', 'Pages: 160; Size: A5; Branding: front print; MOQ: 100 pcs', 'notebook, office, onboarding'],
                    ['Executive Pen Set', 'PrintCraft', '100_18052026_thumbnail.jpg', 399, 'OFC-PEN-100', 'Weighted metal pen set with presentation sleeve for client meetings and seminars.', 'Ink: blue; Material: metal; Branding: laser mark; MOQ: 100 pcs', 'pen set, client gift, seminar'],
                    ['Desk Essentials Organizer Kit', 'EcoDesk', '125_18052026_thumbnail.jpg', 649, 'OFC-ORG-125', 'Compact desk organizer kit for hybrid teams with stationery and cable storage.', 'Includes: organizer, sticky notes, clips; MOQ: 75 kits', 'desk organizer, office kit, hybrid work'],
                    ['Meeting Room Utility Set', 'Apex Gifts', '170_18052026_thumbnail.jpg', 549, 'OFC-MTG-170', 'Practical meeting room kit with writing tools and accessories for training events.', 'Pack: 5 items; Box: kraft; MOQ: 100 sets', 'meeting kit, training, stationery'],
                ],
            ],
            [
                'category' => 'Premium Gift Sets',
                'subcategory' => 'Welcome Kits',
                'child' => 'Employee Joining',
                'products' => [
                    ['New Joiner Welcome Kit Classic', 'Apex Gifts', '360_18052026_thumbnail.jpg', 1499, 'KIT-NJ-360', 'Ready-to-ship welcome kit with bottle, notebook, pen, and custom greeting card.', 'Items: 4; Packaging: rigid box; MOQ: 30 kits', 'welcome kit, new joiner, HR'],
                    ['Executive Black Gift Box', 'Urban Utility', '580_18052026_thumbnail.jpg', 2199, 'KIT-BLK-580', 'Premium black gift box curated for senior employees, partners, and VIP clients.', 'Items: 5; Box: magnetic closure; MOQ: 20 kits', 'executive gift, client kit, premium'],
                    ['Festive Corporate Hamper', 'Apex Gifts', '600_18052026_thumbnail.jpg', 1799, 'KIT-FST-600', 'Festive hamper with practical office products and a personalized brand message card.', 'Items: assorted; Card: personalized; MOQ: 25 hampers', 'festive hamper, corporate gifting, Diwali'],
                    ['Sustainable Office Starter Pack', 'EcoDesk', '638_18052026_thumbnail.jpg', 999, 'KIT-ECO-638', 'Eco-conscious starter pack with reusable products for sustainability campaigns.', 'Material: mixed eco materials; Packaging: kraft; MOQ: 50 packs', 'eco kit, sustainability, reusable'],
                ],
            ],
        ];

        foreach ($catalogue as $group) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($group['category'])],
                ['name' => $group['category'], 'status' => 1]
            );

            $subcategory = Subcategory::updateOrCreate(
                ['slug' => Str::slug($group['subcategory'])],
                ['category_id' => $category->id, 'name' => $group['subcategory'], 'status' => 1]
            );

            $child = ChildCategory::updateOrCreate(
                ['slug' => Str::slug($group['child'])],
                [
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'name' => $group['child'],
                    'status' => 1,
                ]
            );

            foreach ($group['products'] as $index => $item) {
                [$name, $brandName, $thumbnail, $price, $partCode, $short, $specs, $tags] = $item;

                $product = Product::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'brand_id' => $brands[$brandName]->id,
                        'category_id' => $category->id,
                        'subcategory_id' => $subcategory->id,
                        'child_category_id' => $child->id,
                        'name' => $name,
                        'part_code' => $partCode,
                        'part_number' => 'PN-' . $partCode,
                        'thumbnail' => $thumbnail,
                        'short_description' => $short,
                        'variant' => "Standard: Rs. {$price}\nLogo Printed: Rs. " . ($price + 80) . "\nPremium Gift Box: Rs. " . ($price + 180),
                        'price' => $price,
                        'specifications' => $specs,
                        'tags' => $tags,
                        'packaging' => 'Packed in brand-ready corrugated shipping cartons. Custom sleeve and gift box available on request.',
                        'additional_info' => 'Testing sample product. Prices are indicative and can be edited from the admin panel.',
                        'featured' => $index < 2 ? 1 : 0,
                        'is_future' => 0,
                        'status' => 1,
                        'meta_title' => $name . ' | Catasky B2B Catalogue',
                        'meta_description' => $short,
                        'meta_keywords' => $tags,
                    ]
                );

                ProductImage::where('product_id', $product->id)->delete();
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $thumbnail,
                ]);
            }
        }
    }
}
