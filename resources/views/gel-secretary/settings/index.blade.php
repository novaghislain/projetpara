@extends('layouts.gel-secretary')
@section('title', 'Paramètres')

@push('styles')
<style>
    .settings-tabs {
        display: flex; gap: 20px; border-bottom: 1px solid var(--sec-border); margin-bottom: 24px;
    }
    .settings-tab {
        padding: 10px 16px; font-size: 14px; font-weight: 600; color: var(--sec-text-muted);
        cursor: pointer; position: relative;
    }
    .settings-tab.active {
        color: var(--sec-primary);
    }
    .settings-tab.active::after {
        content: ''; position: absolute; bottom: -1px; left: 0; right: 0;
        height: 2px; background: var(--sec-primary);
    }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    .sec-form-group { margin-bottom: 16px; }
    .sec-form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--sec-text); }
    .sec-form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--sec-border); border-radius: 6px; font-size: 13px; }
    .sec-form-control:focus { border-color: var(--sec-primary); outline: none; box-shadow: 0 0 0 3px var(--sec-primary-light); }
    
    .qr-container { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid var(--sec-border); display: inline-block; margin-bottom: 20px; }
    .two-fa-card { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 20px; margin-top: 20px; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Paramètres</h1>
</div>

<div class="sec-card mb-4">
    <div class="sec-card-body pb-0">
        <div class="settings-tabs">
            <div class="settings-tab active" onclick="switchTab('security')">
                <i class="fas fa-shield-alt"></i> Sécurité & Profil
            </div>
            <div class="settings-tab" onclick="switchTab('webmail')">
                <i class="fas fa-envelope-open-text"></i> Configuration Webmail
            </div>
        </div>
    </div>

    <div class="sec-card-body pt-0">
        
        {{-- ONGLETS SÉCURITÉ --}}
        <div id="tab-security" class="tab-content active">
            <div class="row">
                <div class="col-md-6">
                    <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">Modifier le mot de passe</h3>
                    <form action="{{ route('gel-secretary.settings.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="sec-form-group">
                            <label class="sec-form-label">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="sec-form-control" required>
                        </div>
                        <div class="sec-form-group">
                            <label class="sec-form-label">Nouveau mot de passe</label>
                            <input type="password" name="password" class="sec-form-control" required minlength="8">
                        </div>
                        <div class="sec-form-group">
                            <label class="sec-form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" name="password_confirmation" class="sec-form-control" required minlength="8">
                        </div>
                        <button type="submit" class="sec-btn" style="background: var(--sec-primary); color: white;">
                            Mettre à jour le mot de passe
                        </button>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">Authentification à deux facteurs (2FA)</h3>
                    
                    @if($twoFactorEnabled)
                        <div class="two-fa-card" style="border-color: #10B981; background: #ECFDF5;">
                            <div class="d-flex gap-3 align-items-center">
                                <i class="fas fa-check-circle" style="color: #10B981; font-size: 32px;"></i>
                                <div>
                                    <strong style="color: #065F46; font-size: 15px;">2FA Activée</strong>
                                    <p style="margin: 5px 0 0; font-size: 13px; color: #047857;">Votre compte est sécurisé par la double authentification.</p>
                                </div>
                            </div>
                            
                            <form action="{{ route('gel-secretary.settings.2fa.disable') }}" method="POST" class="mt-4" onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver la 2FA ?');">
                                @csrf
                                <div class="sec-form-group">
                                    <label class="sec-form-label" style="color: #065F46;">Entrez votre mot de passe pour confirmer :</label>
                                    <input type="password" name="password" class="sec-form-control" required style="border-color: #A7F3D0;">
                                </div>
                                <button type="submit" class="sec-btn" style="background: var(--sec-danger); color: white;">
                                    Désactiver la 2FA
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="two-fa-card">
                            <p style="font-size: 13px; margin-bottom: 16px;">
                                Augmentez la sécurité de votre compte en activant l'authentification à deux facteurs via Google Authenticator ou Authy.
                            </p>
                            
                            <ol style="font-size: 13px; padding-left: 20px; margin-bottom: 20px; line-height: 1.6;">
                                <li>Téléchargez l'application <strong>Google Authenticator</strong> sur votre téléphone.</li>
                                <li>Scannez le QR Code ci-dessous.</li>
                                <li>Entrez le code à 6 chiffres généré par l'application pour valider l'activation.</li>
                            </ol>
                            
                            <div class="text-center">
                                <div class="qr-container">
                                    {!! $qrCodeSvg !!}
                                </div>
                                <p style="font-size: 12px; color: var(--sec-text-muted); word-break: break-all;">
                                    Clé secrète : <strong>{{ $secretKey }}</strong>
                                </p>
                            </div>
                            
                            <form action="{{ route('gel-secretary.settings.2fa.confirm') }}" method="POST">
                                @csrf
                                <div class="sec-form-group mt-3">
                                    <label class="sec-form-label text-center">Code de vérification (6 chiffres)</label>
                                    <input type="text" name="otp" class="sec-form-control text-center" style="font-size: 18px; letter-spacing: 5px; font-weight: 600;" placeholder="000000" required maxlength="6">
                                </div>
                                <button type="submit" class="sec-btn w-100 justify-content-center" style="background: var(--sec-primary); color: white;">
                                    <i class="fas fa-lock"></i> Activer la double authentification
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- ONGLETS WEBMAIL --}}
        <div id="tab-webmail" class="tab-content">
            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Configuration Webmail (IMAP)</h3>
            <p style="font-size: 13px; color: var(--sec-text-muted); margin-bottom: 20px;">
                Configurez les accès IMAP pour synchroniser les emails du client actuel ({{ $activeClient?->company_name }}). 
                Cela permettra de lire et gérer leurs emails directement depuis le module Webmail.
            </p>
            
            <form action="{{ route('gel-secretary.settings.webmail.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="client_id" value="{{ $activeClient?->id }}">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="sec-form-group">
                            <label class="sec-form-label">Serveur IMAP (Hôte)</label>
                            <input type="text" name="imap_host" class="sec-form-control" value="{{ old('imap_host', $emailConfig?->imap_host) }}" placeholder="imap.gmail.com" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sec-form-group">
                            <label class="sec-form-label">Port IMAP</label>
                            <input type="number" name="imap_port" class="sec-form-control" value="{{ old('imap_port', $emailConfig?->imap_port ?? 993) }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sec-form-group">
                            <label class="sec-form-label">Chiffrement</label>
                            <select name="imap_encryption" class="sec-form-control" required>
                                <option value="ssl" {{ ($emailConfig?->imap_encryption == 'ssl' || !$emailConfig) ? 'selected' : '' }}>SSL</option>
                                <option value="tls" {{ $emailConfig?->imap_encryption == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="false" {{ $emailConfig?->imap_encryption == 'false' ? 'selected' : '' }}>Aucun</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="sec-form-group">
                            <label class="sec-form-label">Adresse Email / Nom d'utilisateur</label>
                            <input type="email" name="imap_username" class="sec-form-control" value="{{ old('imap_username', $emailConfig?->imap_username) }}" placeholder="contact@entreprise.com" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sec-form-group">
                            <label class="sec-form-label">Mot de passe (ou mot de passe d'application)</label>
                            <input type="password" name="imap_password" class="sec-form-control" value="{{ old('imap_password', $emailConfig?->imap_password) }}" required>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="sec-btn" style="background: var(--sec-primary); color: white;">
                        <i class="fas fa-save"></i> Enregistrer la configuration Webmail
                    </button>
                    @if($emailConfig && $emailConfig->is_active)
                        <a href="{{ route('gel-secretary.mail.index') }}" class="sec-btn ms-2" style="background: var(--sec-bg); color: var(--sec-text); border: 1px solid var(--sec-border);">
                            <i class="fas fa-external-link-alt"></i> Ouvrir le Webmail
                        </a>
                    @endif
                </div>
            </form>
        </div>
        
    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }
</script>
@endpush
