@extends('layouts.gel-secretary')

@section('title', 'Score de Conformité GEL® — ' . $client->nom_entreprise)

@push('styles')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
  * { font-family: 'Inter', sans-serif; }

  :root {
    --sec-primary: #0d9488;
    --sec-border:  #e2e8f0;
    --sec-bg:      #f8fafc;
    --sec-text:    #1e293b;
    --sec-muted:   #64748b;
  }

  .animate-fade { animation: fadeIn 0.4s ease both; }
  .delay-1      { animation-delay: 0.08s; }
  .delay-2      { animation-delay: 0.16s; }
  .delay-3      { animation-delay: 0.24s; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }

  /* Score ring */
  .score-ring { transform: rotate(-90deg); }
  .score-ring-track { fill:none; stroke:#e2e8f0; stroke-width:10; }
  .score-ring-fill  { fill:none; stroke-width:10; stroke-linecap:round; transition:stroke-dashoffset 1.2s ease; }

  /* Obligation cards */
  .obligation-card {
    background:white;
    border:1px solid var(--sec-border);
    border-radius:14px;
    padding:18px 20px;
    display:flex;
    align-items:flex-start;
    gap:16px;
    transition:all 0.2s;
  }
  .obligation-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.06); border-color:#cbd5e1; }

  /* Modal overlay */
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(2px); }
  .modal-overlay.open { display:flex; }
  .modal-box { background:white; border-radius:20px; padding:28px; width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
</style>
@endpush

@section('content')

{{-- En-tête --}}
<div class="animate-fade" style="background:white; border:1px solid var(--sec-border); border-radius:16px; padding:24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 6px -1px rgba(0,0,0,0.04); flex-wrap:wrap; gap:16px;">
  <div>
    <h1 style="font-size:22px; font-weight:700; color:var(--sec-text); margin-bottom:6px; letter-spacing:-0.5px;">
      Score de Conformité GEL® — {{ $client->nom_entreprise }}
    </h1>
    <p style="font-size:14px; color:var(--sec-muted); margin:0;">
      Tableau de bord des obligations légales, fiscales et sociales. Mis à jour en temps réel.
    </p>
  </div>
  <div style="display:flex; gap:12px; align-items:center;">
    <a href="{{ route('gel-secretary.conformite.passeport', $client->id) }}" style="background:var(--sec-primary); color:white; border-radius:10px; padding:10px 16px; text-decoration:none; font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; transition:all 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
      <i class="fas fa-file-pdf"></i> Générer Passeport
    </a>
    <a href="{{ route('gel-secretary.clients.show', $client->id) }}" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; padding:10px 16px; text-decoration:none; font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; transition:all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>
</div>

{{-- Score global --}}
<div class="animate-fade delay-1" style="display:grid; grid-template-columns:auto 1fr; gap:24px; background:linear-gradient(135deg, var(--sec-primary), #0f766e); border-radius:20px; padding:32px; margin-bottom:24px; align-items:center; flex-wrap:wrap;">

  {{-- Ring SVG --}}
  <div style="position:relative; width:140px; height:140px; flex-shrink:0;">
    <svg viewBox="0 0 120 120" width="140" height="140" class="score-ring">
      <circle class="score-ring-track" cx="60" cy="60" r="50"/>
      <circle class="score-ring-fill" cx="60" cy="60" r="50"
        id="scoreArc"
        stroke="white"
        stroke-dasharray="314"
        stroke-dashoffset="{{ 314 - (314 * $score / 100) }}"/>
    </svg>
    <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
      <span style="font-size:36px; font-weight:800; color:white; line-height:1;" id="scoreDisplay">{{ $score }}</span>
      <span style="font-size:13px; color:rgba(255,255,255,0.8); font-weight:500;">/ 100</span>
    </div>
  </div>

  <div>
    <div style="font-size:28px; font-weight:800; color:white; margin-bottom:8px;">
      @if($score >= 80) 🟢 Conforme
      @elseif($score >= 50) 🟡 Partiellement conforme
      @else 🔴 Non conforme
      @endif
    </div>
    <div style="color:rgba(255,255,255,0.8); font-size:14px; line-height:1.6; margin-bottom:20px;">
      {{ $items->flatten()->where('statut','ok')->count() }} obligations validées sur {{ $items->flatten()->count() }}
      @if($alertes->count())
      · <span style="color:#fcd34d; font-weight:600;">{{ $alertes->count() }} expiration(s) à venir</span>
      @endif
    </div>
    {{-- Barre de progression --}}
    <div style="background:rgba(255,255,255,0.2); border-radius:999px; height:10px; width:100%; max-width:400px;">
      <div style="background:white; border-radius:999px; height:10px; width:{{ $score }}%; transition:width 1s ease;"></div>
    </div>
  </div>
</div>

{{-- Alertes d'expiration --}}
@if($alertes->count())
<div class="animate-fade delay-2" style="background:#fffbeb; border:1px solid #fde68a; border-radius:14px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:flex-start; gap:14px;">
  <div style="background:#f59e0b; color:white; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
    <i class="fas fa-triangle-exclamation" style="font-size:16px;"></i>
  </div>
  <div>
    <div style="font-weight:700; color:#92400e; margin-bottom:6px;">Expirations à venir dans les 30 jours</div>
    <div style="display:flex; flex-wrap:wrap; gap:8px;">
      @foreach($alertes as $a)
        <span style="background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:4px 12px; font-size:12px; font-weight:600; color:#b45309;">
          {{ $a->titre }} — {{ $a->date_expiration->format('d/m/Y') }}
        </span>
      @endforeach
    </div>
  </div>
</div>
@endif

{{-- Obligations par catégorie --}}
@foreach($items as $categorie => $obligations)
<div class="animate-fade delay-{{ ($loop->index % 3) + 1 }}" style="margin-bottom:28px;">
  <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
    <div style="background:var(--sec-primary); color:white; border-radius:8px; padding:4px 12px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
      {{ ucfirst($categorie) }}
    </div>
    <div style="height:1px; flex:1; background:var(--sec-border);"></div>
    <span style="font-size:12px; color:var(--sec-muted);">
      {{ $obligations->where('statut','ok')->count() }}/{{ $obligations->count() }} OK
    </span>
  </div>

  <div style="display:flex; flex-direction:column; gap:10px;">
    @foreach($obligations as $item)
    <div class="obligation-card">
      {{-- Icône statut --}}
      <div style="width:44px; height:44px; border-radius:12px; background:{{ $item->statut === 'ok' ? '#f0fdf4' : ($item->statut === 'attention' ? '#fffbeb' : '#fff1f2') }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fas {{ $item->icon }}" style="font-size:18px; color:{{ $item->color }};"></i>
      </div>

      {{-- Infos --}}
      <div style="flex:1; min-width:0;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px; flex-wrap:wrap;">
          <span style="font-weight:700; font-size:14px; color:var(--sec-text);">{{ $item->titre }}</span>
          <span style="background:{{ $item->statut === 'ok' ? '#dcfce7' : ($item->statut === 'attention' ? '#fef9c3' : '#fee2e2') }}; color:{{ $item->color }}; border-radius:6px; padding:2px 10px; font-size:11px; font-weight:700; text-transform:uppercase;">
            {{ $item->statut === 'ok' ? 'OK' : ($item->statut === 'attention' ? 'Attention' : ($item->statut === 'expire' ? 'Expiré' : 'Non conforme')) }}
          </span>
          @if($item->date_expiration)
            <span style="font-size:11px; color:{{ $item->date_expiration->isPast() ? '#dc2626' : ($item->date_expiration->diffInDays() <= 30 ? '#d97706' : '#64748b') }}; display:flex; align-items:center; gap:4px;">
              <i class="far fa-calendar"></i> exp. {{ $item->date_expiration->format('d/m/Y') }}
            </span>
          @endif
        </div>
        @if($item->notes)
          <p style="font-size:12px; color:var(--sec-muted); margin:0 0 8px 0;">{{ $item->notes }}</p>
        @endif
        {{-- Actions du plan --}}
        @if($item->actions->count())
          <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:6px;">
            @foreach($item->actions->where('statut','!=','termine') as $action)
              <span style="background:#f1f5f9; border:1px solid #e2e8f0; border-radius:6px; padding:3px 10px; font-size:11px; color:#475569; display:flex; align-items:center; gap:6px;">
                <i class="fas fa-arrow-right" style="font-size:9px;"></i> {{ $action->titre }}
                @if($action->echeance)
                  · {{ $action->echeance->format('d/m') }}
                @endif
                <form method="POST" action="{{ route('gel-secretary.conformite.action.complete', $action->id) }}" style="margin:0; display:inline;">
                  @csrf
                  <button type="submit" style="background:none; border:none; cursor:pointer; color:#10b981; padding:0; font-size:11px;" title="Marquer terminée">✓</button>
                </form>
              </span>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Bouton mettre à jour --}}
      <button onclick="openModal({{ $item->id }}, '{{ addslashes($item->titre) }}', '{{ $item->statut }}', '{{ $item->date_expiration?->format('Y-m-d') }}', '{{ $item->date_validation?->format('Y-m-d') }}', `{{ addslashes($item->notes ?? '') }}`)"
        style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:8px 14px; font-size:13px; font-weight:600; color:#475569; cursor:pointer; transition:all 0.2s; white-space:nowrap; display:flex; align-items:center; gap:6px;"
        onmouseover="this.style.background='#e2e8f0'; this.style.color='var(--sec-primary)'"
        onmouseout="this.style.background='#f8fafc'; this.style.color='#475569'">
        <i class="fas fa-pen"></i> Mettre à jour
      </button>
    </div>
    @endforeach
  </div>
</div>
@endforeach

{{-- Modal de mise à jour --}}
<div class="modal-overlay" id="updateModal" onclick="if(event.target===this) closeModal()">
  <div class="modal-box">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h3 style="font-size:16px; font-weight:700; color:var(--sec-text); margin:0;" id="modalTitle">Mettre à jour</h3>
      <button onclick="closeModal()" style="background:#f1f5f9; border:none; border-radius:8px; width:32px; height:32px; cursor:pointer; color:#64748b; display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <form method="POST" id="updateForm" enctype="multipart/form-data">
      @csrf @method('PUT')

      <div style="margin-bottom:16px;">
        <label style="font-size:13px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:6px;">Statut *</label>
        <select name="statut" id="modalStatut" required style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none;" onfocus="this.style.borderColor='var(--sec-primary)'" onblur="this.style.borderColor='#e2e8f0'">
          <option value="ok">✅ OK — Conforme</option>
          <option value="attention">⚠️ Attention — À surveiller</option>
          <option value="ko">❌ Non conforme</option>
          <option value="expire">🕐 Expiré</option>
        </select>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
        <div>
          <label style="font-size:13px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:6px;">Date de validation</label>
          <input type="date" name="date_validation" id="modalDateValidation" style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:13px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:6px;">Date d'expiration</label>
          <input type="date" name="date_expiration" id="modalDateExpiration" style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;">
        </div>
      </div>

      <div style="margin-bottom:16px;">
        <label style="font-size:13px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:6px;">Pièce justificative</label>
        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
      </div>

      <div style="margin-bottom:20px;">
        <label style="font-size:13px; font-weight:600; color:var(--sec-text); display:block; margin-bottom:6px;">Notes</label>
        <textarea name="notes" id="modalNotes" rows="3" placeholder="Observations..." style="width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:10px 14px; font-size:14px; outline:none; resize:vertical; box-sizing:border-box;" onfocus="this.style.borderColor='var(--sec-primary)'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
      </div>

      {{-- Plan d'action rapide --}}
      <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:20px;">
        <div style="font-size:13px; font-weight:700; color:var(--sec-text); margin-bottom:12px;">
          <i class="fas fa-list-check" style="color:var(--sec-primary); margin-right:6px;"></i> Ajouter une action corrective
        </div>
        <input type="text" name="action_titre" placeholder="Ex: Renouveler le RCCM auprès du CENA" style="width:100%; border:1.5px solid #e2e8f0; border-radius:8px; padding:8px 12px; font-size:13px; margin-bottom:8px; box-sizing:border-box; outline:none;">
        <input type="date" name="action_echeance" style="border:1.5px solid #e2e8f0; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box; width:100%;">
      </div>

      <div style="display:flex; gap:10px;">
        <button type="submit" style="flex:1; background:var(--sec-primary); color:white; border:none; border-radius:12px; padding:12px; font-size:14px; font-weight:700; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
          <i class="fas fa-save" style="margin-right:8px;"></i> Enregistrer
        </button>
        <button type="button" onclick="closeModal()" style="background:#f1f5f9; color:#475569; border:none; border-radius:12px; padding:12px 20px; font-size:14px; font-weight:600; cursor:pointer;">
          Annuler
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
  function openModal(id, titre, statut, dateExp, dateVal, notes) {
    document.getElementById('modalTitle').textContent = 'Mettre à jour — ' + titre;
    document.getElementById('updateForm').action = "{{ url('gel-secretary/conformite') }}/" + id;
    document.getElementById('modalStatut').value = statut;
    document.getElementById('modalDateExpiration').value = dateExp || '';
    document.getElementById('modalDateValidation').value = dateVal || '';
    document.getElementById('modalNotes').value = notes || '';
    document.getElementById('updateModal').classList.add('open');
  }
  function closeModal() {
    document.getElementById('updateModal').classList.remove('open');
  }
  document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });

  // Animation du score ring au chargement
  document.addEventListener('DOMContentLoaded', function() {
    const arc = document.getElementById('scoreArc');
    const score = {{ $score }};
    const target = 314 - (314 * score / 100);
    arc.style.strokeDashoffset = 314;
    setTimeout(() => { arc.style.strokeDashoffset = target; }, 200);
  });
</script>
@endpush
