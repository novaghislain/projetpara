@extends('layouts.gel-informaticien')

@section('title', 'Sauvegardes & Maintenance')

@section('content')
<div class="row g-4">
    <!-- Log des Backups -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-success"><i class="fas fa-database me-2"></i> Historique des Sauvegardes (Automatisées)</h5>
                <button class="btn btn-sm btn-outline-success" onclick="alert('La sauvegarde manuelle nécessite l\'accès SSH au serveur.');">Forcer Sauvegarde</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date d'exécution</th>
                                <th>Statut</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr>
                                    <td>{{ $backup->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        @if($backup->action == 'system_backup_success')
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Succès</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Échec</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $backup->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Aucun log de sauvegarde récent trouvé.</td>
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
        <div class="card shadow-sm border-0 mb-4 border-top border-4 border-primary">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0">Planifier une Maintenance</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">Cette action affichera une bannière d'alerte sur tous les portails (Clients, Secrétaires, Comptables) pour prévenir d'une interruption de service.</p>
                
                <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Fonctionnalité de notification globale en cours d\'intégration.');">
                    <div class="mb-3">
                        <label class="form-label">Date et Heure de début</label>
                        <input type="datetime-local" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durée estimée (heures)</label>
                        <input type="number" class="form-control" value="1" min="1" max="24" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Message d'information</label>
                        <textarea class="form-control" rows="3" required>Une maintenance technique est programmée. La plateforme sera indisponible.</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Programmer l'alerte</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
