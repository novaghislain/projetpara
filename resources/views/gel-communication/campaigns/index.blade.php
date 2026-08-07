@extends(auth()->user()->account_type === 'informaticien' ? 'layouts.gel-informaticien' : 'layouts.gel-communication')

@section('title', 'Campagnes Marketing')

@section('content')
<div class="p-4">
    <div class="sec-page-header">
        <div>
            <h1 class="sec-page-title">Campagnes Marketing</h1>
            <div class="sec-page-sub">Gérez vos campagnes, vos contenus et vos statistiques</div>
        </div>
        <div>
            <!-- Bouton pour créer une campagne manuellement (hors brief) -->
            <button class="sec-btn sec-btn-primary" data-bs-toggle="modal" data-bs-target="#createCampaignModal">
                <i class="fas fa-plus"></i> Nouvelle Campagne
            </button>
        </div>
    </div>

    <div class="sec-card">
        <div class="sec-card-body p-0">
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Nom de la Campagne</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Budget</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td style="font-weight:600;">{{ $campaign->client->company_name ?? 'Inconnu' }}</td>
                            <td>{{ $campaign->name }}</td>
                            <td><span class="sec-badge sec-badge-muted">{{ ucfirst(str_replace('_', ' ', $campaign->type)) }}</span></td>
                            <td style="color:var(--sec-text-muted);">
                                {{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : 'N/A' }} 
                                <i class="fas fa-arrow-right mx-1" style="font-size:10px;"></i> 
                                {{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td>{{ $campaign->budget ? number_format($campaign->budget, 0, ',', ' ') . ' F' : '-' }}</td>
                            <td>
                                @if($campaign->status == 'active')
                                    <span class="sec-badge sec-badge-success">Active</span>
                                @elseif($campaign->status == 'paused')
                                    <span class="sec-badge sec-badge-warning">En pause</span>
                                @else
                                    <span class="sec-badge sec-badge-muted">Terminée</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('gel-communication.campaigns.show', $campaign->id) }}" class="sec-btn sec-btn-sm sec-btn-secondary">Gérer</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-bullhorn fa-3x mb-3" style="color:var(--sec-border);"></i><br>
                                Aucune campagne trouvée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($campaigns->hasPages())
        <div class="p-3 border-top">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Création Campagne -->
<div class="modal fade" id="createCampaignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--sec-primary-light); color:var(--sec-primary);">
                <h5 class="modal-title" style="font-weight:600;"><i class="fas fa-plus me-2"></i> Créer une campagne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('gel-communication.campaigns.store') }}" method="POST">
                    @csrf
                    <div class="sec-form-group">
                        <label>Client</label>
                        <select name="client_id" class="sec-form-select" required>
                            <option value="">Sélectionner un client...</option>
                            @foreach(\App\Models\Client::all() as $client)
                                <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sec-form-group">
                        <label>Nom de la campagne</label>
                        <input type="text" name="name" class="sec-form-control" required>
                    </div>
                    <div class="sec-form-group">
                        <label>Type de campagne</label>
                        <select name="type" class="sec-form-select" required>
                            <option value="social_media">Community Management (Réseaux Sociaux)</option>
                            <option value="ads">Campagne Publicitaire (Ads)</option>
                            <option value="seo">SEO & Référencement</option>
                            <option value="branding">Création Graphique & Branding</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="sec-form-group">
                                <label>Date de début</label>
                                <input type="date" name="start_date" class="sec-form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sec-form-group">
                                <label>Date de fin</label>
                                <input type="date" name="end_date" class="sec-form-control">
                            </div>
                        </div>
                    </div>
                    <div class="sec-form-group">
                        <label>Budget (F CFA)</label>
                        <input type="number" name="budget" class="sec-form-control">
                    </div>
                    
                    <button type="submit" class="sec-btn sec-btn-primary w-100 mt-3">Créer la campagne</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
