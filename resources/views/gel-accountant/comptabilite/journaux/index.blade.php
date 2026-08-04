@extends('layouts.gel-accountant')

@section('title', 'Journaux - GEL Cabinet')

@section('content')
<div class="gel-page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 class="gel-page-title" style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">Journaux Comptables</h1>
        <p class="gel-page-subtitle" style="color: #64748b; margin-top: 4px; font-size: 14px;">{{ $journaux->count() ?? 0 }} journal(aux) enregistré(s)</p>
    </div>
    <button class="gel-btn gel-btn-primary" onclick="toggleNewJournalForm()" style="display: inline-flex; align-items: center; gap: 8px;">
        <i class="bi bi-plus-lg"></i> Nouveau journal
    </button>
</div>

{{-- Formulaire Nouveau Journal (Masqué par défaut) --}}
<div id="newJournalCard" class="gel-card mb-4" style="display: none; border-left: 4px solid var(--gel-primary, #059669); transition: all 0.3s ease;">
    <div class="gel-card-header" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0; color: #0f172a;"><i class="bi bi-journal-plus me-2" style="color: var(--gel-primary, #059669);"></i>Créer un nouveau journal</h3>
        <button type="button" onclick="toggleNewJournalForm()" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer;">&times;</button>
    </div>
    <div class="gel-card-body" style="padding: 20px;">
        <form method="POST" action="{{ route('gel-accountant.comptabilite.journaux.store') }}" id="newJournalForm">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
                <div class="gel-form-group">
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Code *</label>
                    <input type="text" name="code" class="gel-form-control" required placeholder="Ex: VTE" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; text-transform: uppercase;">
                </div>
                <div class="gel-form-group">
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Libellé *</label>
                    <input type="text" name="libelle" class="gel-form-control" required placeholder="Ex: Journal des ventes" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div class="gel-form-group">
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Type</label>
                    <select name="type" class="gel-form-select" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff;">
                        <option value="">— Sélectionner —</option>
                        <option value="ventes">Ventes</option>
                        <option value="achats">Achats</option>
                        <option value="banque">Banque</option>
                        <option value="caisse">Caisse</option>
                        <option value="operations_diverses">Opérations diverses</option>
                    </select>
                </div>
                <div class="gel-form-group">
                    <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Client rattaché</label>
                    <select name="client_id" class="gel-form-select" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff;">
                        <option value="">Tous les clients</option>
                        @if(isset($clients) && count($clients) > 0)
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->nom_entreprise }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="gel-btn gel-btn-secondary" onclick="toggleNewJournalForm()">Annuler</button>
                <button type="submit" class="gel-btn gel-btn-primary"><i class="bi bi-check-lg me-1"></i> Enregistrer le journal</button>
            </div>
        </form>
    </div>
</div>

{{-- Liste des Journaux --}}
<div class="gel-card">
    <div class="gel-card-body" style="padding: 0;">
        @if($journaux->count() > 0)
            <div style="overflow-x: auto;">
                <table class="gel-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; text-align: left;">
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; width: 100px;">Code</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Libellé</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; width: 140px;">Type</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; width: 200px;">Client</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; width: 100px;">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journaux as $journal)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                                <td style="padding: 14px 20px; font-weight: 700; color: #0f172a;">
                                    <span style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 13px;">{{ $journal->code }}</span>
                                </td>
                                <td style="padding: 14px 20px; font-size: 14px; font-weight: 500; color: #334155;">{{ $journal->libelle }}</td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #64748b; text-transform: capitalize;">
                                    <i class="bi bi-tag me-1" style="color: #94a3b8;"></i>{{ str_replace('_', ' ', $journal->type ?? '—') }}
                                </td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #475569;">
                                    @if($journal->client)
                                        <span class="badge" style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 12px; font-weight: 500;"><i class="bi bi-building me-1"></i>{{ $journal->client->nom_entreprise }}</span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Tous les clients</span>
                                    @endif
                                </td>
                                <td style="padding: 14px 20px;">
                                    @if($journal->actif)
                                        <span class="gel-badge gel-badge-success" style="padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">Actif</span>
                                    @else
                                        <span class="gel-badge gel-badge-danger" style="padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">Inactif</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="gel-empty" style="text-align: center; padding: 60px 20px;">
                <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #94a3b8; font-size: 28px;">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
                <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">Aucun journal comptable</h3>
                <p style="color: #64748b; font-size: 14px; max-width: 400px; margin: 0 auto 20px auto;">Vous n'avez pas encore créé de journal. Créez des journaux pour organiser la saisie (Ventes, Achats, Banque, etc.).</p>
                <button class="gel-btn gel-btn-primary" onclick="toggleNewJournalForm()">
                    <i class="bi bi-plus-lg me-1"></i> Créer votre premier journal
                </button>
            </div>
        @endif
    </div>
</div>

<script>
function toggleNewJournalForm() {
    var card = document.getElementById('newJournalCard');
    if (card.style.display === 'none' || card.style.display === '') {
        card.style.display = 'block';
    } else {
        card.style.display = 'none';
    }
}
</script>
@endsection
