<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S11 : messagerie structurée à 3 canaux.
     * Ajoute receiver_id (utilisateur destinataire) pour les canaux internes
     * Secrétaire ↔ Comptable et Comptable ↔ Administrateur.
     */
    public function up(): void
    {
        Schema::table('gel_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('gel_messages', 'receiver_id')) {
                $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id');
                $table->foreign('receiver_id')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('gel_messages', 'channel')) {
                $table->string('channel')->default('entreprise')->after('receiver_id'); // entreprise | interne_comptable | interne_admin
            }
        });
    }

    public function down(): void
    {
        Schema::table('gel_messages', function (Blueprint $table) {
            if (Schema::hasColumn('gel_messages', 'channel')) {
                $table->dropColumn('channel');
            }
            if (Schema::hasColumn('gel_messages', 'receiver_id')) {
                $table->dropForeign(['receiver_id']);
                $table->dropColumn('receiver_id');
            }
        });
    }
};
