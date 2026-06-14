<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailsRecusTable extends Migration
{
    public function up()
    {
        Schema::create('emails_recus', function (Blueprint $table) {
            $table->id();
            $table->string('message_id_imap')->unique();
            $table->string('expediteur');
            $table->string('expediteur_email');
            $table->string('objet');
            $table->text('corps_html')->nullable();
            $table->text('corps_texte')->nullable();
            $table->timestamp('date_reception');
            $table->enum('statut', ['nouveau', 'lu', 'transmis', 'en_traitement', 'cloture']);
            $table->json('pieces_jointes_json')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('emails_recus');
    }
}
