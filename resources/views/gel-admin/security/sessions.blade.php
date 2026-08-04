@extends('layouts.gel-admin')

@section('title', 'Gestion des Sessions')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Sessions Actives</h1>
        <p class="admin-page-sub">Consultez les appareils actuellement connectés à votre compte.</p>
    </div>
    <a href="{{ route('gel-admin.security.index') }}" class="admin-btn admin-btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

<div class="admin-card mb-4">
    <div class="admin-card-header">
        <div class="admin-card-title">Appareils connectés</div>
    </div>
    <div class="admin-card-body p-0">
        @if(count($sessions) > 0)
            <table class="table mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">Appareil</th>
                        <th class="border-0 px-4 py-3">Adresse IP</th>
                        <th class="border-0 px-4 py-3">Dernière activité</th>
                        <th class="border-0 px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                        <tr>
                            <td class="px-4 fw-medium">
                                <i class="fas fa-desktop me-2 text-muted"></i>
                                Appareil inconnu <!-- You'd parse the User-Agent here -->
                                @if($session->id === session()->getId())
                                    <span class="badge bg-success ms-2">Cet appareil</span>
                                @endif
                            </td>
                            <td class="px-4 text-muted">{{ $session->ip_address }}</td>
                            <td class="px-4 text-muted">{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</td>
                            <td class="px-4 text-end">
                                @if($session->id !== session()->getId())
                                    <form action="{{ route('gel-admin.security.sessions.revoke', $session->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Déconnecter</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-4 text-center text-muted">
                <i class="fas fa-info-circle me-2"></i> Pilote de session non pris en charge pour cette fonctionnalité (nécessite le driver 'database').
            </div>
        @endif
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">Historique des connexions (10 dernières)</div>
    </div>
    <div class="admin-card-body p-0">
        @if(count($loginLogs) > 0)
            <table class="table mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">Date</th>
                        <th class="border-0 px-4 py-3">Adresse IP</th>
                        <th class="border-0 px-4 py-3">Navigateur / Appareil</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loginLogs as $log)
                        <tr>
                            <td class="px-4 fw-medium">{{ \Carbon\Carbon::parse($log->login_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 text-muted">{{ $log->ip_address }}</td>
                            <td class="px-4 text-muted">{{ Str::limit($log->user_agent, 50) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-4 text-center text-muted">
                Aucun historique de connexion trouvé.
            </div>
        @endif
    </div>
</div>
@endsection
