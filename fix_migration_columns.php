<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$migrations = [
    '2026_05_05_114753_add_additional_fields_to_products_table',
    '2026_05_06_052749_add_child_category_id_to_products_table',
    '2026_05_07_075936_add_seo_fields_to_products_table',
    '2026_05_07_092246_add_part_code_to_products_table',
    '2026_05_11_085204_add_is_future_to_products_table',
    '2026_05_19_111215_add_price_to_products_table',
];

foreach ($migrations as $migration) {
    Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%'.$migration.'%')->delete();
    echo "Deleted migration entry for $migration\n";
}
