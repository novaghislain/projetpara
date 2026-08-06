@extends('layouts.gel-secretary')
@section('title', 'Coffre-fort numérique — Secrétariat')

@section('content')
<style>
  @keyframes fadeUp { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
  .animate-fade { animation:fadeUp .3s ease-out; }
  .sf-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; margin-top:16px; }
  .sf-card { background:#fff; border:1px solid var(--sec-border); border-radius:14px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.04); transition:.2s; }
  .sf-card:hover { box-shadow:0 6px 20px rgba(0,0,0,.08); transform:translateY(-2px); }
  .sf-head { padding:14px 16px; display:flex; gap:12px; align-items:flex-start; border-bottom:1px solid var(--sec-border); }
  .sf-check { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .sf-body { padding:12px 16px; }
  .sf-tag { display:inline-block; padding:3px 8px; border-radius:10px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
  .meta-row { display:flex; justify-content:space-between; font-size:11px; color:var(--sec-text-muted); padding:4px 0; border-bottom:1px solid #F3F4F6; }
  .meta-row:last-child { border-bottom:none; }
  .lock-group { display:flex; gap:12px; align-items:center; padding:12px 16px; border-radius:12px; background:#FFF7ED; border:1px solid #FED7AA; color:#92400E; font-size:13px; margin-top:16px; }
  .hist-row { display:flex; gap:10px; align-items:center; padding:8px 0; border-bottom:1px solid #F3F4F6; font-size:12px; }
  .tab-section-header { padding:10px 16px; background:#F8FAFC; border-bottom:1px solid var(--sec-border); font-size:11px; font-weight:700; color:var(--sec-text-muted); text-transform:uppercase; }
</style>

<div class="sec-page-header">
  <div>
    <div class="sec-page-title"><i class="fas fa-shield-alt" style="color:#B45309;margin-right:8px;"></i>Coffre-fort numérique</div>
    <div class="sec-page-sub">Documents officiels sensibles — accès renforcé, traçabilité stricte, téléchargement sécurisé</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;">
    <button class="sec-btn sec-btn-secondary" type="button" onclick="toggleAll()"><i class="fas fa-check-double"></i> Tout sélectionner</button>
    <button class="sec-btn sec-btn-primary" id="bulkDownloadBtn" type="button" onclick="bulkDownload()" disabled>
      <i class="fas fa-file-archive"></i> Télécharger (.zip)
    </button>
  </div>
</div>

<form id="bulkForm" method="POST" action="{{ route('gel-secretary.safebox.bulk-download') }}">
  @csrf
  <input type="hidden" name="ids" id="bulkIds" value="[]">
</form>

@if($activeClient)
<div style="margin-bottom:14px;">
  <span class="sec-badge" style="background:#FFF7ED;color:#B45309;border:1px solid #FED7AA;padding:6px 12px;">
    <i class="fas fa-lock"></i> {{ $perimetre }}
  </span>
</div>
@endif

<div class="lock-group animate-fade">
  <i class="fas fa-shield-alt" style="font-size:18px;color:#B45309;"></i>
  <div>
    <strong>Accès contrôlé.</strong>
    Chaque consultation et téléchargement est enregistré dans l'historique de sécurité.
    Le coffre ne contient que des documents à caractère confidentiel ou officiel.
  </div>
</div>

{{-- ─── Filtres ─────────────────────────────────────────────── --}}
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
  <a href="{{ request()->url() }}" class="sec-btn sec-btn-sm {{ request('category') ? 'sec-btn-secondary' : 'sec-btn-primary' }}">Tous</a>
  @foreach($groupes ?? [] as $label => $cats)
    <a href="{{ request()->fullUrlWithQuery(['category' => implode(',', $cats)]) }}" class="sec-btn sec-btn-secondary sec-btn-sm">{{ $label }}</a>
  @endforeach
</div>

{{-- ─── Liste documents du coffre ─────────────────────────────────────── --}}
<div class="sf-grid animate-fade">
  @forelse($documents as $doc)
    @php
      $estConfidentiel = ($doc->privacy_level ?? '') === 'confidentiel';
      $color = $estConfidentiel ? '#B45309' : '#0D9488';
    @endphp
    <div class="sf-card">
      <div class="sf-head">
        <div class="sf-check" style="background:{{ $color }}1A;">
          <i class="fas {{ $estConfidentiel ? 'fa-file-lock' : 'fa-shield-halved' }}" style="color:{{ $color }};"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);word-break:break-word;">{{ $doc->name }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">{{ $doc->client?->company_name }}</div>
        </div>
        <input type="checkbox" class="safebox-check" value="{{ $doc->id }}" onchange="updateBulk()">
      </div>
      <div class="sf-body">
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">
          <span class="sf-tag" style="background:#F3F4F6;color:#4B5563;">{{ strtoupper($doc->category ?? 'sensible') }}</span>
          @if($estConfidentiel)
            <span class="sf-tag" style="background:#FEF2F2;color:#B45309;"><i class="fas fa-lock" style="font-size:9px;"></i> Confidentiel</span>
          @endif
        </div>
        <div class="meta-row">
          <span><i class="far fa-calendar-alt"></i> Date</span>
          <span style="font-weight:600;color:var(--sec-text);">{{ $doc->document_date ? date('d/m/Y', strtotime($doc->document_date)) : optional($doc->created_at)->format('d/m/Y') }}</span>
        </div>
        <div class="meta-row">
          <span><i class="far fa-user"></i> Enregistré par</span>
          <span style="font-weight:600;color:var(--sec-text);">{{ $doc->uploaded_by ?? '—' }}</span>
        </div>
        <div style="display:flex;gap:8px;margin-top:10px;">
          <a href="{{ route('gel-secretary.safebox.view', $doc->id) }}" target="_blank" class="sec-btn sec-btn-secondary sec-btn-sm" style="flex:1;"><i class="fas fa-eye"></i> Lire</a>
          <a href="{{ route('gel-secretary.safebox.download', $doc->id) }}" class="sec-btn sec-btn-secondary sec-btn-sm" style="flex:1;"><i class="fas fa-download"></i> Télécharger</a>
        </div>
      </div>
    </div>
  @empty
    <div style="text-align:center;padding:60px 20px;color:var(--sec-text-muted);grid-column:1/-1;">
      <i class="fas fa-lock" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px;"></i>
      <p style="margin:0 0 6px;color:var(--sec-text);font-weight:600;">Coffre-fort vide pour ce périmètre</p>
      <p style="margin:0;font-size:13px;">Classez un document en « Confidentiel » ou en catégorie officielle (RCCM, IFU, statuts, CNSS…) pour qu'il apparaisse ici.</p>
    </div>
  @endforelse
</div>

<div style="margin-top:16px;">{{ $documents->links() }}</div>

{{-- ─── Historique de consultation ─────────────────────── --}}
<div class="panel-card" style="margin-top:24px;border:1px solid var(--sec-border);border-radius:12px;overflow:hidden;background:#fff;">
  <div class="tab-section-header"><i class="fas fa-history"></i> Historique des consultations & téléchargements</div>
  <div style="padding:16px;">
    @forelse($historique as $h)
      <div class="hist-row">
        <span style="font-weight:700;color:var(--sec-text);text-transform:capitalize;min-width:80px;">{{ $h->action }}</span>
        <span style="flex:1;color:var(--sec-text-muted);">{{ $h->document }}</span>
        <span style="font-weight:600;">{{ $h->user }}</span>
        <span style="color:var(--sec-text-muted);">{{ optional($h->at)->format('d/m H:i') }}</span>
      </div>
    @empty
      <div style="color:var(--sec-text-muted);font-size:12px;">Aucun accès au coffre-fort pour l'instant.</div>
    @endforelse
  </div>
</div>


<script>
  function updateBulk() {
    var n = document.querySelectorAll('.safebox-check:checked').length;
    var btn = document.getElementById('bulkDownloadBtn');
    if (btn) btn.disabled = (n === 0);
    return n;
  }
  document.addEventListener('change', function (e) {
    if (e.target && e.target.classList.contains('safebox-check')) updateBulk();
  });
  function toggleAll() {
    var boxes = document.querySelectorAll('.safebox-check');
    var checked = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).length;
    var wantAll = checked < boxes.length;
    boxes.forEach(function (b) { b.checked = wantAll; });
    updateBulk();
  }
  function bulkDownload() {
    var ids = [];
    document.querySelectorAll('.safebox-check:checked').forEach(function (b) { ids.push(b.value); });
    if (!ids.length) return;
    document.getElementById('bulkIds').value = JSON.stringify(ids);
    document.getElementById('bulkForm').submit();
  }
</script>

@endsection
