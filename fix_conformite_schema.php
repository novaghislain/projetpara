<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Mise à jour de gel_conformite...\n";

Schema::table('gel_conformite', function (Blueprint $table) {
    $cols = DB::select("SHOW COLUMNS FROM gel_conformite");
    $existing = array_map(fn($c) => $c->Field, $cols);
    
    if (!in_array('deleted_at', $existing)) $table->softDeletes();
    if (!in_array('code', $existing)) $table->string('code')->nullable();
    if (!in_array('categorie', $existing)) $table->string('categorie')->nullable();
    if (!in_array('titre', $existing)) $table->string('titre')->nullable();
});

echo "Mise à jour terminée !\n";
