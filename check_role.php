<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::first();
if ($user) {
    echo "User: " . $user->nom . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Cabinet ID: " . $user->cabinet_id . "\n";
} else {
    echo "No user found\n";
}
