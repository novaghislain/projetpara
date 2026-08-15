<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@demo.com')->first();
$entrepriseId = "019ffbc8-a3cc-719c-b48f-c89906b6b4ee"; // from test_ids.php earlier

echo "Simulating invitation from Entreprise $entrepriseId...\n";

// create invitation
$invitation = \App\Models\EntrepriseInvitation::create([
    'entreprise_id' => $entrepriseId,
    'invited_by_user_id' => $user->id,
    'email' => 'nouveau.comptable@demo.com',
    'role_invite' => 'comptable',
]);

echo "Invitation created with token: " . $invitation->token . "\n";

// simulate new user registering
$newUser = \App\Models\User::firstOrCreate(
    ['email' => 'nouveau.comptable@demo.com'],
    [
        'nom' => 'Comptable',
        'prenom' => 'Nouveau',
        'password' => bcrypt('password'),
    ]
);

echo "New user created/found: " . $newUser->id . "\n";

// simulate acceptance
Auth::login($newUser);
app(\App\Http\Controllers\EntrepriseInvitationAcceptController::class)->accept(request(), $invitation->token);

$aff = \App\Models\Affectation::where('user_id', $newUser->id)->where('entreprise_id', $entrepriseId)->first();
echo "Affectation created: " . ($aff ? "YES (role_id: " . $aff->role_id . ")" : "NO") . "\n";
