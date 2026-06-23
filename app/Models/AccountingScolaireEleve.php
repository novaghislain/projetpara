<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingScolaireEleve extends Model
{
    use SoftDeletes;
    protected $table = 'accounting_scolaire_eleves';
    protected $fillable = ['client_id','matricule','nom','prenom','date_naissance','sexe','classe','annee_scolaire','niveau','statut','nom_tuteur','contact_tuteur','email_tuteur','adresse','notes','created_by'];
    protected $casts = ['date_naissance'=>'date'];
    public function client() { return $this->belongsTo(Client::class); }
    public function scopeForClient($q, $cid) { return $q->where('client_id', $cid); }
}
