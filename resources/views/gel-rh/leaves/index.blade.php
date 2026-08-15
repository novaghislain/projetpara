@extends('layouts.gel-rh')
@section('title', 'Demandes de Congés')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">Congés & Absences</h1>
  </div>
</div>

<div class="sec-card">
  <div class="sec-card-body p-0">
    <table class="sec-table">
      <thead>
        <tr>
          <th>Salarié</th>
          <th>Type</th>
          <th>Dates</th>
          <th>Motif</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($leaves as $leave)
        <tr>
          <td>{{ $leave->nom }} {{ $leave->prenom }}</td>
          <td>{{ ucfirst($leave->type_conge) }}</td>
          <td>{{ \Carbon\Carbon::parse($leave->date_debut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($leave->date_fin)->format('d/m/Y') }}</td>
          <td>{{ $leave->motif ?: '-' }}</td>
          <td>
            @if($leave->statut == 'en_attente')
              <span class="sec-badge sec-badge-warning">En attente</span>
            @elseif($leave->statut == 'approuve')
              <span class="sec-badge sec-badge-success">Approuvé</span>
            @else
              <span class="sec-badge" style="background:#FEF2F2; color:var(--gel-danger)">Rejeté</span>
            @endif
          </td>
          <td>
            @if($leave->statut == 'en_attente')
            <div style="display:flex;gap:8px;">
              <form action="{{ route('gel-rh.leaves.approve', $leave->id) }}" method="POST">
                @csrf
                <button class="sec-btn sec-btn-primary sec-btn-sm"><i class="fas fa-check"></i></button>
              </form>
              <form action="{{ route('gel-rh.leaves.reject', $leave->id) }}" method="POST">
                @csrf
                <button class="sec-btn sec-btn-sm" style="background:#FEF2F2; color:var(--gel-danger); border:1px solid #FECACA;"><i class="fas fa-times"></i></button>
              </form>
            </div>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($leaves->isEmpty())
      <div class="gel-empty">
        <i class="fas fa-umbrella-beach"></i>
        <h3>Aucune demande</h3>
        <p>Il n'y a aucune demande de congés pour le moment.</p>
      </div>
    @endif
  </div>
</div>
@endsection
