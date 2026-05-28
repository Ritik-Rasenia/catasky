<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ChildCategory;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeOption;
use App\Models\SubcategoryAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DynamicAttributeSystemSeeder extends Seeder
{
    public function run(): void
    {
        $admin = \App\Models\User::role('Super Admin')->first();
        $adminId = $admin ? $admin->id : 1;

        // 1. Create or Find Standard Attribute Groups
        $sections = [
            'Basic Details',
            'Technical Specifications',
            'Packaging Details',
            'Compliance & Safety',
            'Commercial Details'
        ];

        $groups = [];
        foreach ($sections as $index => $name) {
            $groups[$name] = AttributeGroup::firstOrCreate(
                ['name' => $name],
                [
                    'user_id' => $adminId,
                    'slug' => Str::slug($name),
                    'is_active' => true,
                    'sort_order' => $index
                ]
            );
        }

        // 2. Industry Categories
        $industries = [
            'Fashion' => [
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=800&q=80',
                'subcategories' => [
                    'T-Shirts' => [
                        'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&q=80',
                        'types' => ['Round Neck T-Shirt', 'Polo T-Shirt'],
                        'attributes' => [
                            [
                                'name' => 'Size',
                                'type' => 'select',
                                'group' => 'Basic Details',
                                'placeholder' => 'Select Size',
                                'is_required' => true,
                                'is_filterable' => true,
                                'is_comparable' => true,
                                'is_variant_enabled' => true,
                                'options' => ['S', 'M', 'L', 'XL', 'XXL']
                            ],
                            [
                                'name' => 'Color',
                                'type' => 'color',
                                'group' => 'Basic Details',
                                'placeholder' => '#000000',
                                'is_required' => true,
                                'is_filterable' => true,
                                'is_comparable' => true,
                                'is_variant_enabled' => true,
                            ],
                            [
                                'name' => 'Fabric Material',
                                'type' => 'text',
                                'group' => 'Technical Specifications',
                                'placeholder' => 'e.g. 100% Organic Cotton',
                                'is_required' => true,
                                'is_filterable' => true,
                                'is_comparable' => true,
                            ],
                            [
                                'name' => 'Fit Type',
                                'type' => 'text',
                                'group' => 'Technical Specifications',
                                'placeholder' => 'e.g. Regular Fit, Slim Fit',
                                'is_required' => false,
                                'is_filterable' => true,
                                'is_comparable' => false,
                            ],
                            [
                                'name' => 'Sleeve Type',
                                'type' => 'text',
                                'group' => 'Technical Specifications',
                                'placeholder' => 'e.g. Short Sleeve, Full Sleeve',
                                'is_required' => false,
                                'is_filterable' => false,
                                'is_comparable' => false,
                            ]
                        ]
                    ]
                ]
            ],
            'Chemicals' => [
                'image' => 'https://images.unsplash.com/photo-1532187863486-abf9d39d66e8?auto=format&fit=crop&w=800&q=80',
                'subcategories' => [
                    'Cleaning Liquid' => [
                        'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80',
                        'types' => ['Industrial Cleaner', 'Domestic Liquid'],
                        'attributes' => [
                            [
                                'name' => 'pH Value',
                                'type' => 'number',
                                'group' => 'Technical Specifications',
                                'placeholder' => 'e.g. 7',
                                'is_required' => true,
                                'is_filterable' => true,
                                'is_comparable' => true,
                            ],
                            [
                                'name' => 'Chemical Concentration',
                                'type' => 'text',
                                'group' => 'Technical Specifications',
                                'placeholder' => 'e.g. 15% Active Ingredients',
                                'is_required' => true,
                                'is_filterable' => false,
                                'is_comparable' => true,
                            ],
                            [
                                'name' => 'MSDS File',
                                'type' => 'file',
                                'group' => 'Compliance & Safety',
                                'placeholder' => 'Upload MSDS Safety Document',
                                'is_required' => true,
                                'is_filterable' => false,
                                'is_comparable' => false,
                            ],
                            [
                                'name' => 'Shelf Life',
                                'type' => 'text',
                                'group' => 'Packaging Details',
                                'placeholder' => 'e.g. 24 Months',
                                'is_required' => false,
                                'is_filterable' => true,
                                'is_comparable' => false,
                            ]
                        ]
                    ]
                ]
            ],
            'Electronics' => [
                'image' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=800&q=80',
                'subcategories' => [
                    'CCTV Camera' => [
                        'image' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=800&q=80',
                        'types' => ['Dome Camera', 'Bullet Camera'],
                        'attributes' => [
                            [
                                'name' => 'Resolution',
                                'type' => 'select',
                                'group' => 'Technical Specifications',
                                'placeholder' => 'Select Resolution',
                                'is_required' => true,
                                'is_filterable' => true,
                                'is_comparable' => true,
                                'options' => ['2MP (1080p)', '4MP (2K)', '8MP (4K UHD)']
                            ],
                            [
                                'name' => 'Lens Type',
                                'type' => 'text',
                                'group' => 'Technical Specifications',
                                'placeholder' => 'e.g. 2.8mm Fixed, 3.6mm Varifocal',
                                'is_required' => false,
                                'is_filterable' => false,
                                'is_comparable' => true,
                            ],
                            [
                                'name' => 'Night Vision Support',
                                'type' => 'boolean',
                                'group' => 'Technical Specifications',
                                'placeholder' => '',
                                'is_required' => true,
                                'is_filterable' => true,
                                'is_comparable' => true,
                            ],
                            [
                                'name' => 'Connectivity',
                                'type' => 'select',
                                'group' => 'Technical Specifications',
                                'placeholder' => 'Select Connectivity',
                                'is_required' => true,
                                'is_filterable' => true,
                                'is_comparable' => true,
                                'options' => ['Wi-Fi Wireless', 'PoE Wired', '4G LTE Cellular']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        foreach ($industries as $catName => $catData) {
            // Category
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($catName)],
                [
                    'name' => $catName,
                    'image' => $catData['image'],
                    'status' => 1
                ]
            );
            $this->command?->info("Seeded Category: {$catName}");

            foreach ($catData['subcategories'] as $subcatName => $subcatData) {
                // Subcategory
                $subcategory = Subcategory::updateOrCreate(
                    ['category_id' => $category->id, 'slug' => Str::slug($subcatName)],
                    [
                        'name' => $subcatName,
                        'image' => $subcatData['image'],
                        'status' => 1
                    ]
                );
                $this->command?->info("  -> Seeded Subcategory: {$subcatName}");

                // Child Categories (Product Types)
                foreach ($subcatData['types'] as $typeName) {
                    ChildCategory::updateOrCreate(
                        ['category_id' => $category->id, 'subcategory_id' => $subcategory->id, 'slug' => Str::slug($typeName)],
                        [
                            'name' => $typeName,
                            'status' => 1
                        ]
                    );
                }
                $this->command?->info("      -> Seeded Product Types (Child Categories)");

                // Reusable Global Attributes & subcategory associations
                foreach ($subcatData['attributes'] as $index => $attrData) {
                    $group = $groups[$attrData['group']];
                    
                    $slug = Str::slug($attrData['name']);
                    $attribute = Attribute::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'user_id' => $adminId,
                            'attribute_group_id' => $group->id,
                            'name' => $attrData['name'],
                            'type' => $attrData['type'],
                            'placeholder' => $attrData['placeholder'] ?? '',
                            'is_required' => $attrData['is_required'] ?? false,
                            'is_searchable' => true,
                            'is_filterable' => $attrData['is_filterable'] ?? false,
                            'is_comparable' => $attrData['is_comparable'] ?? false,
                            'is_variant_enabled' => $attrData['is_variant_enabled'] ?? false,
                            'is_global' => true,
                            'approval_status' => 'approved',
                            'is_active' => true,
                            'sort_order' => $index,
                        ]
                    );

                    // Options for select/multiselect
                    if (isset($attrData['options'])) {
                        $attribute->options()->delete();
                        foreach ($attrData['options'] as $optIndex => $optText) {
                            AttributeOption::create([
                                'attribute_id' => $attribute->id,
                                'value' => Str::slug($optText),
                                'label' => $optText,
                                'sort_order' => $optIndex,
                            ]);
                        }
                    }

                    // Map to Subcategory
                    SubcategoryAttribute::updateOrCreate(
                        [
                            'subcategory_id' => $subcategory->id,
                            'attribute_id' => $attribute->id
                        ],
                        [
                            'attribute_group_id' => $group->id,
                            'is_required' => $attrData['is_required'] ?? false,
                            'sort_order' => $index
                        ]
                    );
                }
                $this->command?->info("      -> Mapped global attributes at Subcategory level");
            }
        }

        $this->command?->info("Dynamic attribute system examples seeded successfully!");
    }
}
