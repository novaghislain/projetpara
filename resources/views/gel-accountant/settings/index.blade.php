@php $currentSection = 'settings'; @endphp
@extends('layouts.gel-accountant')
@section('title', 'Paramètres - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Paramètres</h1>
        <p class="gel-page-subtitle">Configuration de votre cabinet comptable</p>
    </div>
</div>

{{-- Navigation par onglets --}}
<div class="gel-tabs" style="margin-bottom:24px;">
    <a class="gel-tab active" id="stab-profil" onclick="switchSettingTab('profil', this)">Profil</a>
    <a class="gel-tab" id="stab-cabinet" onclick="switchSettingTab('cabinet', this)">Cabinet</a>
    <a href="{{ route('gel-accountant.settings.client-portal') }}" class="gel-tab">Espace Client</a>
    <a class="gel-tab" id="stab-notifs" onclick="switchSettingTab('notifs', this)">Notifications</a>
    <a class="gel-tab" id="stab-securite" onclick="switchSettingTab('securite', this)">Sécurité</a>
</div>

{{-- ONGLET PROFIL --}}
<div id="stab-content-profil">
    <div class="gel-card p-4 mb-4">
        <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Informations personnelles</span></div>
        <div class="gel-card-body p-4 mb-4">
            <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--gel-border);">
                <div class="avatar" style="width:64px;height:64px;font-size:20px;background:var(--gel-primary);color:white;overflow:hidden;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                    @if(auth()->user()?->photo)
                        <img src="{{ asset('storage/' . auth()->user()->photo) }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ strtoupper(substr(auth()->user()?->name ?? auth()->user()?->email ?? 'U', 0, 2)) }}
                    @endif
                </div>
                <div>
                    <div style="font-weight:700;font-size:16px;">{{ auth()->user()?->name ?? 'Utilisateur' }}</div>
                    <div style="color:var(--gel-text-muted);font-size:13px;">{{ auth()->user()?->email }}</div>
                    <div style="margin-top:6px;">
                        <span class="gel-badge gel-badge-info">{{ auth()->user()?->fonction ?? 'Comptable' }}</span>
                    </div>
                </div>
                <form action="{{ route('gel-accountant.settings.avatar.update') }}" method="POST" enctype="multipart/form-data" style="margin-left:auto;">
                    @csrf
                    <input type="file" name="photo" id="avatarFileInput" style="display:none;" onchange="this.form.submit()">
                    <button type="button" class="gel-btn gel-btn-secondary gel-btn-sm" onclick="document.getElementById('avatarFileInput').click()">
                        <i class="fas fa-camera"></i> Changer la photo
                    </button>
                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-3">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('gel-accountant.settings.profile.update') }}" method="POST" style="max-width:480px;">
                @csrf
                <div class="gel-form-group">
                    <label>Nom complet *</label>
                    <input type="text" name="name" class="gel-form-control" value="{{ old('name', auth()->user()?->name) }}" required>
                </div>
                <div class="gel-form-group">
                    <label>Adresse e-mail *</label>
                    <input type="email" name="email" class="gel-form-control" value="{{ old('email', auth()->user()?->email) }}" required>
                </div>
                <div class="gel-form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="phone" class="gel-form-control" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="+229 ...">
                </div>
                <div class="gel-form-group">
                    <label>Titre professionnel / Fonction</label>
                    <input type="text" name="fonction" class="gel-form-control" value="{{ old('fonction', auth()->user()?->fonction) }}" placeholder="Expert-Comptable, Auditeur...">
                </div>
                <div style="margin-top:20px;">
                    <button type="submit" class="gel-btn gel-btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ONGLET CABINET --}}
<div id="stab-content-cabinet" style="display:none;">
    <div class="gel-card p-4 mb-4">
        <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Informations du cabinet</span></div>
        <div class="gel-card-body p-4 mb-4">
            <form action="#" method="POST" style="max-width:480px;">
                @csrf
                <div class="gel-form-group">
                    <label>Nom du cabinet *</label>
                    <input type="text" name="cabinet_name" class="gel-form-control" value="Cabinet GEL">
                </div>
                <div class="gel-form-group">
                    <label>Numéro d'ordre (CPA)</label>
                    <input type="text" name="num_ordre" class="gel-form-control" placeholder="CPA-BJ-XXXX">
                </div>
                <div class="gel-form-group">
                    <label>Adresse</label>
                    <textarea name="adresse" class="gel-form-control" rows="2" placeholder="Adresse complète du cabinet"></textarea>
                </div>
                <div class="gel-form-group">
                    <label>Ville</label>
                    <input type="text" name="ville" class="gel-form-control" placeholder="Cotonou">
                </div>
                <div class="gel-form-group">
                    <label>Téléphone cabinet</label>
                    <input type="tel" name="phone" class="gel-form-control" placeholder="+229 ...">
                </div>
                <div class="gel-form-group">
                    <label>Site web</label>
                    <input type="url" name="website" class="gel-form-control" placeholder="https://cabinet.com">
                </div>
                <div class="gel-form-group">
                    <label>Logo du cabinet</label>
                    <input type="file" name="logo" class="gel-form-control" accept="image/*">
                </div>
                <div style="margin-top:20px;">
                    <button type="button" class="gel-btn gel-btn-primary" onclick="showToast('Informations du cabinet mises À  jour !', 'success')">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ONGLET NOTIFICATIONS --}}
