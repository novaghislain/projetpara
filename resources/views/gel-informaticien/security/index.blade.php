@extends('layouts.gel-informaticien')

@section('title', 'Sécurité & Accès')

@section('content')
<div class="p-4">
    <div class="animate-fade" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: var(--sec-text-primary); margin-bottom: 4px;">Sécurité & Accès</h1>
            <p style="font-size: 12px; color: var(--sec-text-muted);">Gestion des accès bloqués, des sessions et du journal technique.</p>
        </div>
    </div>

    <div class="row g-4 animate-fade delay-1">
        <!-- Comptes Suspendus -->
        <div class="col-lg-12">
            <div class="pro-panel mb-4" style="border-top: 4px solid #ef4444;">
                <div class="sec-card-header d-flex justify-content-between align-items-center" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                    <h5 class="mb-0" style="font-weight:600; font-size:15px; color:#ef4444;"><i class="fas fa-users-cog me-2"></i> Gestion des Comptes Internes</h5>
                    <form action="{{ route('gel-informaticien.security.index') }}" method="GET" class="d-flex" style="max-width: 300px;">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Rechercher un compte...">
                        <button type="submit" class="btn btn-sm btn-outline-secondary ms-1"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="sec-table" style="margin-bottom:0;">
                            <thead style="background:#fef2f2;">
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Raison du Blocage</th>
                                    <th>Date du Blocage</th>
                                    <th>Actions Techniques</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($internalUsers as $user)
                                    <tr style="{{ $user->is_suspended ? 'background:#fef2f2;' : '' }}">
                                        <td style="font-weight:600;">
                                            {{ $user->prenom }} {{ $user->name }}
                                            @if($user->is_suspended)
                                                <span class="sec-badge sec-badge-danger ms-2"><i class="fas fa-ban"></i> Suspendu</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td><span class="sec-badge sec-badge-muted">{{ $user->account_type }}</span></td>
                                        <td>{{ $user->is_suspended ? ($user->suspended_reason ?: 'Non précisé') : '-' }}</td>
                                        <td style="color:var(--sec-text-muted); font-size:12px;">{{ $user->is_suspended && $user->suspended_at ? $user->suspended_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>
                                            <div class="btn-group">
                                                @if($user->is_suspended)
                                                <form action="{{ route('gel-informaticien.security.unlock', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Débloquer ce compte ?')" style="font-weight:600;">
                                                        <i class="fas fa-unlock me-1"></i> Débloquer
                                                    </button>
                                                </form>
                                                @else
                                                <form action="{{ route('gel-informaticien.security.suspend', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Suspendre ce compte ?')" style="font-weight:600;">
                                                        <i class="fas fa-ban me-1"></i> Suspendre
                                                    </button>
                                                </form>
                                                @endif
                                                <form action="{{ route('gel-informaticien.security.reset_password', $user->id) }}" method="POST" class="d-inline ms-1">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning text-dark" onclick="return confirm('Forcer la réinitialisation du mot de passe ?')" style="font-weight:600;" title="Forcer Reset MDP">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('gel-informaticien.security.download_logs', $user->id) }}" class="btn btn-sm btn-info text-white ms-1" style="font-weight:600;" title="Télécharger l'historique complet">
                                                    <i class="fas fa-download"></i> Logs
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-users fa-3x mb-3" style="color:#64748b; opacity:0.5;"></i><br>
                                            Aucun compte interne trouvé.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($internalUsers->hasPages())
                    <div class="p-3 border-top">
                        {{ $internalUsers->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Journal Sécurité -->
        <div class="col-lg-12">
            <div class="pro-panel mb-4" style="border-top: 4px solid var(--sec-warning);">
                <div class="sec-card-header" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                    <h5 class="mb-0" style="font-weight:600; font-size:15px; color:#d97706;"><i class="fas fa-exclamation-circle me-2"></i> Alertes Techniques (50 dernières)</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="sec-table" style="margin-bottom:0;">
                            <thead style="background:#fffbeb;">
                                <tr>
                                    <th>Date</th>
                                    <th>IP</th>
                                    <th>Événement</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($securityAlerts as $alert)
                                    <tr>
                                        <td style="width: 150px; color:var(--sec-text-muted); font-size:12px;">{{ $alert->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td style="width: 130px;"><span class="sec-badge sec-badge-muted">{{ $alert->ip_address }}</span></td>
                                        <td>
                                            @if($alert->event == 'LOGIN_FAILED')
                                                <span class="sec-badge sec-badge-danger">ECHEC_CONNEXION</span>
                                            @elseif($alert->event == '2FA_FAILED')
                                                <span class="sec-badge sec-badge-warning">ECHEC_2FA</span>
                                            @else
                                                <span class="sec-badge sec-badge-muted">{{ $alert->event }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $alert->description }}</td>
                                        <td>
                                            @if(!$alert->is_resolved)
                                            <form action="{{ route('gel-informaticien.security.resolve_alert', $alert->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Marquer comme traitée">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            @else
                                                <span class="text-success"><i class="fas fa-check-double"></i> Traitée</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-3x mb-3" style="color:#22c55e; opacity:0.5;"></i><br>
                                            Aucune alerte récente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Accès Temporaires -->
        <div class="col-lg-12">
            <div class="pro-panel mb-4" style="border-top: 4px solid var(--sec-info);">
                <div class="sec-card-header" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                    <h5 class="mb-0" style="font-weight:600; font-size:15px; color:var(--sec-info);"><i class="fas fa-key me-2"></i> Accès Exceptionnels en Cours</h5>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="sec-table" style="margin-bottom:0;">
                            <thead style="background:#eff6ff;">
                                <tr>
                                    <th>Client (Cible)</th>
                                    <th>Informaticien</th>
                                    <th>Raison / Motif</th>
                                    <th>Expiration</th>
                                    <th>Action d'Urgence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeAccesses as $access)
                                    <tr>
                                        <td style="font-weight:600;">{{ $access->client->company_name }}</td>
                                        <td>{{ $access->informaticien->prenom }} {{ $access->informaticien->name }}</td>
                                        <td>{{ $access->reason }}</td>
                                        <td style="color:var(--sec-text-muted); font-size:12px;">{{ $access->expires_at->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            <form action="{{ route('gel-informaticien.security.revoke_access', $access->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger fw-bold" onclick="return confirm('Voulez-vous vraiment révoquer immédiatement cet accès ?')">
                                                    <i class="fas fa-ban me-1"></i> Révoquer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-shield-alt fa-3x mb-3" style="color:#22c55e; opacity:0.5;"></i><br>
                                            Aucun accès exceptionnel en cours.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
