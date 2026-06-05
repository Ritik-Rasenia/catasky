<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;

echo "--- database roles ---\n";
foreach (Role::all() as $role) {
    echo "ID: {$role->id}, Name: {$role->name}, Guard: {$role->guard_name}\n";
}