<div id="stab-content-notifs" style="display:none;">
    <div class="gel-card p-4 mb-4">
        <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Préférences de notifications</span></div>
        <div class="gel-card-body p-4 mb-4">
            <div style="display:flex;flex-direction:column;gap:16px;">
                @foreach([
                    ['notif_facturation', 'Nouvelles factures et paiements', 'Recevoir un email lorsqu\'une facture est émise ou un paiement reçu'],
                    ['notif_echeances', 'Rappels d\'échéances fiscales', 'Alerte 7 jours avant chaque échéance (TVA, CNSS, IRPP...)'],
                    ['notif_clients', 'Activité des clients', 'Notification lors des connexions et modifications de vos clients'],
                    ['notif_equipe', 'Activité de l\'équipe', 'Alertes lors de modifications par vos collaborateurs'],
                    ['notif_rapports', 'Rapports disponibles', 'Email lorsqu\'un rapport est généré ou partagé'],
                ] as $notif)
                <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:14px;border:1px solid var(--gel-border);border-radius:8px;">
                    <div>
                        <div style="font-weight:600;margin-bottom:3px;">{{ $notif[1] }}</div>
                        <div style="font-size:12px;color:var(--gel-text-muted);">{{ $notif[2] }}</div>
                    </div>
                    <label style="position:relative;display:inline-block;width:42px;height:22px;cursor:pointer;flex-shrink:0;margin-left:16px;">
                        <input type="checkbox" name="{{ $notif[0] }}" checked style="opacity:0;width:0;height:0;">
                        <span onclick="this.previousElementSibling.checked=!this.previousElementSibling.checked;showToast('Préférence mise À  jour','success')"
                              style="position:absolute;cursor:pointer;inset:0;background:#2CA01C;border-radius:22px;transition:0.3s;">
                            <span style="position:absolute;height:16px;width:16px;left:3px;bottom:3px;background:white;border-radius:50%;transition:0.3s;transform:translateX(20px);"></span>
                        </span>
                    </label>
                </div>
                @endforeach
            </div>
            <div style="margin-top:20px;">
                <button type="button" class="gel-btn gel-btn-primary" onclick="showToast('Préférences de notifications sauvegardées !', 'success')">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ONGLET SÉCURITÉ --}}
<div id="stab-content-securite" style="display:none;">
    <div class="gel-card p-4 mb-4">
        <div class="gel-card-header p-4 mb-4"><span style="font-weight:700;">Sécurité du compte</span></div>
        <div class="gel-card-body p-4 mb-4">
            <form action="{{ route('gel-accountant.settings.password.update') }}" method="POST" style="max-width:420px;">
                @csrf
                <h4 style="margin-bottom:16px;color:var(--gel-text-primary);">Changer le mot de passe</h4>
                <div class="gel-form-group">
                    <label>Mot de passe actuel *</label>
                    <input type="password" name="current_password" class="gel-form-control" required placeholder="Mot de passe actuel">
                </div>
                <div class="gel-form-group">
                    <label>Nouveau mot de passe *</label>
                    <input type="password" name="password" class="gel-form-control" required placeholder="Minimum 8 caractères">
                </div>
                <div class="gel-form-group">
                    <label>Confirmer le nouveau mot de passe *</label>
                    <input type="password" name="password_confirmation" class="gel-form-control" required placeholder="Répéter le nouveau mot de passe">
                </div>
                <div style="margin-top:20px;">
                    <button type="submit" class="gel-btn gel-btn-primary">
                        <i class="fas fa-lock"></i> Mettre à jour le mot de passe
                    </button>
                </div>
            </form>
            <hr style="margin:30px 0;border-color:var(--gel-border);">
            <div>
                <h4 style="margin-bottom:12px;color:var(--gel-text-primary);">Authentification à deux facteurs</h4>
                <p style="font-size:13px;color:var(--gel-text-secondary);margin-bottom:16px;">Renforcez la sécurité de votre compte avec un code SMS ou une application d'authentification.</p>
                <a href="{{ route('2fa.setup') }}" class="gel-btn gel-btn-secondary">
                    <i class="fas fa-shield-alt"></i> Configurer la 2FA
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function switchSettingTab(tab, el) {
    // Désactiver tous les onglets et contenus
    document.querySelectorAll('.gel-tab').forEach(t => t.classList.remove('active'));
    ['profil','cabinet','notifs','securite'].forEach(function(t) {
        var c = document.getElementById('stab-content-'+t);
        if (c) c.style.display = 'none';
    });
    // Activer l'onglet sélectionné
    el.classList.add('active');
    var content = document.getElementById('stab-content-'+tab);
    if (content) content.style.display = '';
}
</script>
@endpush
@endsection

