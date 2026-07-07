@extends('layouts.gel-accountant')

@section('title', 'Journaux - GEL Cabinet')

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Journaux</h1>
        <p class="gel-page-subtitle">{{ $journaux->count() ?? 0 }} journal(aux) comptable(s)</p>
    </div>
    <button class="gel-btn gel-btn-primary" onclick="openPanel('Nouveau journal', `
        <form method="POST" action="{{ route('gel-accountant.comptabilite.journaux.store') }}" id="newJournalForm">
            @csrf
            <div class="gel-form-group">
                <label>Code *</label>
                <input type="text" name="code" class="gel-form-control" required placeholder="Ex: VTE">
            </div>
            <div class="gel-form-group">
                <label>Libellé *</label>
                <input type="text" name="libelle" class="gel-form-control" required>
            </div>
            <div class="gel-form-group">
                <label>Type</label>
                <select name="type" class="gel-form-select">
                    <option value="">—</option>
                    <option value="ventes">Ventes</option>
                    <option value="achats">Achats</option>
                    <option value="banque">Banque</option>
                    <option value="caisse">Caisse</option>
                    <option value="operations_diverses">Opérations diverses</option>
                </select>
            </div>
            <div class="gel-form-group">
                <label>Client</label>
                <select name="client_id" class="gel-form-select">
                    <option value="">Tous les clients</option>
                    @if(isset($clients) && count($clients) > 0)
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->nom_entreprise }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </form>
    `, '<button class="gel-btn gel-btn-secondary" onclick="closePanel()">Annuler</button><button class="gel-btn gel-btn-primary" onclick="document.getElementById(\'newJournalForm\').submit()">Créer</button>')">
        <i class="bi bi-plus-circle"></i> Nouveau journal
    </button>
</div>

<div class="gel-card">
    <div class="gel-card-body" style="padding:0;">
        @if($journaux->count() > 0)
            <table class="gel-table">
                <thead>
                    <tr>
                        <th style="width:80px;">Code</th>
                        <th>Libellé</th>
                        <th style="width:120px;">Type</th>
                        <th style="width:100px;">Client</th>
                        <th style="width:60px;">Statut</th>
                        <th style="width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journaux as $journal)
                        <tr>
                            <td><strong>{{ $journal->code }}</strong></td>
                            <td>{{ $journal->libelle }}</td>
                            <td>{{ $journal->type ?? '—' }}</td>
                            <td>{{ $journal->client->nom_entreprise ?? 'Tous' }}</td>
                            <td>
                                @if($journal->actif)
                                    <span class="gel-badge gel-badge-success">Actif</span>
                                @else
                                    <span class="gel-badge gel-badge-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <button class="gel-btn gel-btn-sm gel-btn-secondary" onclick="openPanel('Journal {{ $journal->code }}', \`
                                    <div class="gel-form-group"><label>Code</label><input class="gel-form-control" value="{{ $journal->code }}" readonly></div>
                                    <div class="gel-form-group"><label>Libellé</label><input class="gel-form-control" value="{{ $journal->libelle }}" readonly></div>
                                    <div class="gel-form-group"><label>Type</label><input class="gel-form-control" value="{{ $journal->type ?? '—' }}" readonly></div>
                                \`)"><i class="bi bi-eye"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="gel-empty">
                <i class="bi bi-bookmark-check"></i>
                <h3>Aucun journal</h3>
                <p>Créez vos journaux comptables (Ventes, Achats, Banque, Caisse, OD).</p>
            </div>
        @endif
    </div>
</div>
@endsection
