<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateTransmissionsInternesTable extends Migration
{
    public function up()
    {
        Schema::create('transmissions_internes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_recu_id')->constrained('emails_recus')->onDelete('cascade');
            $table->foreignId('transmis_par_id')->constrained('internal_accounts')->onDelete('cascade');
            $table->foreignId('transmis_a_id')->constrained('internal_accounts')->onDelete('cascade');
            $table->string('note')->nullable();
            $table->enum('priorite', ['normale', 'urgente', 'très_urgente'])->default('normale');
            $table->enum('statut', ['en_attente', 'en_traitement', 'traité']);
            $table->timestamp('date_transmission')->nullable();
            $table->timestamp('date_traitement')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transmissions_internes');
    }
}
