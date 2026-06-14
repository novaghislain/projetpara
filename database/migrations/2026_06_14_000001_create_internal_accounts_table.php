<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateInternalAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('internal_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['secretariat', 'comptabilite', 'other']);
            $table->string('name');
            $table->string('email_connexion')->unique();
            $table->string('email_imap')->nullable();
            $table->json('modules_json')->nullable();
            $table->boolean('actif')->default(true);
            $table->boolean('created_by_super_admin')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('internal_accounts');
    }
}
