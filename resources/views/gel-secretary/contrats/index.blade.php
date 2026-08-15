@extends('layouts.gel-secretary')
@section('title', 'Gestion des Contrats')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title"><i class="fas fa-file-contract" style="color:var(--sec-primary); margin-right:8px;"></i>Contrats</h1>
    <p class="sec-page-sub">Gestion et suivi des contrats clients.</p>
  </div>
  <div>
    <a href="{{ route('gel-secretary.contrats.create', ['client_id' => request('client_id')]) }}" class="sec-btn sec-btn-primary">
      <i class="fas fa-plus"></i> Nouveau Contrat
    </a>
  </div>
</div>

<div class="sec-card animate-fade delay-2">
  <div class="sec-card-body p-0">
    <div class="table-responsive">
      <table class="sec-table">
        <thead>
          <tr>
            <th>Titre</th>
            <th>Type</th>
            <th>Partie Adverse</th>
            <th>Client</th>
            <th>Date Signature</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($contrats as $contrat)
            <tr>
              <td><strong>{{ $contrat->titre }}</strong></td>
              <td>{{ $contrat->type_contrat }}</td>
              <td>{{ $contrat->partie_adverse }}</td>
              <td>{{ $contrat->client->entreprise_nom_commercial ?? 'N/A' }}</td>
              <td>{{ $contrat->date_signature ? $contrat->date_signature->format('d/m/Y') : '-' }}</td>
              <td>
                <span class="badge bg-{{ $contrat->statut == 'actif' ? 'success' : ($contrat->statut == 'brouillon' ? 'secondary' : 'warning') }}">
                  {{ ucfirst($contrat->statut) }}
                </span>
              </td>
              <td class="text-end">
                <a href="{{ route('gel-secretary.contrats.show', $contrat->id) }}" class="btn btn-sm btn-outline-secondary" title="Voir">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('gel-secretary.contrats.edit', $contrat->id) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                  <i class="fas fa-edit"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4" style="color: var(--sec-text-muted);">
                <i class="fas fa-file-contract fa-2x mb-2" style="color: #cbd5e1;"></i>
                <p class="mb-0">Aucun contrat trouvé.</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($contrats->hasPages())
    <div class="card-footer border-0" style="background:#fff; border-top:1px solid #e2e8f0 !important; padding:12px 24px;">
      {{ $contrats->appends(request()->query())->links() }}
    </div>
  @endif
</div>
@endsection
