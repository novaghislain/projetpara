<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Référentiel fiscal et social (République du Bénin — Code Général des Impôts).
     *
     * Stocke sous forme de couples clé → valeur les taux et jours d'échéance,
     * afin qu'ils soient MODIFIABLES (ils évoluent chaque année par loi de finances)
     * et jamais codés en dur dans le code.
     *
     * `cabinet_id` NULL ⇒ valeur globale ; une valeur par cabinet peut la surcharger.
     */
    public function up(): void
    {
        Schema::create('gel_fiscal_parameters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cabinet_id')->nullable()->index();
            $table->string('cle')->index();
            $table->string('label')->nullable();
            $table->text('valeur')->nullable();
            $table->string('unite')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['cabinet_id', 'cle']);
        });

        // Valeurs par défaut (Code Général des Impôts 2026 & Code de la Sécurité Sociale)
        $defaults = [
            ['taux_tva', 'Taux de TVA', '18', '%', 'TVA — impôt sur la valeur ajoutée (Code Général des Impôts).'],
            ['jour_echeance_tva', 'Jour de dépôt TVA', '15', 'jour', 'La déclaration TVA du mois M est à déposer le 15 du mois suivant.'],
            ['taux_tps', 'TPS (Taxe Professionnelle Synthétique)', '5', '%', 'Taux de la Taxe Professionnelle Synthétique (taux variable selon loi de finances).'],
            ['taux_its', 'ITS (Impôt sur les Traitements et Salaires)', '0', 'barème', 'Barème progressif mensuel — réglable selon le barème en vigueur en loi de finances.'],
            ['jour_echeance_its', 'Jour de déclaration ITS', '15', 'jour', 'Paiement mensuel de l’ITS au plus tard le 15 du mois suivant.'],
            ['taux_vps', 'VPS (Versement Patronal sur Salaires)', '4', '%', 'Contribution patronale à l’assurance sociale (à ajuster).'],
            ['cnss_employeur', 'CNSS — part employeur', '0', '%', 'Cotisation sociale employeur (à ajuster selon le code de la sécu).'],
            ['cnss_salarie', 'CNSS — part salarié', '0', '%', 'Cotisation sociale salarié (à ajuster selon le code de la sécu).'],
            ['jour_echeance_cnss', 'Jour de dépôt CNSS', '15', 'jour', 'Déclaration et paiement mensuel de la CNSS.'],
            ['taux_patente', 'Patente', '0', 'variable', 'Droit de patente — variable selon le secteur et le chiffre d’affaires.'],
            ['jour_echeance_patente', 'Jour de paiement de la Patente', '31', 'jour', 'Échéance annuelle de la patente (décembre).'],
            ['exercice_fiscal', 'Exercice fiscal', '2026', 'année', 'Millésime de l’exercice en cours (loi de finances).'],
        ];

        foreach ($defaults as [$cle, $label, $valeur, $unite, $description]) {
            DB::table('gel_fiscal_parameters')->insert([
                'cabinet_id' => null,
                'cle' => $cle,
                'label' => $label,
                'valeur' => $valeur,
                'unite' => $unite,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gel_fiscal_parameters');
    }
};