<?php
foreach(['Fiscaliste', 'Auditeur', 'Super Admin'] as $role) {
    App\Models\Role::firstOrCreate(['name' => $role]);
}
echo "Roles added successfully.\n";
