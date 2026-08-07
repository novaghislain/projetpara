<?php
$u = \App\Models\User::firstOrCreate(
    ['email' => 'it@gel.cabinet'],
    [
        'name' => 'Informaticien Test',
        'password' => Hash::make('password'),
        'account_type' => 'informaticien',
        'is_active' => true
    ]
);
$u->assignRole('informaticien');
$modules = ['it.tickets', 'it.security', 'it.health', 'it.backups', 'it.dev_requests'];
foreach ($modules as $m) {
    foreach (['view', 'create', 'update', 'delete', 'export', 'validate'] as $a) {
        $u->givePermissionTo("$m.$a");
    }
}
echo "Done\n";
