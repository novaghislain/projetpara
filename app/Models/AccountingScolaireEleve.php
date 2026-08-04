<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle représentant un élève dans le module scolaire.
 *
 * Gère les informations des élèves inscrits dans un établissement
 * scolaire. Chaque élève est lié à un client (établissement) avec
 * son matricule, sa classe, son niveau et les coordonnées de son tuteur.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (établissement scolaire)
 * @property string $matricule Matricule de l'élève
 * @property string $nom Nom de l'élève
 * @property string $prenom Prénom de l'élève
 * @property string|null $date_naissance Date de naissance
 * @property string $sexe Sexe
 * @property string $classe Classe
 * @property string $annee_scolaire Année scolaire
 * @property string $niveau Niveau d'études
 * @property string $statut Statut (inscrit, exclu, diplome)
 * @property string $nom_tuteur Nom du tuteur ou parent
 * @property string $contact_tuteur Contact du tuteur
 * @property string|null $email_tuteur Email du tuteur
 * @property string|null $adresse Adresse du tuteur
 * @property string|null $notes Notes
 * @property int|null $created_by Identifiant de l'utilisateur créateur
 *
 * @property-read Client|null $client Client (établissement) associé
 *
 * @table accounting_scolaire_eleves
 */
class AccountingScolaireEleve extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_scolaire_eleves';
    protected $fillable = ['client_id','matricule','nom','prenom','date_naissance','sexe','classe','annee_scolaire','niveau','statut','nom_tuteur','contact_tuteur','email_tuteur','adresse','notes','created_by'];
    protected $casts = ['date_naissance'=>'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
