@extends('layouts.gel-super-admin')
@section('title', 'Historique d\'Audit Plateforme')
@section('content')
<h1 class="page-title">Historique d'Audit Plateforme</h1>
<p class="page-subtitle">Registre consolidé de toutes les actions effectuées par les entreprises et les super administrateurs.</p>

<div class="card shadow-sm border-0 h-100 p-4 mb-4">
    <form action="{{ route('gel-super-admin.platform.audit') }}" method="GET" class="row g-3">
        <div class="col-md-3">
            <input type="text" name="event" class="form-control" placeholder="Action (ex: UPDATE_PLAN)" value="{{ request('event') }}">
        </div>
        <div class="col-md-3">
            <select name="user_id" class="form-select">
                <option value="">Tous les utilisateurs</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                <span class="input-group-text">à</span>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>
</div>

<div class="card shadow-sm border-0 h-100 p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle table-borderless">
            <thead>
                <tr>
                    <th>Horodatage</th>
                    <th>Utilisateur</th>
                    <th>Événement</th>
                    <th>Entité</th>
                    <th>Détails</th>
                    <th>Adresse IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-muted" style="font-size:0.85rem">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                        @if($log->user)
                            <div class="fw-bold">{{ $log->user->name }}</div>
                            <div class="text-muted" style="font-size:0.8rem">{{ $log->user->email }}</div>
                        @else
                            <span class="text-muted">Système</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $log->event }}</span></td>
                    <td class="text-muted" style="font-size:0.85rem">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                    <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $log->description }}">
                        {{ $log->description }}
                    </td>
                    <td class="text-muted" style="font-size:0.85rem">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Aucun enregistrement d'audit trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
