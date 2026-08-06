@extends('layouts.gel-informaticien')

@section('title', 'Sécurité & Accès')

@section('content')
<div class="row g-4">
    <!-- Comptes Suspendus -->
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 text-danger"><i class="fas fa-user-lock me-2"></i> Comptes Bloqués / Suspendus</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
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
                            @forelse($suspendedUsers as $user)
                                <tr>
                                    <td>{{ $user->prenom }} {{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge bg-secondary">{{ $user->account_type }}</span></td>
                                    <td>{{ $user->suspended_reason ?: 'Non précisé' }}</td>
                                    <td>{{ $user->suspended_at ? $user->suspended_at->format('d/m/Y H:i') : 'Inconnue' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <form action="{{ route('gel-informaticien.security.unlock', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Débloquer ce compte ?')">
                                                    <i class="fas fa-unlock me-1"></i> Débloquer
                                                </button>
                                            </form>
                                            <form action="{{ route('gel-informaticien.security.reset_password', $user->id) }}" method="POST" class="d-inline ms-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Forcer la réinitialisation du mot de passe ?')">
                                                    <i class="fas fa-key me-1"></i> Reset MDP
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Aucun compte actuellement suspendu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Journal Sécurité -->
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 text-warning"><i class="fas fa-exclamation-circle me-2"></i> Alertes Techniques (50 dernières)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>IP</th>
                                <th>Événement</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($securityAlerts as $alert)
                                <tr>
                                    <td style="width: 150px;">{{ $alert->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td style="width: 130px;"><span class="badge bg-light text-dark">{{ $alert->ip_address }}</span></td>
                                    <td>
                                        @if($alert->event == 'LOGIN_FAILED')
                                            <span class="badge bg-danger">ECHEC_CONNEXION</span>
                                        @elseif($alert->event == '2FA_FAILED')
                                            <span class="badge bg-warning text-dark">ECHEC_2FA</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $alert->event }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $alert->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Aucune alerte récente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
