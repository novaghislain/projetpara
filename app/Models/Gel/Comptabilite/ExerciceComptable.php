<?php

namespace App\Models\Gel\Comptabilite;

use App\Models\Cabinet;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Modèle ExerciceComptable (Exercice comptable).
 *
 * Définit une période comptable (généralement annuelle) avec une date de début
 * et une date de fin. Un exercice peut être ouvert ou clôturé.
 * Permet de clôturer l'exercice (validation des écritures, équilibrage)
 * et de générer le bilan d'ouverture pour l'exercice suivant.
 * Table associée : `gel_exercices`.
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int|null $client_id ID du client
 * @property string $libelle Libellé de l'exercice (ex: Exercice 2026)
 * @property \Carbon\Carbon $date_debut Date de début
 * @property \Carbon\Carbon $date_fin Date de fin
 * @property bool $cloture Si l'exercice est clôturé
 * @property \Carbon\Carbon|null $date_cloture Date de clôture
 *
 * @property-read \App\Models\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Client|null $client Client associé
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Gel\Comptabilite\EcritureComptable[] $ecritures Écritures de l'exercice
 */
class ExerciceComptable extends Model
{
    protected $table = 'gel_exercices';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'libelle',
        'date_debut',
        'date_fin',
        'cloture',
        'date_cloture',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'date_cloture' => 'date',
            'cloture' => 'boolean',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function ecritures(): HasMany
    {
        return $this->hasMany(EcritureComptable::class, 'exercice_id');
    }

    // ─── Méthodes métier ─────────────────────────────────────────

    public function estOuvert(): bool
    {
        return !$this->cloture;
    }

    public function estClos(): bool
    {
        return $this->cloture;
    }

    public function peutEcrire(): bool
    {
        if ($this->cloture) {
            return false;
        }

        $debut = Carbon::parse($this->date_debut);
        $fin = Carbon::parse($this->date_fin);
        $now = now();

        return $now->between($debut, $fin);
    }

