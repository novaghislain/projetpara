<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

/**
 * Paramètre du référentiel fiscal et social béninois.
 *
 * Couple clé → valeur, modifiable (loi de finances annuelle).
 * `cabinet_id` NULL ⇒ global ; peut être surchargé par cabinet.
 *
 * Table : gel_fiscal_parameters
 */
class FiscalParameter extends Model
{
    protected $table = 'gel_fiscal_parameters';

    protected $fillable = [
        'cabinet_id', 'cle', 'label', 'valeur', 'unite', 'description',
    ];

    public const TVA_RATE = 'taux_tva';
    public const TVA_DUE_DAY = 'jour_echeance_tva';
    public const ITS_RATE = 'taux_its';
    public const ITS_DUE_DAY = 'jour_echeance_its';
    public const TPS_RATE = 'taux_tps';
    public const VPS_RATE = 'taux_vps';
    public const PATENTE_RATE = 'taux_patente';
    public const PATENTE_DUE_DAY = 'jour_echeance_patente';
    public const CNSS_EMPLOYEUR = 'cnss_employeur';
    public const CNSS_SALARIE = 'cnss_salarie';
    public const CNSS_DUE_DAY = 'jour_echeance_cnss';
    public const EXERCICE_FISCAL = 'exercice_fiscal';

    /**
     * Lit la valeur d'un paramètre pour un cabinet (global en repli).
     */
    public static function lire(string $cle, ?int $cabinetId = null, $defaut = null): ?string
    {
        return (string) (self::tableau($cabinetId)[$cle] ?? $defaut);
    }

    /**
     * Retourne toutes les valeurs sous forme de tableau clé → valeur.
     * (le paramètre de cabinet, sinon le paramètre global)
     */
    public static function tableau(?int $cabinetId = null): array
    {
        $result = [];
        foreach (self::all() as $p) {
            // Priorité : cabinet → global (on n'écrase une valeur « cabinet » par « global »)
            if ($p->cabinet_id && $p->cabinet_id === $cabinetId) {
                $result[$p->cle] = $p->valeur;
            } elseif (!$p->cabinet_id && !array_key_exists($p->cle, $result)) {
                $result[$p->cle] = $p->valeur;
            }
        }
        return $result;
    }
}