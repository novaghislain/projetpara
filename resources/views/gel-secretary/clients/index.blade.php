@extends('layouts.gel-secretary')
@section('title', 'Entreprises clientes — Secrétariat')

@section('content')
<div class="sec-page-header">
  <div>
    <div class="sec-page-title">Entreprises clientes</div>
    <div class="sec-page-sub">Portefeuille d'entreprises gérées par le secrétariat</div>
  </div>
</div>

{{-- ─── S2 : Arborescence — Chiffres clés du portefeuille ───────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:18px;">
  <div class="sec-card" style="padding:16px;display:flex;align-items:center;gap:14px;">
    <div style="width:44px;height:44px;border-radius:10px;background:rgba(14,165,233,.12);color:#0EA5E9;display:flex;align-items:center;justify-content:center;"><i class="fas fa-building"></i></div>
    <div><div style="font-size:22px;font-weight:700;">{{ $clients->count() }}</div><div style="font-size:11px;color:var(--sec-text-muted);">Entreprises</div></div>
  </div>
  <div class="sec-card" style="padding:16px;display:flex;align-items:center;gap:14px;">
    <div style="width:44px;height:44px;border-radius:10px;background:rgba(16,185,129,.1);color:#10B981;display:flex;align-items:center;justify-content:center;"><i class="fas fa-folder-open"></i></div>
    <div><div style="font-size:22px;font-weight:700;">{{ $clients->sum(fn($c) => $c->stats['documents_count'] ?? 0) }}</div><div style="font-size:11px;color:var(--sec-text-muted);">Documents</div></div>
  </div>
  <div class="sec-card" style="padding:16px;display:flex;align-items:center;gap:14px;">
    <div style="width:44px;height:44px;border-radius:10px;background:rgba(139,92,246,.1);color:#8B5CF6;display:flex;align-items:center;justify-content:center;"><i class="fas fa-tasks"></i></div>
    <div><div style="font-size:22px;font-weight:700;">{{ $clients->sum(fn($c) => $c->stats['tasks_count'] ?? 0) }}</div><div style="font-size:11px;color:var(--sec-text-muted);">Tâches</div></div>
  </div>
  <div class="sec-card" style="padding:16px;display:flex;align-items:center;gap:14px;">
    <div style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,.1);color:#F59E0B;display:flex;align-items:center;justify-content:center;"><i class="fas fa-inbox"></i></div>
    <div><div style="font-size:22px;font-weight:700;">{{ $clients->sum(fn($c) => $c->stats['courriers_count'] ?? 0) }}</div><div style="font-size:11px;color:var(--sec-text-muted);">Courriers</div></div>
  </div>
</div>

<div class="sec-card">
  <div class="sec-card-header">
    {{-- S2 : Arborescence — racine "ENTREPRISES" → liste des dossiers clients --}}
    <div class="sec-card-title" style="display:flex;align-items:center;gap:10px;">
      <i class="fas fa-sitemap" style="color:var(--sec-primary);"></i>
      <span style="font-weight:700;">ENTREPRISES</span>
      <span style="color:var(--sec-text-muted);font-weight:500;">/</span>
      <span style="color:var(--sec-text-muted);">Portefeuille ({{ $clients->count() }})</span>
    </div>
    <input type="text" class="sec-form-control" style="max-width:240px;" placeholder="🔍 Rechercher..." oninput="filterClients(this.value)">
  </div>
  <div style="padding:0;">
    <table class="sec-table" id="clientsTable">
      <thead>
        <tr>
          <th>Entreprise</th>
          <th>Email</th>
          <th>Téléphone</th>
          <th>Ville</th>
          <th>Statut</th>
          <th style="text-align:center;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($clients as $c)
        <tr class="client-row" data-name="{{ strtolower($c->nom_entreprise) }}">
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:34px;height:34px;border-radius:50%;background:var(--sec-primary);color:white;
                          display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">
                {{ strtoupper(substr($c->nom_entreprise ?? 'E', 0, 2)) }}
              </div>
              <div>
                <div style="font-weight:600;font-size:13px;">{{ $c->nom_entreprise }}</div>
                @if($c->ifu)<div style="font-size:11px;color:var(--sec-text-muted);">IFU : {{ $c->ifu }}</div>@endif
              </div>
            </div>
          </td>
          <td style="color:var(--sec-text-muted);">{{ $c->email ?? '—' }}</td>
          <td>{{ $c->telephone ?? '—' }}</td>
          <td>{{ $c->ville ?? '—' }}</td>
          <td>
            <span class="sec-badge {{ ($c->status ?? 'actif') === 'actif' ? 'sec-badge-success' : 'sec-badge-muted' }}" style="margin-bottom: 6px; display:inline-block;">
              {{ ucfirst($c->status ?? 'actif') }}
            </span>
            @if(isset($c->stats))
              <div style="font-size:11px; color:var(--sec-text-muted);">
                <ul style="list-style:none; padding:0; margin:0;">
                  <li><i class="fas fa-folder-open" style="width:14px; text-align:center;"></i> {{ $c->stats['documents_count'] }} doc(s)</li>
                  <li><i class="fas fa-tasks" style="width:14px; text-align:center;"></i> {{ $c->stats['tasks_count'] }} tâche(s)</li>
                  <li><i class="fas fa-envelope" style="width:14px; text-align:center;"></i> {{ $c->stats['courriers_count'] }} courrier(s)</li>
                  <li style="margin-top:4px; font-weight:600;"><i class="fas fa-clock" style="width:14px; text-align:center;"></i> Activité : {{ str_replace('il y a', '', $c->stats['last_activity']) }}</li>
                </ul>
              </div>
            @endif
          </td>
          <td style="text-align:center;">
            <a href="{{ route('gel-secretary.clients.show', $c->id) }}"
               class="sec-btn sec-btn-secondary sec-btn-sm">
              <i class="fas fa-eye"></i> Voir
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;padding:40px;color:var(--sec-text-muted);">
            <i class="fas fa-building" style="font-size:36px;display:block;margin-bottom:10px;opacity:.3;"></i>
            Aucune entreprise enregistrée
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script>
function filterClients(q) {
  q = q.toLowerCase();
  document.querySelectorAll('#clientsTable tbody tr.client-row').forEach(function(row) {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>
@endpush
@endsection
