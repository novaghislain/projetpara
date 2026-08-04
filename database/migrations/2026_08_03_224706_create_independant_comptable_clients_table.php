<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table des clients propres d'un comptable indépendant (Modèle 3B).
     *
     * ISOLATION : chaque enregistrement appartient à un seul comptable (comptable_id).
     * Un comptable indépendant peut avoir N clients. Chaque client est isolé.
     *
     * Le champ `type` distingue :
     * - 'manuel'    : client saisi manuellement, sans compte plateforme
     * - 'invite'    : client qui a accepté une invitation et a un compte Portal Contact
     *
     * Le champ `invitation_token` permet de générer un lien d'invitation unique.
     */
    public function up(): void
    {
        Schema::create('independant_comptable_clients', function (Blueprint $table) {
            $table->id();

            // Lien au comptable propriétaire — ISOLATION OBLIGATOIRE
            $table->foreignId('comptable_id')->constrained('users')->cascadeOnDelete();

            // Informations de base du client (entreprise externe)
            $table->string('nom_entreprise');
            $table->string('contact_nom')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('adresse')->nullable();
            $table->string('ifu', 100)->nullable();
            $table->string('rccm', 100)->nullable();
            $table->string('secteur')->nullable();
            $table->text('notes')->nullable();

            // Type d'ajout et statut
            $table->string('type', 20)->default('manuel'); // manuel, invite
            $table->string('statut', 20)->default('actif'); // actif, inactif, archive

            // Lien optionnel au PortalContact si le client a accepté l'invitation
            $table->foreignId('portal_contact_id')->nullable()->constrained('portal_contacts')->nullOnDelete();

            // Token d'invitation
            $table->string('invitation_token', 128)->nullable()->unique();
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('invitation_accepted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('independant_comptable_clients');
    }
};
