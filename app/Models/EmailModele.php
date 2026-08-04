<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un modèle d'email pré-défini.
 *
 * Table associée : `email_modeles`
 *
 * Permet de créer et gérer des modèles d'emails réutilisables
 * avec sujet et corps HTML pour les envois automatisés.
 */
class EmailModele extends Model
{
    protected $table = 'email_modeles';

    protected $fillable = [
        'nom',
        'sujet',
        'corps_html',
        'type',
        'actif',
    ];
}
