@extends('layouts.gel-informaticien')

@section('title', 'Sauvegardes & Maintenance')

@section('content')
<div class="p-4">
    <div class="animate-fade" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 24px; display:flex; justify-content:space-between; align-items:flex-end;">
        <div>
            <h1 style="font-size: 18px; font-weight: 700; color: var(--sec-text-primary); margin-bottom: 4px;">Sauvegardes & Maintenance</h1>
            <p style="font-size: 12px; color: var(--sec-text-muted);">Gestion des logs de backup et des alertes d'interruption de service.</p>
        </div>
    </div>

    <div class="row g-4 animate-fade delay-1">
        <!-- Log des Backups -->
        <div class="col-lg-8">
            <div class="pro-panel mb-4" style="border-top: 4px solid #10B981;">
                <div class="sec-card-header d-flex justify-content-between align-items-center" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                    <h5 class="mb-0" style="font-weight:600; font-size:15px; color:#10B981;"><i class="fas fa-database me-2"></i> Historique des Sauvegardes (Automatisées)</h5>
                    <button class="btn btn-sm" style="background:#ECFDF5;color:#10B981; font-weight:600;" onclick="alert('La sauvegarde manuelle nécessite l\'accès SSH au serveur.');">Forcer Sauvegarde</button>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive">
                        <table class="sec-table" style="margin-bottom:0;">
                            <thead style="background:#f0fdf4;">
                                <tr>
                                    <th>Date d'exécution</th>
                                    <th>Statut</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $backup)
                                    <tr>
                                        <td style="color:var(--sec-text-muted); font-size:12px;">{{ $backup->created_at->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            @if($backup->action == 'system_backup_success')
                                                <span class="sec-badge sec-badge-success"><i class="fas fa-check-circle"></i> Succès</span>
                                            @else
                                                <span class="sec-badge sec-badge-danger"><i class="fas fa-times-circle"></i> Échec</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $backup->description }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="fas fa-server fa-3x mb-3" style="color:#10B981; opacity:0.5;"></i><br>
                                            Aucun log de sauvegarde récent trouvé.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Planification Maintenance -->
        <div class="col-lg-4">
            <div class="pro-panel mb-4" style="border-top: 4px solid var(--sec-info);">
                <div class="sec-card-header" style="padding:16px 20px; border-bottom:1px solid var(--sec-border);">
                    <h5 class="mb-0" style="font-weight:600; font-size:15px;"><i class="fas fa-calendar-alt me-2" style="color:var(--sec-info);"></i> Planifier une Maintenance</h5>
                </div>
                <div class="panel-body" style="padding:20px;">
                    <p class="text-muted small mb-4">Cette action affichera une bannière d'alerte sur tous les portails (Clients, Secrétaires, Comptables) pour prévenir d'une interruption de service.</p>
                    
                    <form action="{{ route('gel-informaticien.maintenance.store_window') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Date et Heure de début</label>
                            <input type="datetime-local" name="starts_at" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Durée estimée (heures)</label>
                            <input type="number" name="duration" class="form-control" value="1" min="1" max="24" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Message d'information</label>
                            <textarea name="message" class="form-control" rows="3" required>Une maintenance technique est programmée. La plateforme sera indisponible.</textarea>
                        </div>
                        <button type="submit" class="sec-btn w-100 justify-content-center" style="background:var(--sec-info); color:white; font-weight:600;">Programmer l'alerte</button>
                    </form>
                    
                    @if($windows->count() > 0)
                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Maintenances récentes</h6>
                        <ul class="list-group list-group-flush small">
                            @foreach($windows as $w)
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $w->starts_at->format('d/m/Y H:i') }}</strong>
                                        <span class="text-muted">{{ $w->starts_at->diffInHours($w->ends_at) }}h</span>
                                    </div>
                                    <p class="mb-0 text-muted">{{ Str::limit($w->message, 50) }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
