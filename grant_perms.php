<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@demo.com')->first();
$aff = $user->affectations()->first();

$actions = ['consulter', 'preparer_declaration', 'valider_declaration', 'televerser_declaration'];
foreach($actions as $action) {
    $existing = \DB::table('permissions')
        ->where('affectation_id', $aff->id)
        ->where('ressource', 'fiscalite')
        ->where('action', $action)
        ->first();
        
    if ($existing) {
        \DB::table('permissions')->where('id', $existing->id)->update(['autorise' => true]);
    } else {
        \DB::table('permissions')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'affectation_id' => $aff->id,
            'ressource' => 'fiscalite',
            'action' => $action,
            'autorise' => true,
        ]);
    }
    echo "Added permission for $action on fiscalite.\n";
}
