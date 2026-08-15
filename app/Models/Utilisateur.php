<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Utilisateur extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'cabinet_id',
        'email',
        'mot_de_passe_hash',
        'nom',
        'telephone',
        'mfa_active',
        'mfa_secret',
        'statut',
        'cree_le',
        'supprime_le',
        'password',
        'remember_token',
        'role',
        'active_client_id',
        'is_autonomous'
    ];

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'payeur_utilisateur_id', 'id');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'utilisateur_id', 'id');
    }

    public function reglesFiscales()
    {
        return $this->hasMany(ReglesFiscale::class, 'cree_par', 'id');
    }

    /**
     * Vérifie si l'utilisateur possède un rôle spécifique pour l'entreprise active.
     * @param string $roleCode
     * @return bool
     */
    public function hasRoleForActiveEntreprise($roleCode)
    {
        $activeEntrepriseId = session('active_entreprise_id');
        if (!$activeEntrepriseId) {
            return false;
        }

        // Si l'utilisateur a déjà été vérifié par le middleware, la requête contient les affectations
        $affectations = request()->get('user_affectations');
        if ($affectations) {
            return $affectations->contains(function ($affectation) use ($roleCode) {
                return $affectation->role && $affectation->role->code === $roleCode;
            });
        }

        // Sinon, requête BDD
        return $this->affectations()
            ->where('entreprise_id', $activeEntrepriseId)
            ->where('statut', 'active')
            ->whereHas('role', function ($query) use ($roleCode) {
                $query->where('code', $roleCode);
            })->exists();
    }
}
