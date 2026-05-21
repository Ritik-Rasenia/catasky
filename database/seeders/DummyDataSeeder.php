<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Models\Enquiry;
use App\Models\Solution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        ProductImage::truncate();
        ChildCategory::truncate();
        Subcategory::truncate();
        Category::truncate();
        Brand::truncate();
        Setting::truncate();
        Enquiry::truncate();
        Solution::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Settings
        Setting::create([
            'site_title' => 'Catasky Premium',
            'site_description' => 'Premium B2B Corporate Catalogue & Gifting Platform',
            'email' => 'corporate@catasky.com',
            'phone' => '+91-919871376205',
            'primary_color' => '#4F46E5',
            'secondary_color' => '#10B981',
            'font_family' => 'Outfit',
        ]);

        // 2. Solutions
        $solutionsList = [
            'New Joiner Welcome Kit',
            'Premium Executive Gifting',
            'Annual Excellence Awards',
            'Eco-Friendly Green Initiative',
            'Corporate Event SwagBag'
        ];
        $solutions = [];
        foreach ($solutionsList as $solName) {
            $solutions[] = Solution::create([
                'name' => $solName,
                'slug' => Str::slug($solName),
                'status' => 1
            ]);
        }

        // 3. Brands
        $brandNames = ['Patagonia', 'Nike', 'Stanley', 'Logitech', 'Bose', 'Apple', 'Sennheiser', 'Parker'];
        $brands = [];
        foreach ($brandNames as $name) {
            $brands[$name] = Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'image' => 'https://logo.clearbit.com/' . Str::lower($name) . '.com',
                'status' => 1
            ]);
        }

        // 4. Categories, Subcategories, Child Categories, & Products
        $catStructure = [
            [
                'name' => 'Apparel & Wearables',
                'slug' => 'apparel',
                'subcategories' => [
                    [
                        'name' => 'Outerwear',
                        'slug' => 'outerwear',
                        'child_categories' => ['Hoodies & Jackets', 'Sweaters & Pullovers']
                    ],
                    [
                        'name' => 'Corporate Casuals',
                        'slug' => 'corporate-casuals',
                        'child_categories' => ['Polos', 'Oversized Tees']
                    ]
                ],
                'products' => [
                    [
                        'name' => 'Patagonia Better Sweater Jacket',
                        'brand' => 'Patagonia',
                        'sub' => 'Outerwear',
                        'child' => 'Sweaters & Pullovers',
                        'price' => '₹8,500 - ₹12,000',
                        'img' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=800&q=80',
                        'gallery' => [
                            'https://images.unsplash.com/photo-1544022613-e87ca75a784a?auto=format&fit=crop&w=800&q=80',
                            'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?auto=format&fit=crop&w=800&q=80'
                        ],
                        'part_code' => 'PC-PAT-091',
                        'part_number' => 'PN-910239-M',
                        'short' => 'Warm, low-bulk full-zip jacket made with soft knitted polyester fleece. Fabric dyed with a low-impact process.',
                        'specs' => "Material: 100% Recycled Polyester Fleece\nWeight: 580g (20.5 oz)\nFit: Regular Fit with raglan sleeves\nClosure: Front full-zip with chin guard\nPockets: Vertical left-chest zipper pocket, zippered handwarmer pockets",
                        'tags' => 'sustainable, corporate premium, winterwear, patagonia',
                        'packaging' => 'Shipped in biodegradable cornstarch polybag with a premium Catasky craft paper wrap.',
                        'info' => 'Custom chest embroidery available with a 15-day turn-around. Min Order: 25 Units.'
                    ],
                    [
                        'name' => 'Nike Dri-FIT Tech Polo',
                        'brand' => 'Nike',
                        'sub' => 'Corporate Casuals',
                        'child' => 'Polos',
                        'price' => '₹3,200 - ₹4,500',
                        'img' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&w=800&q=80',
                        'gallery' => [
                            'https://images.unsplash.com/photo-1479064555552-3ef4979f8908?auto=format&fit=crop&w=800&q=80'
                        ],
                        'part_code' => 'PC-NKE-442',
                        'part_number' => 'PN-NKDF-POLO',
                        'short' => 'Elite moisture-wicking golf polo designed for maximum corporate versatility and high athletic performance.',
                        'specs' => "Fabric: 100% Recycled Polyester Dri-FIT\nTechnology: Sweat-wicking micro-mesh fibers\nCollar: Self-fabric collar with 3-button placket\nLogo Customisation: Right-chest or sleeve print/embroidery",
                        'tags' => 'activewear, breathable, corporate uniform, nike',
                        'packaging' => 'Individually folded in recyclable matte B2B cardboard boxes.',
                        'info' => 'Ideal for company off-sites and corporate gifting. Lead time: 10 days.'
                    ]
                ]
            ],
            [
                'name' => 'Tech & Smart Gadgets',
                'slug' => 'tech',
                'subcategories' => [
                    [
                        'name' => 'Premium Audio',
                        'slug' => 'premium-audio',
                        'child_categories' => ['Noise Cancelling Headphones', 'Wireless Earbuds']
                    ],
                    [
                        'name' => 'Desk Equipment',
                        'slug' => 'desk-equipment',
                        'child_categories' => ['Wireless Mice', 'Mechanical Keyboards']
                    ]
                ],
                'products' => [
                    [
                        'name' => 'Bose QuietComfort Ultra Headphones',
                        'brand' => 'Bose',
                        'sub' => 'Premium Audio',
                        'child' => 'Noise Cancelling Headphones',
                        'price' => '₹35,900',
                        'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                        'gallery' => [
                            'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=800&q=80'
                        ],
                        'part_code' => 'PC-BOS-QC',
                        'part_number' => 'PN-BOSE-QCU-001',
                        'short' => 'World-class noise cancelling headphones featuring immersive spatial audio and premium leatherette earcups.',
                        'specs' => "Audio: Custom Bose Spatial Audio Engine\nBattery Life: Up to 24 hours of playback\nCharging: USB-C Fast Charge (15 mins for 3 hours)\nMicrophones: 12-microphone array for professional calls",
                        'tags' => 'audio, executive gift, premium, noise cancelling',
                        'packaging' => 'Sleek luxury Bose retail box enclosed in a custom debossed magnetic black gift box.',
                        'info' => 'Laser engraving available on the custom travel case. Min Order: 5 Units.'
                    ],
                    [
                        'name' => 'Logitech MX Master 3S Mouse',
                        'brand' => 'Logitech',
                        'sub' => 'Desk Equipment',
                        'child' => 'Wireless Mice',
                        'price' => '₹10,995',
                        'img' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=800&q=80',
                        'gallery' => [],
                        'part_code' => 'PC-LOG-MX3S',
                        'part_number' => 'PN-MX-MSTR-3S',
                        'short' => 'Ergonomic performance mouse with 8K DPI tracking and hyper-fast MagSpeed electromagnetic scrolling.',
                        'specs' => "DPI: 200 - 8000 DPI adjustable sensors\nScroll: MagSpeed electromagnetic wheel\nBattery: 70 days on full charge, quick charge support\nButtons: 7 programmable gesture buttons",
                        'tags' => 'productivity, ergonomic, developer gift, logitech',
                        'packaging' => 'Ecofriendly kraft paper box with Catasky branded paper tape sealing.',
                        'info' => 'Custom metal logo sticker attachment on the side panel is optional. Min Order: 10 Units.'
                    ]
                ]
            ],
            [
                'name' => 'Premium Drinkware',
                'slug' => 'drinkware',
                'subcategories' => [
                    [
                        'name' => 'Vacuum Flasks',
                        'slug' => 'vacuum-flasks',
                        'child_categories' => ['Tumblers & Straws', 'Travel Bottles']
                    ]
                ],
                'products' => [
                    [
                        'name' => 'Stanley Quencher H2.0 FlowState',
                        'brand' => 'Stanley',
                        'sub' => 'Vacuum Flasks',
                        'child' => 'Tumblers & Straws',
                        'price' => '₹4,500 - ₹6,000',
                        'img' => 'https://images.unsplash.com/photo-1602143307185-84e030739987?auto=format&fit=crop&w=800&q=80',
                        'gallery' => [
                            'https://images.unsplash.com/photo-1574680077532-f2bf135987f6?auto=format&fit=crop&w=800&q=80'
                        ],
                        'part_code' => 'PC-STY-QCH',
                        'part_number' => 'PN-STANLEY-Q40',
                        'short' => 'Double-wall vacuum insulated B2B trending tumbler featuring a versatile FlowState 3-position rotating lid.',
                        'specs' => "Capacity: 40 oz (1.18 Liters)\nRetention: 11 hours cold, 2 days iced, 7 hours hot\nMaterial: 90% Recycled 18/8 Stainless Steel\nLid: FlowState rotating cover with splash guard",
                        'tags' => 'trending, drinkware, stainless steel, stanley',
                        'packaging' => 'Biodegradable protective sleeve in high-durability premium corrugated tube boxes.',
                        'info' => 'Full-circle high-resolution fiber laser engraving is highly recommended. Min Order: 50 Units.'
                    ]
                ]
            ]
        ];

        foreach ($catStructure as $cData) {
            // Create Category
            $category = Category::create([
                'name' => $cData['name'],
                'slug' => $cData['slug'],
                'status' => 1
            ]);

            $subCache = [];
            $childCache = [];

            // Pre-create Subcategories and Child categories
            foreach ($cData['subcategories'] as $subData) {
                $subcategory = Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subData['name'],
                    'slug' => $subData['slug'],
                    'status' => 1
                ]);
                $subCache[$subData['name']] = $subcategory;

                foreach ($subData['child_categories'] as $childName) {
                    $child = ChildCategory::create([
                        'category_id' => $category->id,
                        'subcategory_id' => $subcategory->id,
                        'name' => $childName,
                        'slug' => Str::slug($childName),
                        'status' => 1
                    ]);
                    $childCache[$childName] = $child;
                }
            }

            // Create Products
            foreach ($cData['products'] as $p) {
                $brand = $brands[$p['brand']];
                $subcategory = $subCache[$p['sub']];
                $child = $childCache[$p['child']] ?? null;

                $product = Product::create([
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'child_category_id' => $child?->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']),
                    'part_code' => $p['part_code'],
                    'part_number' => $p['part_number'],
                    'thumbnail' => $p['img'],
                    'short_description' => $p['short'],
                    'variant' => $p['price'],
                    'specifications' => $p['specs'],
                    'tags' => $p['tags'],
                    'packaging' => $p['packaging'],
                    'additional_info' => $p['info'],
                    'featured' => 1,
                    'is_future' => 0,
                    'status' => 1,
                    'meta_title' => 'Curated B2B | ' . $p['name'],
                    'meta_description' => 'Detailed product specification matrix, dimensions, pricing options, and corporate volume branding notes for ' . $p['name'] . '.',
                    'meta_keywords' => str_replace(' ', '', $p['tags'])
                ]);

                // Seed Gallery Images
                if (!empty($p['gallery'])) {
                    foreach ($p['gallery'] as $galleryUrl) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image' => $galleryUrl
                        ]);
                    }
                }

                // Sync 1-2 random B2B solutions to products
                $randomSolutions = collect($solutions)->random(rand(1, 2))->pluck('id')->toArray();
                $product->solutions()->sync($randomSolutions);
            }
        }
    }
}
