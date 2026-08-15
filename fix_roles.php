<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

echo "--- MIGRATION DES ROLES (Diagramme 26.1) ---\n";

$map = [
    'gestionnaire_cabinet' => 'director',
    'admin'                => 'director',
    'chef_comptable'       => 'pole_responsible',
    'comptable_senior'     => 'comptable',
    'comptable_junior'     => 'collaborator',
    'agent_paie'           => 'rh',
    'agent_client'         => 'secretaire',
    'entreprise_admin'     => 'company_admin',
    'entreprise_comptable' => 'company_manager',
    'entreprise_rh'        => 'rh',
    'cpa_particulier'      => 'client',
    // Les autres (super_admin, auditeur, fiscaliste) gardent le même nom
];

// 1. Mettre à jour la colonne 'role' dans la table 'utilisateurs'
$users = DB::table('utilisateurs')->get();
$updatedCount = 0;
foreach ($users as $user) {
    $oldRole = $user->role;
    $newRole = $map[$oldRole] ?? $oldRole; // si pas dans le map, on garde
    
    // Fallback si vide
    if (empty($newRole)) {
        $newRole = 'collaborator';
    }

    if ($oldRole !== $newRole) {
        DB::table('utilisateurs')->where('id', $user->id)->update(['role' => $newRole]);
        $updatedCount++;
    }
}
echo "Mis à jour {$updatedCount} utilisateur(s) avec les nouveaux noms de rôles.\n";

// 2. Nettoyer les anciens rôles Spatie qui ne sont pas dans le nouveau diagramme
$validRoles = [
    'super_admin', 'director', 'pole_responsible', 'fiscaliste', 'comptable', 'collaborator', 'secretaire',
    'company_admin', 'company_manager', 'company_employee', 'caissier', 'juriste', 'rh', 'gestionnaire_projet',
    'auditeur', 'client'
];
$deleted = DB::table('gel_roles')->whereNotIn('name', $validRoles)->delete();
echo "Supprimé {$deleted} anciens rôles Spatie obsolètes.\n";

// Show current users
echo "\n--- Utilisateurs Actuels ---\n";
$users = DB::table('utilisateurs')->get(['id', 'nom', 'email', 'role', 'cabinet_id']);
foreach ($users as $u) {
    echo "  [{$u->role}] {$u->nom} ({$u->email})\n";
}
echo "Terminé.\n";
