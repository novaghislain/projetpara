@extends('layouts.gel-secretary')
@section('title', 'Profil Professionnel')

@section('content')
<div style="max-width:800px; margin:0 auto; padding:20px 0;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1 style="font-size:24px; font-weight:800; color:#1e293b; margin:0;">Mon Profil Marketplace</h1>
            <p style="color:#64748b; margin:4px 0 0 0;">Présentez vos compétences aux entreprises qui recherchent un(e) assistant(e) ou un comptable.</p>
        </div>
        <span style="padding:6px 16px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; 
            background:{{ $profil->statut_validation == 'valide' ? '#dcfce7' : ($profil->statut_validation == 'en_attente' ? '#fef3c7' : '#f1f5f9') }}; 
            color:{{ $profil->statut_validation == 'valide' ? '#16a34a' : ($profil->statut_validation == 'en_attente' ? '#d97706' : '#64748b') }};">
            {{ str_replace('_', ' ', $profil->statut_validation) }}
        </span>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div style="background:white; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden;">
        <form action="{{ route('gel-secretary.cabinet.profil.store') }}" method="POST" style="padding:24px;">
            @csrf
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:8px;">Domaines d'expertise</label>
                    <input type="text" name="domaine_expertise" value="{{ old('domaine_expertise', $profil->domaine_expertise) }}" placeholder="Ex: Comptabilité, Gestion de paie, Secrétariat juridique" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:14px;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:8px;">Disponibilités</label>
                    <select name="disponibilites" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:14px;">
                        <option value="">Sélectionnez...</option>
                        <option value="Temps plein" {{ $profil->disponibilites == 'Temps plein' ? 'selected' : '' }}>Temps plein</option>
                        <option value="Temps partiel" {{ $profil->disponibilites == 'Temps partiel' ? 'selected' : '' }}>Temps partiel</option>
                        <option value="Soirs et weekends" {{ $profil->disponibilites == 'Soirs et weekends' ? 'selected' : '' }}>Soirs et weekends</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:8px;">Tarif Horaire Indiqué (en Francs CFA)</label>
                <input type="number" name="tarif_horaire" value="{{ old('tarif_horaire', $profil->tarif_horaire) }}" placeholder="Ex: 5000" style="width:100%; max-width:300px; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:14px;">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:8px;">Bio / Description</label>
                <textarea name="bio" rows="5" placeholder="Décrivez votre expérience, vos atouts, et ce que vous pouvez apporter aux clients..." style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit; font-size:14px; resize:vertical;">{{ old('bio', $profil->bio) }}</textarea>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:20px;">
                <button type="submit" style="background:#f1f5f9; color:#475569; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-save"></i> Enregistrer le brouillon
                </button>
                
                @if($profil->statut_validation == 'brouillon' || $profil->statut_validation == 'rejete')
                <button type="submit" name="submit_validation" value="1" style="background:#0d9488; color:white; border:none; padding:10px 24px; border-radius:8px; font-weight:600; cursor:pointer;">
                    Soumettre pour validation <i class="fas fa-paper-plane"></i>
                </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
