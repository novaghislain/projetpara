@extends('layouts.gel-accountant')

@section('title', 'Écritures - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Écritures comptables</h1>
        <p class="gel-page-subtitle">{{ $ecritures->total() ?? 0 }} écriture(s) — {{ $totalDebit ?? 0 }} FCFA au débit / {{ $totalCredit ?? 0 }} FCFA au crédit</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('gel-accountant.comptabilite.ecritures.create') }}" class="gel-btn gel-btn-primary">
            <i class="bi bi-plus-circle"></i> Nouvelle écriture
        </a>
        <a href="{{ route('gel-accountant.ecritures.export') }}" class="gel-btn gel-btn-secondary">
            <i class="bi bi-download"></i> Exporter CSV
        </a>
        <button class="gel-btn gel-btn-secondary" onclick="document.getElementById('filtersPanel').classList.toggle('show')">
            <i class="bi bi-funnel"></i> Filtres
        </button>
    </div>
</div>

{{-- Filtres --}}
<div class="gel-card" id="filtersPanel" style="margin-bottom:16px;{{ request()->anyFilled(['client_id','journal_id','date_from','date_to','statut']) ? '' : 'display:none;' }}">
    <div class="gel-card-body p-4 mb-4">
        <form method="GET" action="{{ route('gel-accountant.comptabilite.ecritures') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div class="gel-filter-group">
                <label>Client</label>
                <select name="client_id" class="gel-filter-select">
                    <option value="">Tous</option>
                    @if(isset($clients) && count($clients) > 0)
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="gel-filter-group">
                <label>Journal</label>
                <select name="journal_id" class="gel-filter-select">
                    <option value="">Tous</option>
                    @if(isset($journaux) && count($journaux) > 0)
                        @foreach($journaux as $j)
                            <option value="{{ $j->id }}" {{ request('journal_id') == $j->id ? 'selected' : '' }}>{{ $j->code }} - {{ $j->libelle }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="gel-filter-group">
                <label>Du</label>
                <input type="date" name="date_from" class="gel-filter-select" value="{{ request('date_from') }}">
            </div>
            <div class="gel-filter-group">
                <label>Au</label>
                <input type="date" name="date_to" class="gel-filter-select" value="{{ request('date_to') }}">
            </div>
            <div class="gel-filter-group">
                <label>Statut</label>
                <select name="statut" class="gel-filter-select">
                    <option value="">Tous</option>
                    <option value="valide" {{ request('statut') === 'valide' ? 'selected' : '' }}>Validées</option>
                    <option value="brouillon" {{ request('statut') === 'brouillon' ? 'selected' : '' }}>Brouillons</option>
                </select>
            </div>
            <button type="submit" class="gel-btn gel-btn-primary gel-btn-sm">Filtrer</button>
            <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" class="gel-btn gel-btn-secondary gel-btn-sm">Réinitialiser</a>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="gel-card p-4 mb-4">
    <div class="gel-card-body p-4 mb-4">
        @if($ecritures->count() > 0)
            <table class="gel-table">
                <thead>
                    <tr>
                        <th style="width:100px;">N°</th>
                        <th style="width:90px;">Date</th>
                        <th>Libellé</th>
                        <th>Journal</th>
                        <th style="width:80px;">Client</th>
                        <th class="gel-text-right" style="width:130px;">Débit</th>
                        <th class="gel-text-right" style="width:130px;">Crédit</th>
                        <th style="width:80px;">Statut</th>
                        <th style="width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ecritures as $e)
                        <tr>
                            <td><strong>{{ $e->numero ?? '—' }}</strong></td>
                            <td>{{ $e->date_ecriture->format('d/m/Y') }}</td>
                            <td>
                                {{ Str::limit($e->libelle, 40) }}
                                @if($e->ref_piece)
                                    <br><small style="color:var(--gel-text-muted);">Pièce: {{ $e->ref_piece }}</small>
                                @endif
                            </td>
                            <td>{{ $e->journal->code ?? '—' }}</td>
                            <td>{{ $e->client->nom_entreprise ?? '—' }}</td>
                            <td class="gel-text-right">{{ number_format($e->total_debit, 0, ',', ' ') }}</td>
                            <td class="gel-text-right">{{ number_format($e->total_credit, 0, ',', ' ') }}</td>
                            <td>
                                @if($e->valide)
                                    <span class="gel-badge gel-badge-success">Validée</span>
                                @else
                                    <span class="gel-badge gel-badge-warning">Brouillon</span>
                                @endif
                            </td>
                            <td>
                                <div class="gel-dropdown">
                                    <button class="gel-btn gel-btn-sm gel-btn-secondary" onclick="event.stopPropagation();this.nextElementSibling.classList.toggle('show')">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="gel-dropdown-menu">
                                        <a href="{{ route('gel-accountant.comptabilite.ecritures.show', $e->id) }}" class="gel-dropdown-item">
                                            <i class="bi bi-eye"></i> Voir
                                        </a>
                                        @if(!$e->valide)
                                            <a href="{{ route('gel-accountant.comptabilite.ecritures.edit', $e->id) }}" class="gel-dropdown-item">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </a>
                                            <button class="gel-dropdown-item" onclick="if(confirm('Valider cette écriture ?'))window.location.href='{{ route('gel-accountant.comptabilite.ecritures.valider', $e->id) }}'">
                                                <i class="bi bi-check-lg"></i> Valider
                                            </button>
                                            <div class="gel-dropdown-divider"></div>
                                            <button class="gel-dropdown-item" style="color:var(--gel-danger);" onclick="if(confirm('Supprimer cette écriture ?'))window.location.href='{{ route('gel-accountant.comptabilite.ecritures.destroy', $e->id) }}'">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="gel-empty">
                <i class="bi bi-journal-text"></i>
                <h3>Aucune écriture</h3>
                <p>Créez votre première écriture comptable ou ajustez les filtres.</p>
                <a href="{{ route('gel-accountant.comptabilite.ecritures.create') }}" class="gel-btn gel-btn-primary">Nouvelle écriture</a>
            </div>
        @endif
    </div>
</div>

@if($ecritures->hasPages())
    <div class="gel-pagination">
        {{ $ecritures->links() }}
    </div>
@endif
@endsection
