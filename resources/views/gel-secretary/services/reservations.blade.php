@extends('layouts.gel-secretary')
@section('title', 'Réservations - Secrétariat')

@section('content')
<div class="sec-page-header">
  <div>
    <h1 class="sec-page-title">
      <i class="fas fa-calendar-check" style="color:#F59E0B; margin-right:8px;"></i> Réservations
    </h1>
    <p class="sec-page-sub">
      @if($activeClient)
        Gestion des salles, véhicules et équipements pour : <strong>{{ $activeClient->nom_entreprise }}</strong>
      @else
        Veuillez sélectionner une entreprise active dans la barre supérieure.
      @endif
    </p>
  </div>
  @if($activeClient)
  <div style="display:flex; gap:10px;">
    <button class="sec-btn sec-btn-primary" onclick="document.getElementById('reservationModal').style.display='flex'">
      <i class="fas fa-plus"></i> Nouvelle réservation
    </button>
  </div>
  @endif
</div>

@if($activeClient)
<!-- Système d'onglets simples -->
<div style="margin-bottom:20px; border-bottom:1px solid var(--sec-border); display:flex; gap:20px;">
    <button class="tab-btn active" onclick="switchTab('tab-upcoming', this)" style="background:none; border:none; padding:10px 4px; font-size:14px; font-weight:600; color:var(--sec-primary); border-bottom:2px solid var(--sec-primary); cursor:pointer;">
        Réservations à venir ({{ $upcomingReservations->count() }})
    </button>
    <button class="tab-btn" onclick="switchTab('tab-past', this)" style="background:none; border:none; padding:10px 4px; font-size:14px; font-weight:600; color:var(--sec-text-muted); cursor:pointer;">
        Historique ({{ $pastReservations->count() }})
    </button>
</div>

<!-- ONGLET A VENIR -->
<div id="tab-upcoming" class="tab-content" style="display:block;">
    <div class="sec-card">
        <div class="sec-card-header">
            <div class="sec-card-title">Réservations en cours et futures</div>
        </div>
        <div class="sec-card-body" style="padding:0;">
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Ressource</th>
                        <th>Type</th>
                        <th>Date & Heure</th>
                        <th>Motif</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upcomingReservations as $res)
                    <tr>
                        <td><strong>{{ $res->resource_name }}</strong></td>
                        <td>
                            @if($res->type === 'salle') <span class="sec-badge sec-badge-info"><i class="fas fa-door-open"></i> Salle</span>
                            @elseif($res->type === 'vehicule') <span class="sec-badge sec-badge-warning"><i class="fas fa-car"></i> Véhicule</span>
                            @else <span class="sec-badge sec-badge-muted"><i class="fas fa-laptop"></i> Équipement</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:600; color:var(--sec-text);">{{ $res->start_time->format('d/m/Y') }}</div>
                            <div style="font-size:11px; color:var(--sec-text-muted);">{{ $res->start_time->format('H:i') }} - {{ $res->end_time->format('H:i') }}</div>
                        </td>
                        <td>{{ $res->purpose ?? '—' }}</td>
                        <td>
                            @if($res->status === 'pending')
                                <span class="sec-badge sec-badge-warning">En attente</span>
                            @elseif($res->status === 'confirmed')
                                <span class="sec-badge sec-badge-success">Confirmé</span>
                            @elseif($res->status === 'cancelled')
                                <span class="sec-badge sec-badge-danger">Annulé</span>
                            @else
                                <span class="sec-badge sec-badge-muted">{{ ucfirst($res->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                @if($res->status === 'pending')
                                <form action="{{ route('gel-secretary.services.reservations.update', $res->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="sec-btn" style="padding:4px 8px; font-size:11px; background:#DCFCE7; color:#16A34A; border:1px solid #BBF7D0;" title="Confirmer"><i class="fas fa-check"></i></button>
                                </form>
                                <form action="{{ route('gel-secretary.services.reservations.update', $res->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="sec-btn" style="padding:4px 8px; font-size:11px; background:#FEE2E2; color:#DC2626; border:1px solid #FECACA;" title="Annuler"><i class="fas fa-times"></i></button>
                                </form>
                                @endif
                                <form action="{{ route('gel-secretary.services.reservations.destroy', $res->id) }}" method="POST" onsubmit="return confirm('Supprimer définitivement cette réservation ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sec-btn" style="padding:4px 8px; font-size:11px; background:#F1F5F9; color:var(--sec-text);" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px; color:var(--sec-text-muted);">
                            <i class="fas fa-calendar-times" style="font-size:24px; margin-bottom:10px; display:block;"></i>
                            Aucune réservation à venir.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ONGLET PASSEES -->
<div id="tab-past" class="tab-content" style="display:none;">
    <div class="sec-card">
        <div class="sec-card-header">
            <div class="sec-card-title">Historique des réservations</div>
        </div>
        <div class="sec-card-body" style="padding:0;">
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Ressource</th>
                        <th>Type</th>
                        <th>Date & Heure</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pastReservations as $res)
                    <tr>
                        <td><strong>{{ $res->resource_name }}</strong></td>
                        <td>{{ ucfirst($res->type) }}</td>
                        <td>{{ $res->start_time->format('d/m/Y H:i') }} - {{ $res->end_time->format('H:i') }}</td>
                        <td>
                            @if($res->status === 'confirmed')
                                <span class="sec-badge sec-badge-success">Confirmé</span>
                            @elseif($res->status === 'cancelled')
                                <span class="sec-badge sec-badge-danger">Annulé</span>
                            @else
                                <span class="sec-badge sec-badge-muted">{{ ucfirst($res->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:30px; color:var(--sec-text-muted);">
                            Aucun historique.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nouvelle Réservation -->
<div id="reservationModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:12px; width:100%; max-width:500px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom:20px; font-size:18px;"><i class="fas fa-calendar-plus text-primary"></i> Saisir une réservation</h3>
        <form action="{{ route('gel-secretary.services.reservations.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Type de ressource *</label>
                <select name="type" class="form-select" required>
                    <option value="salle">Salle de réunion</option>
                    <option value="vehicule">Véhicule</option>
                    <option value="equipement">Équipement (Projecteur, etc.)</option>
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Nom de la ressource *</label>
                <input type="text" name="resource_name" class="form-control" placeholder="Ex: Salle A, Peugeot 208..." required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Début *</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Fin *</label>
                    <input type="datetime-local" name="end_time" class="form-control" required>
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:6px;">Motif / Commentaire</label>
                <textarea name="purpose" class="form-control" rows="2"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                <button type="button" class="sec-btn" style="background:#F1F5F9; color:#475569;" onclick="document.getElementById('reservationModal').style.display='none'">Annuler</button>
                <button type="submit" class="sec-btn sec-btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tabId, btnElement) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.color = 'var(--sec-text-muted)';
        btn.style.borderBottom = 'none';
        btn.classList.remove('active');
    });
    
    document.getElementById(tabId).style.display = 'block';
    btnElement.style.color = 'var(--sec-primary)';
    btnElement.style.borderBottom = '2px solid var(--sec-primary)';
    btnElement.classList.add('active');
}
</script>
@endif

@endsection
