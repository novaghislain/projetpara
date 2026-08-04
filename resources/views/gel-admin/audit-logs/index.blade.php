@extends('layouts.gel-admin')

@section('title', 'Historique d\'Audit')

@section('content')
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Historique d'Audit</h1>
        <p class="admin-page-sub">Traçabilité complète des actions effectuées sur le compte.</p>
    </div>
    <a href="{{ route('gel-admin.audit-logs.export', request()->all()) }}" class="admin-btn admin-btn-secondary">
        <i class="fas fa-file-export"></i> Exporter CSV
    </a>
</div>

<div class="admin-card mb-4">
    <div class="admin-card-body p-3 bg-light rounded border-0">
        <form method="GET" action="{{ route('gel-admin.audit-logs.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Action</label>
                <input type="text" name="action" class="form-control form-control-sm" value="{{ request('action') }}" placeholder="Ex: login, update...">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Date de début</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Date de fin</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="admin-btn admin-btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrer</button>
                <a href="{{ route('gel-admin.audit-logs.index') }}" class="admin-btn admin-btn-secondary btn-sm ms-2">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle" style="font-size: 13px;">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3">Date</th>
                        <th class="border-0 px-4 py-3">Utilisateur</th>
                        <th class="border-0 px-4 py-3">Action</th>
                        <th class="border-0 px-4 py-3">Description</th>
                        <th class="border-0 px-4 py-3">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 text-muted whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 fw-medium">{{ $log->actor_name ?? 'Système' }}</td>
                            <td class="px-4"><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td class="px-4 text-muted">{{ $log->description }}</td>
                            <td class="px-4 text-muted small">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Aucun historique trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 border-top">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
