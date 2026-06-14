<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailModelesTable extends Migration
{
    public function up()
    {
        Schema::create('email_modeles', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('sujet');
            $table->text('corps_html');
            $table->enum('type', ['accuse_reception', 'demande_docs', 'confirmation_rdv', 'autre']);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_modeles');
    }
}
