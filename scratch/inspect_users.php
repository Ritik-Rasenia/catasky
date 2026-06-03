<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "--- ALL USERS ---\n";
foreach (User::all() as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}\n";
}
