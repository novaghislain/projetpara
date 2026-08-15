<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== RÔLES SPATIE ===\n";
$roles = Role::all(['name', 'guard_name']);
foreach ($roles as $r) {
    echo "  - {$r->name} ({$r->guard_name})\n";
}

echo "\n=== PERMISSIONS SPATIE ===\n";
$perms = Permission::all(['name', 'guard_name']);
foreach ($perms as $p) {
    echo "  - {$p->name}\n";
}

echo "\n=== RÔLES TABLE CUSTOM (roles) ===\n";
$rôles = DB::table('roles')->get(['code', 'libelle']);
foreach ($rôles as $r) {
    echo "  - {$r->code} : {$r->libelle}\n";
}
