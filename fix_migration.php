<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$migration = '2026_05_05_085344_create_products_table';
Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%'.$migration.'%')->delete();
echo "Deleted migration entry for $migration\n";
