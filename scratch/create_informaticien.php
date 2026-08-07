<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);


$u = \App\Models\User::where('account_type', 'informaticien')->first();
if (!$u) {
    $u = \App\Models\User::create([
        'name' => 'Informatique',
        'prenom' => 'Support',
        'email' => 'informaticien@gelsabinet.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'account_type' => 'informaticien',
        'role' => 'informaticien',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
}
echo "Email: " . $u->email . "\n";
echo "Password: password123\n";
