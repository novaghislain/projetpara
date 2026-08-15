<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$users = [
    [
        'name' => 'Secrétaire GEL',
        'email' => 'secretaire@gel.cabinet',
        'password' => Hash::make('password'),
        'role' => 'secretaire',
        'role_secretaire' => true,
        'is_active' => true,
    ],
    [
        'name' => 'Comptable GEL',
        'email' => 'comptable@gel.cabinet',
        'password' => Hash::make('password'),
        'role' => 'comptable',
        'is_active' => true,
    ],
    [
        'name' => 'Administrateur Entreprise',
        'email' => 'admin@entreprise.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'is_company_admin' => true,
        'is_active' => true,
    ]
];

foreach ($users as $userData) {
    User::updateOrCreate(
        ['email' => $userData['email']],
        $userData
    );
    echo "Créé : {$userData['email']} (Mot de passe: password)\n";
}
