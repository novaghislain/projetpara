<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une ligne d'analyse comptable.
 *
 * Permet la ventilation analytique des écritures comptables par
 * centre de coût, projet ou tout autre axe d'analyse. Ce modèle
 * sert à répartir les montants entre différentes entités
 * analytiques pour un suivi budgétaire détaillé.
 *
 * @property int $id
 *
 * @table analytic_lines
 */
class AnalyticLine extends Model
{
    //
}
