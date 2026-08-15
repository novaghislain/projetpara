<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasTable('contact_entreprise')) {
    Schema::create('contact_entreprise', function (Blueprint $table) {
        $table->id();
        $table->uuid('client_id')->index();
        $table->uuid('portal_contact_id')->index();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    echo "Created contact_entreprise\n";
} else {
    echo "contact_entreprise exists\n";
}
