<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'informatique@gelsabinet.com';
$u = \App\Models\User::where('email', $email)->first();
if ($u) {
    // Due to the 'hashed' cast on the User model, we should set the raw string
    $u->update([
        'password' => 'password'
    ]);
    echo "Password updated successfully.";
} else {
    echo "User not found.";
}
