<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add questionnaire fields to consultations
        Schema::table('consultations', function (Blueprint $table) {
            $table->string('analysis_type')->nullable()->after('area')->comment('visage, corps, ou les_deux');
            $table->json('answers')->nullable()->after('results')->comment('Raw questionnaire answers');
            $table->json('skin_score')->nullable()->after('answers')->comment('Hydratation, Eclat, Sensibilite, Imperfections /10');
            $table->json('diagnostic')->nullable()->after('skin_score')->comment('Type peau, Etat, Probleme principal');
            $table->json('recommended_product_ids')->nullable()->after('diagnostic')->comment('IDs produits recommandés');
        });

        // Add skin tags to products for smart recommendations
        Schema::table('products', function (Blueprint $table) {
            $table->string('skin_type_tag')->nullable()->after('features')->comment('grasse, seche, mixte, normale, tous');
            $table->string('problem_tag')->nullable()->after('skin_type_tag')->comment('acne, taches, rides, terne, secheresse, tous');
            $table->string('routine_step')->nullable()->after('problem_tag')->comment('nettoyant, serum, creme, masque, gommage, soleil, corps');
            $table->boolean('is_victoire')->default(false)->after('routine_step')->comment('Marque Victoire para');
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['analysis_type', 'answers', 'skin_score', 'diagnostic', 'recommended_product_ids']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['skin_type_tag', 'problem_tag', 'routine_step', 'is_victoire']);
        });
    }
};
