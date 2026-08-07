@extends('layouts.gel-communication')

@section('title', $campaign->name)

@section('content')
<div class="p-4">
    <div class="mb-4">
        <a href="{{ route('gel-communication.campaigns.index') }}" style="color:var(--sec-text-muted); font-size:13px;">
            <i class="fas fa-arrow-left me-1"></i> Retour aux campagnes
        </a>
    </div>

    <div class="sec-page-header">
        <div>
            <h1 class="sec-page-title">{{ $campaign->name }}</h1>
            <div class="sec-page-sub">Client : <strong>{{ $campaign->client->company_name ?? 'Inconnu' }}</strong> | Type : {{ ucfirst(str_replace('_', ' ', $campaign->type)) }}</div>
        </div>
        <div class="d-flex gap-2">
            @if($campaign->status == 'active')
                <span class="sec-badge sec-badge-success" style="font-size:13px; padding:6px 12px;">Active</span>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Section Visuels & Contenus -->
        <div class="col-lg-8">
            <div class="sec-card mb-4">
                <div class="sec-card-header">
                    <h5 class="sec-card-title m-0"><i class="fas fa-images me-2" style="color:var(--sec-primary);"></i> Visuels et Livrables</h5>
                    <button class="sec-btn sec-btn-sm sec-btn-primary" data-bs-toggle="modal" data-bs-target="#uploadContentModal">
                        <i class="fas fa-upload"></i> Ajouter un contenu
                    </button>
                </div>
                <div class="sec-card-body p-0">
                    <table class="sec-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Fichier</th>
                                <th>Statut de Validation</th>
                                <th>Commentaire Client</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaign->contents as $content)
                                <tr>
                                    <td style="font-weight:600;">{{ $content->title }}</td>
                                    <td>
                                        <a href="#" style="color:var(--sec-info);"><i class="fas fa-file-image me-1"></i> Voir</a>
                                    </td>
                                    <td>
                                        @if($content->status == 'pending_client')
                                            <span class="sec-badge sec-badge-warning"><i class="fas fa-hourglass-half"></i> En attente client</span>
                                        @elseif($content->status == 'approved')
                                            <span class="sec-badge sec-badge-success"><i class="fas fa-check"></i> Approuvé</span>
                                        @elseif($content->status == 'rejected')
                                            <span class="sec-badge sec-badge-danger"><i class="fas fa-times"></i> Rejeté (À modifier)</span>
                                        @else
                                            <span class="sec-badge sec-badge-muted">Brouillon</span>
                                        @endif
                                    </td>
                                    <td style="font-size:12px; font-style:italic; color:var(--sec-text-muted);">
                                        {{ $content->client_feedback ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Aucun contenu soumis pour cette campagne</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section Statistiques -->
        <div class="col-lg-4">
            <div class="sec-card mb-4">
                <div class="sec-card-header">
                    <h5 class="sec-card-title m-0"><i class="fas fa-chart-bar me-2" style="color:var(--sec-info);"></i> Performances</h5>
                </div>
                <div class="sec-card-body">
                    <div style="font-size:12px; color:var(--sec-text-muted); margin-bottom:15px;">
                        Vue d'ensemble des dernières métriques rapportées.
                    </div>
                    @php
                        $totalFollowers = $campaign->stats->sum('followers_gained');
                        $totalReach = $campaign->stats->sum('reach');
                        $totalSpend = $campaign->stats->sum('spend');
                    @endphp

                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid var(--sec-border); padding-bottom:10px;">
                        <span style="font-weight:600; font-size:13px;">Followers gagnés</span>
                        <span class="sec-badge sec-badge-info">+{{ number_format($totalFollowers, 0, ',', ' ') }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; border-bottom:1px solid var(--sec-border); padding-bottom:10px;">
                        <span style="font-weight:600; font-size:13px;">Personnes touchées (Reach)</span>
                        <span>{{ number_format($totalReach, 0, ',', ' ') }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                        <span style="font-weight:600; font-size:13px;">Dépense Publicitaire</span>
                        <span style="color:var(--sec-danger); font-weight:700;">{{ number_format($totalSpend, 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>

            <!-- Détails Campagne -->
            <div class="sec-card">
                <div class="sec-card-header">
                    <h5 class="sec-card-title m-0">Informations</h5>
                </div>
                <div class="sec-card-body" style="font-size:13px;">
                    <p><strong>Budget prévu:</strong> {{ $campaign->budget ? number_format($campaign->budget, 0, ',', ' ') . ' F' : 'Non défini' }}</p>
                    <p><strong>Date de début:</strong> {{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : 'N/A' }}</p>
                    <p><strong>Date de fin:</strong> {{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Contenu -->
<div class="modal fade" id="uploadContentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--sec-primary-light); color:var(--sec-primary);">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-upload me-2"></i> Soumettre un visuel au client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('gel-communication.contents.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">
                    <div class="sec-form-group">
                        <label>Titre du visuel / contenu</label>
                        <input type="text" name="title" class="sec-form-control" required placeholder="Ex: Maquette Logo V1">
                    </div>
                    <div class="sec-form-group">
                        <label>Description (Optionnel)</label>
                        <textarea name="description" class="sec-form-control" rows="3" placeholder="Explications pour le client..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="sec-form-group">
                                <label>Date de publication prévue</label>
                                <input type="datetime-local" name="publish_date" class="sec-form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sec-form-group">
                                <label>Plateforme</label>
                                <select name="platform" class="sec-form-select">
                                    <option value="">Sélectionner...</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="LinkedIn">LinkedIn</option>
                                    <option value="Twitter">Twitter / X</option>
                                    <option value="Site Web">Site Web</option>
                                    <option value="Newsletter">Newsletter</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="sec-form-group">
                        <label>Fichier visuel (Demo)</label>
                        <input type="file" class="sec-form-control" disabled>
                        <small class="text-muted mt-1">L'upload réel est désactivé pour la démo.</small>
                    </div>
                    
                    <button type="submit" class="sec-btn sec-btn-primary w-100 mt-3">Envoyer pour validation</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
