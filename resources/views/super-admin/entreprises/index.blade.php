@extends('layouts.gel-super-admin')

@section('title', 'Gestion des Entreprises')

@section('content')
<div class="mb-4">
    <h1 style="font-size: 24px; font-weight: 700; color: #111827;">Espace Super Admin</h1>
    <div style="color: #6B7280; font-size: 14px;">Gestion des Entreprises, Modules et Utilisateurs</div>
</div>

<div class="row">
    <!-- COLONNE GAUCHE: Liste des entreprises -->
    <div class="col-md-8">
        @foreach($entreprises as $ent)
        <div class="sa-card mb-4">
            <div class="sa-card-header d-flex justify-content-between align-items-center">
                <div class="sa-card-title">
                    <i class="fas fa-building" style="color:var(--sa-primary); margin-right:8px;"></i> 
                    {{ $ent->raison_sociale }}
                </div>
                <span class="badge" style="background:#DCFCE7; color:#166534;">{{ $ent->statut_abonnement }}</span>
            </div>
            
            <div class="sa-card-body p-0">
                <div class="p-3 border-bottom" style="background: #f8fafc;">
                    <h6 class="mb-2" style="font-size: 13px; font-weight: 600; color: #475569;">Modules Activés</h6>
                    <form action="{{ route('super-admin.entreprises.modules', $ent->id) }}" method="POST" class="d-flex align-items-center gap-3">
                        @csrf
                        @php
                            $actifs = $ent->modules->where('actif', true)->pluck('module_code')->toArray();
                        @endphp
                        
                        @foreach($tousModules as $code => $label)
                        <label style="font-size: 13px; display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" name="modules[{{ $code }}]" value="1" {{ in_array($code, $actifs) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                        
                        <button type="submit" class="btn btn-sm text-white ms-auto" style="background: var(--sa-primary); border: none;">
                            <i class="fas fa-save"></i> Enregistrer les modules
                        </button>
                    </form>
                </div>
                
                <table class="table mb-0" style="font-size: 14px;">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ent->affectations as $aff)
                        <tr>
                            <td style="font-weight: 600;">{{ $aff->utilisateur->nom ?? 'N/A' }}</td>
                            <td>{{ $aff->utilisateur->email }}</td>
                            <td><span class="badge bg-info text-dark">{{ $aff->role->libelle ?? 'Inconnu' }}</span></td>
                            <td><span class="badge bg-success">{{ $aff->statut }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="sa-card-footer" style="background: #f1f5f9; padding: 12px 16px; border-top: 1px solid var(--sa-border);">
                <h6 class="mb-2" style="font-size: 13px; font-weight: 600; color: #475569;">Ajouter un utilisateur à cette entreprise</h6>
                <form action="{{ route('super-admin.entreprises.affectations', $ent->id) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="nom" class="form-control form-control-sm" placeholder="Nom" required style="width: 150px;">
                    <input type="email" name="email" class="form-control form-control-sm" placeholder="Email" required style="width: 200px;">
                    <input type="text" name="mot_de_passe" class="form-control form-control-sm" placeholder="Mot de passe" required style="width: 150px;">
                    <select name="role_id" class="form-select form-select-sm" required style="width: 180px;">
                        <option value="">Sélectionner un rôle</option>
                        @foreach($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->libelle }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm text-white" style="background: var(--sa-primary); border: none;"><i class="fas fa-plus"></i> Ajouter</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <!-- COLONNE DROITE: Nouvelle Entreprise -->
    <div class="col-md-4">
        <div class="sa-card">
            <div class="sa-card-header">
                <div class="sa-card-title">Créer une Entreprise</div>
            </div>
            <div class="sa-card-body">
                <form action="{{ route('super-admin.entreprises.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 500;">Raison Sociale</label>
                        <input type="text" name="raison_sociale" class="form-control" required>
                    </div>
                    
                    <hr class="my-4" style="border-color: var(--sa-border);">
                    <h6 class="mb-3" style="font-size: 13px; font-weight: 600; color: #475569;">Administrateur Principal</h6>
                    
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 500;">Nom Complet</label>
                        <input type="text" name="nom_admin" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 500;">Email</label>
                        <input type="email" name="email_admin" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 500;">Mot de passe temporaire</label>
                        <input type="text" name="mot_de_passe" class="form-control" value="password123" required>
                    </div>
                    
                    <button type="submit" class="btn text-white w-100 mt-3" style="background: var(--sa-primary); border: none; padding: 10px;">
                        <i class="fas fa-building"></i> Créer Entreprise & Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
