<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Str;

$dummyData = [
    1 => ['T-Shirts', 'Polos', 'Sweaters', 'Caps'],
    2 => ['Headphones', 'Mice', 'Keyboards', 'Chargers'],
    3 => ['Bottles', 'Mugs', 'Tumblers'],
    4 => ['Trophies', 'Plaques', 'Medals'],
    5 => ['Backpacks', 'Tote Bags', 'Duffel Bags'],
    6 => ['Notebooks', 'Pens', 'Organizers'],
    7 => ['Fitness', 'Travel', 'Wellness'],
    8 => ['Panduit', 'Legrand']
];

foreach ($dummyData as $catId => $subs) {
    $cat = Category::find($catId);
    if ($cat) {
        echo "Seeding subcategories for Category: {$cat->name}...\n";
        foreach ($subs as $subName) {
            Subcategory::updateOrCreate(
                ['category_id' => $catId, 'name' => $subName],
                [
                    'slug' => Str::slug($subName),
                    'status' => 1
                ]
            );
            echo "  - Added subcategory: {$subName}\n";
        }
    }
}
echo "Seeding completed successfully!\n";
