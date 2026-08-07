<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'informatique@gelsabinet.com';
$u = \App\Models\User::where('account_type', 'informaticien')->first();
if (!$u) {
    $u = \App\Models\User::create([
        'name' => 'Informatique',
        'prenom' => 'Pôle',
        'email' => $email,
        'password' => bcrypt('password'),
        'role' => 'informaticien',
        'account_type' => 'informaticien',
        'onboarding_token' => null,
    ]);
} else {
    $u->update([
        'email' => $email,
        'password' => bcrypt('password')
    ]);
}
echo $u->email;
