@extends('layouts.gel-business')

@section('title', 'Inviter un collaborateur - Mon Entreprise')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Inviter un collaborateur</h1>
        <p class="gel-page-subtitle">Invitez un cabinet comptable ou une secrétaire à vous rejoindre</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
    {{-- Formulaire d'invitation --}}
    <div class="gel-card">
        <div class="gel-card-header">
            <strong>Envoyer une invitation</strong>
        </div>
        <div class="gel-card-body">
            @if(session('success'))
                <div class="gel-alert gel-alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="gel-alert gel-alert-danger">
                    <ul style="margin:0;padding-left:16px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('gel-business.inviter-comptable.send') }}">
                @csrf

                <div class="gel-form-group">
                    <label>Rôle du collaborateur *</label>
                    <select name="role_invite" class="gel-form-control" required>
                        <option value="comptable">Comptable</option>
                        <option value="secretaire">Secrétaire</option>
                    </select>
                </div>

                <div class="gel-form-group">
                    <label>Email du collaborateur *</label>
                    <input type="email" name="email" class="gel-form-control" required placeholder="email@exemple.fr">
                    <div class="gel-form-text">L'email de la personne que vous souhaitez inviter.</div>
                </div>

                <div class="gel-form-group">
                    <label>Message (optionnel)</label>
                    <textarea name="message" class="gel-form-control" rows="4" placeholder="Bonjour, nous souhaitons vous confier notre comptabilité...">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="gel-btn gel-btn-primary">
                    <i class="bi bi-send"></i> Envoyer l'invitation
                </button>
            </form>
        </div>
    </div>

    {{-- Informations --}}
    <div>
        <div class="gel-card">
            <div class="gel-card-header">
                <strong>Comment ça marche ?</strong>
            </div>
            <div class="gel-card-body">
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <div style="display:flex;gap:12px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--gel-primary-light);color:var(--gel-primary);display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">1</div>
                        <div>
                            <strong>Envoyez l'invitation</strong>
                            <p style="font-size:12px;color:var(--gel-text-secondary);margin:2px 0 0;">Saisissez l'email de votre cabinet comptable.</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--gel-primary-light);color:var(--gel-primary);display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">2</div>
                        <div>
                            <strong>Le comptable accepte</strong>
                            <p style="font-size:12px;color:var(--gel-text-secondary);margin:2px 0 0;">Votre comptable reçoit un email avec un lien d'acceptation.</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--gel-primary-light);color:var(--gel-primary);display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">3</div>
                        <div>
                            <strong>Comptabilité gérée</strong>
                            <p style="font-size:12px;color:var(--gel-text-secondary);margin:2px 0 0;">Votre comptable accède à vos données et gère votre comptabilité.</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--gel-primary-light);color:var(--gel-primary);display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">4</div>
                        <div>
                            <strong>Vous suivez</strong>
                            <p style="font-size:12px;color:var(--gel-text-secondary);margin:2px 0 0;">Consultez votre grand livre, balance et états financiers en temps réel.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($invitations) && count($invitations) > 0)
            <div class="gel-card" style="margin-top:16px;">
                <div class="gel-card-header">
                    <strong>Invitations envoyées</strong>
                </div>
                <div class="gel-card-body" style="padding:0;">
                    @foreach($invitations as $inv)
                        <div style="padding:12px 16px;border-bottom:1px solid var(--gel-border-light);display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <div style="font-size:13px;">{{ $inv->email }}</div>
                                <div style="font-size:11px;color:var(--gel-text-muted);">
                                    {{ $inv->created_at->format('d/m/Y') }} • {{ ucfirst($inv->role_invite ?? 'Comptable') }}
                                </div>
                            </div>
                            <div>
                                @if($inv->statut === 'en_attente')
                                    <span class="gel-badge gel-badge-warning">En attente</span>
                                @elseif($inv->statut === 'acceptee')
                                    <span class="gel-badge gel-badge-success">Acceptée</span>
                                @else
                                    <span class="gel-badge gel-badge-danger">{{ $inv->statut }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
