<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

// === Fiscaliste — Toutes les permissions fiscalité ===
$fiscaliste = Role::findByName('fiscaliste', 'web');
$fiscalistePerms = [
    'fiscalite.consulter', 'fiscalite.lire', 'fiscalite.declarer', 'fiscalite.tva',
    'fiscalite.e_mecef', 'fiscalite.optimiser', 'fiscalite.parametrer_regle',
    'fiscalite.preparer_declaration', 'fiscalite.valider_declaration',
    'fiscalite.valider_regle', 'fiscalite.televerser_declaration', 'fiscalite.suivi',
    'comptabilite.consulter', 'comptabilite.balance', 'comptabilite.bilan',
    'comptabilite.resultat', 'comptabilite.exporter',
];

foreach ($fiscalistePerms as $permName) {
    $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
    if (!$fiscaliste->hasPermissionTo($perm)) {
        $fiscaliste->givePermissionTo($perm);
    }
}
echo "✓ fiscaliste: " . count($fiscalistePerms) . " permissions assignées\n";

// === Auditeur — Lecture seule ===
$auditeur = Role::findByName('auditeur', 'web');
$auditeurPerms = [
    'fiscalite.consulter', 'fiscalite.lire', 'fiscalite.suivi',
    'comptabilite.consulter', 'comptabilite.balance', 'comptabilite.bilan',
    'comptabilite.resultat', 'comptabilite.exporter',
    'ged.consulter', 'ged.telecharger',
];

foreach ($auditeurPerms as $permName) {
    $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
    if (!$auditeur->hasPermissionTo($perm)) {
        $auditeur->givePermissionTo($perm);
    }
}
echo "✓ auditeur: " . count($auditeurPerms) . " permissions assignées (lecture seule)\n";

// === Comptable — Accès comptabilité complet ===
$comptable = Role::findByName('comptable', 'web');
$comptablePerms = [
    'comptabilite.consulter', 'comptabilite.ecrire', 'comptabilite.valider',
    'comptabilite.balance', 'comptabilite.bilan', 'comptabilite.resultat',
    'comptabilite.journaux', 'comptabilite.cloturer', 'comptabilite.analyse',
    'comptabilite.exporter', 'comptabilite.param',
    'fiscalite.consulter', 'fiscalite.lire', 'fiscalite.declarer',
    'fiscalite.tva', 'fiscalite.e_mecef', 'fiscalite.preparer_declaration',
    'fiscalite.televerser_declaration', 'fiscalite.suivi',
    'client.consulter', 'client.creer', 'client.modifier',
    'crm.consulter', 'crm.documents', 'crm.relancer',
    'ged.consulter', 'ged.importer', 'ged.telecharger',
];

foreach ($comptablePerms as $permName) {
    $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
    if (!$comptable->hasPermissionTo($perm)) {
        $comptable->givePermissionTo($perm);
    }
}
echo "✓ comptable: " . count($comptablePerms) . " permissions assignées\n";

echo "\nTerminé ✓\n";
