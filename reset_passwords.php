<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$emails = ['admin@demo.com', 'secretaire@demo.com', 'comptable@demo.com'];
$hash = bcrypt('password');

foreach ($emails as $email) {
    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        // Try setting both if they exist
        try {
            \Illuminate\Support\Facades\DB::table('utilisateurs')
                ->where('id', $user->id)
                ->update([
                    'password' => $hash,
                    'mot_de_passe_hash' => $hash
                ]);
            echo "Password reset to 'password' for $email\n";
        } catch (\Exception $e) {
            echo "Error for $email: " . $e->getMessage() . "\n";
        }
    } else {
        echo "User $email not found.\n";
    }
}
