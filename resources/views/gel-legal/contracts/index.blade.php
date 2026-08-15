@extends('layouts.gel-legal')
@section('title', 'Contrats')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">Contrats & Actes</h1>
  </div>
  <button class="sec-btn sec-btn-primary" onclick="document.getElementById('addContractModal').style.display='flex'">
    <i class="fas fa-plus"></i> Nouveau Contrat
  </button>
</div>

<div class="sec-card">
  <div class="sec-card-body p-0">
    <table class="sec-table">
      <thead>
        <tr>
          <th>Intitulé</th>
          <th>Type</th>
          <th>Date de signature</th>
          <th>Expiration</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        @foreach($contracts as $c)
        <tr>
          <td style="font-weight:600;">{{ $c->intitule }}</td>
          <td>{{ ucfirst($c->type) }}</td>
          <td>{{ $c->date_signature ? \Carbon\Carbon::parse($c->date_signature)->format('d/m/Y') : '-' }}</td>
          <td>{{ $c->date_expiration ? \Carbon\Carbon::parse($c->date_expiration)->format('d/m/Y') : '-' }}</td>
          <td><span class="sec-badge sec-badge-success">Actif</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($contracts->isEmpty())
      <div class="gel-empty">
        <i class="fas fa-file-contract"></i>
        <h3>Aucun contrat</h3>
        <p>Les contrats ajoutés s'afficheront ici.</p>
      </div>
    @endif
  </div>
</div>

{{-- Modale simple d'ajout --}}
<div id="addContractModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; width:500px; border-radius:8px; padding:24px;">
        <h3 style="margin-top:0;">Ajouter un contrat</h3>
        <form action="{{ route('gel-legal.contracts.store') }}" method="POST">
            @csrf
            <div class="sec-form-group">
                <label>Intitulé du contrat</label>
                <input type="text" name="intitule" class="sec-form-control" required>
            </div>
            <div class="sec-form-group">
                <label>Type</label>
                <select name="type" class="sec-form-control">
                    <option value="CDI">CDI</option>
                    <option value="CDD">CDD</option>
                    <option value="Prestation">Prestation de service</option>
                    <option value="Bail">Bail commercial</option>
                </select>
            </div>
            <div class="sec-form-group">
                <label>Date d'expiration (Optionnel)</label>
                <input type="date" name="date_expiration" class="sec-form-control">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="sec-btn sec-btn-secondary" onclick="document.getElementById('addContractModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
