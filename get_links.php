<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invs = \App\Models\EntrepriseInvitation::orderBy('created_at', 'desc')->take(5)->get();
foreach($invs as $i) {
    echo "Email: " . $i->email . "\n";
    echo "Lien d'acceptation: " . route('invitation.entreprise.accept', ['token' => $i->token]) . "\n\n";
}
