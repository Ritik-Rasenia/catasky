<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "--- seeded users & roles ---\n";
foreach (User::all() as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
}
