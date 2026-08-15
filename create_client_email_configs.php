<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasTable('client_email_configs')) {
    Schema::create('client_email_configs', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('client_id')->index();
        $table->string('provider')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->timestamps();
    });
    echo "Created client_email_configs\n";
}