    /**
     * Génère les écritures d'ouverture pour l'exercice suivant.
     */
    public function bilanOuverture(?int $nouvelExerciceId = null): bool
    {
        if (!$this->cloture) {
            throw new \RuntimeException("L'exercice courant doit être clôturé avant de générer le bilan d'ouverture.");
        }

        DB::beginTransaction();
        try {
            $exerciceSuivant = $nouvelExerciceId
                ? self::findOrFail($nouvelExerciceId)
                : self::where('client_id', $this->client_id)
                    ->where('date_debut', $this->date_fin->addDay())
                    ->first();

            if (!$exerciceSuivant) {
                throw new \RuntimeException("Aucun exercice suivant trouvé pour générer le bilan d'ouverture.");
            }

            // Récupérer les comptes de bilan (classes 1, 2, 3, 4, 5) avec leur solde
            $soldes = DB::table('gel_lignes_ecriture as le')
                ->join('gel_ecritures as e', 'le.ecriture_id', '=', 'e.id')
                ->join('gel_comptes_comptables as cc', 'le.compte_id', '=', 'cc.id')
                ->where('e.exercice_id', $this->id)
                ->whereIn('cc.classe', ['1', '2', '3', '4', '5'])
                ->selectRaw('le.compte_id, cc.code, cc.intitule, cc.solde_debiteur,
                    SUM(CASE WHEN le.sens = "debit" THEN le.montant ELSE 0 END) as total_debit,
                    SUM(CASE WHEN le.sens = "credit" THEN le.montant ELSE 0 END) as total_credit')
                ->groupBy('le.compte_id', 'cc.code', 'cc.intitule', 'cc.solde_debiteur')
                ->get();

            // Journal OD (opérations diverses) pour l'ouverture
            $journalOD = Journal::where('cabinet_id', $this->cabinet_id)
                ->where('code', 'OD')
                ->first();

            if (!$journalOD) {
                throw new \RuntimeException("Journal OD (Opérations Diverses) introuvable.");
            }

            $libelleOuverture = "Bilan d'ouverture {$exerciceSuivant->libelle}";
            $dateOuverture = Carbon::parse($exerciceSuivant->date_debut);

            $totalDebit = 0;
            $totalCredit = 0;
            $lignes = [];

            foreach ($soldes as $solde) {
                $soldeNet = $solde->total_debit - $solde->total_credit;

                if (abs($soldeNet) < 0.01) {
                    continue;
                }

                if ($soldeNet > 0) {
                    $lignes[] = [
                        'compte_id' => $solde->compte_id,
                        'sens' => 'debit',
                        'montant' => abs($soldeNet),
                        'libelle_ligne' => "Report N-1 : {$solde->code} {$solde->intitule}",
                    ];
                    $totalDebit += abs($soldeNet);
                } elseif ($soldeNet < 0) {
                    $lignes[] = [
                        'compte_id' => $solde->compte_id,
                        'sens' => 'credit',
                        'montant' => abs($soldeNet),
                        'libelle_ligne' => "Report N-1 : {$solde->code} {$solde->intitule}",
                    ];
                    $totalCredit += abs($soldeNet);
                }
            }

            if (empty($lignes)) {
                DB::rollBack();
                Log::info('Aucune ligne à reporter pour le bilan d\'ouverture.', [
                    'exercice_id' => $this->id,
                    'client_id' => $this->client_id,
                ]);
                return false;
            }

            $ecritureOuverture = EcritureComptable::create([
                'cabinet_id' => $this->cabinet_id,
                'exercice_id' => $exerciceSuivant->id,
                'journal_id' => $journalOD->id,
                'client_id' => $this->client_id,
                'numero' => $journalOD->numero_suivant,
                'date_ecriture' => $dateOuverture,
                'date_piece' => $dateOuverture,
                'libelle' => $libelleOuverture,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'valide' => true,
                'valide_par' => null,
                'date_validation' => now(),
                'created_by' => 1,
            ]);

            foreach ($lignes as $ligne) {
                $ecritureOuverture->lignes()->create($ligne);
            }

            DB::commit();
            Log::info("Bilan d'ouverture généré avec succès.", [
                'exercice_id' => $exerciceSuivant->id,
                'ecriture_id' => $ecritureOuverture->id,
                'total_lignes' => count($lignes),
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur génération bilan d'ouverture : {$e->getMessage()}", [
                'exercice_id' => $this->id,
            ]);
            throw $e;
        }
    }

    /**
     * Exécute la clôture de l'exercice.
     */
    public function cloturer(): bool
    {
        if ($this->cloture) {
            throw new \RuntimeException("L'exercice est déjà clôturé.");
        }

        // Vérifier que toutes les écritures sont validées
        $nonValidees = $this->ecritures()->where('valide', false)->count();
        if ($nonValidees > 0) {
            throw new \RuntimeException(
                "Impossible de clôturer : {$nonValidees} écriture(s) non validée(s)."
            );
        }

        // Vérifier que chaque écriture est équilibrée
        $desequilibrees = $this->ecritures()
            ->where('total_debit', '!=', 'total_credit')
            ->count();
        if ($desequilibrees > 0) {
            throw new \RuntimeException(
                "Impossible de clôturer : {$desequilibrees} écriture(s) déséquilibrée(s)."
            );
        }

        $this->update([
            'cloture' => true,
            'date_cloture' => now(),
        ]);

        Log::info("Exercice clôturé avec succès.", [
            'exercice_id' => $this->id,
            'libelle' => $this->libelle,
        ]);

        return true;
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeOuvert($query)
    {
        return $query->where('cloture', false);
    }

    public function scopeClos($query)
    {
        return $query->where('cloture', true);
    }

    public function scopeByCabinet($query, int $cabinetId)
    {
        return $query->where('cabinet_id', $cabinetId);
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeEncours($query)
    {
        $now = now()->format('Y-m-d');
        return $query->where('date_debut', '<=', $now)
            ->where('date_fin', '>=', $now)
            ->where('cloture', false);
    }
}
