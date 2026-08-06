@extends('layouts.gel-secretary')
@section('title', 'Administration — Secrétariat')

@section('content')
<style>
  .adm-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-top:20px; }
  .adm-tab { padding:10px 16px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px;
             display:inline-flex; align-items:center; gap:8px; background:#fff; color:var(--sec-text);
             border:1px solid var(--sec-border); cursor:pointer; transition:.2s; }
  .adm-tab:hover { background:#F8FAFC; }
  .adm-tab.active { background:var(--sec-primary); color:#fff; border-color:var(--sec-primary); }
  .adm-panel { display:none; }
  .adm-panel.active { display:block; animation:fadeUp .3s ease-out; }
  @keyframes fadeUp { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
  .panel-card { background:#fff; border:1px solid var(--sec-border); border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.02); }
  .list-row { display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--sec-border); }
  .list-row:last-child { border-bottom:none; }
  .list-row:hover { background:#F8FAFC; }
  .empty-state { padding:40px; text-align:center; color:var(--sec-text-muted); font-size:13px; }
  .tab-section-header { padding:10px 16px; background:#F8FAFC; border-bottom:1px solid var(--sec-border); font-size:11px; font-weight:700; color:var(--sec-text-muted); text-transform:uppercase; }
</style>

<div class="sec-page-header">
  <div>
    <div class="sec-page-title"><i class="fas fa-landmark" style="color:var(--sec-primary);margin-right:8px;"></i>Administration</div>
    <div class="sec-page-sub">Gestion administrative : courriers, e-mails, documents de gestion</div>
  </div>
</div>

@if($activeClient)
<div style="margin-bottom:14px;">
  <span class="sec-badge" style="background:#EFF6FF;color:#2563EB;border:1px solid #BFDBFE;padding:6px 12px;">
    <i class="fas fa-building"></i> Contexte : {{ $activeClient->nom_entreprise }}
  </span>
</div>
@endif

{{-- ─── Onglets Administration (S3) ─────────────────────────────────── --}}
<div class="adm-tabs animate-fade delay-1">
  <button class="adm-tab active" data-panel="courriers-entrants"><i class="fas fa-arrow-down"></i> Courriers entrants</button>
  <button class="adm-tab" data-panel="courriers-sortants"><i class="fas fa-arrow-up"></i> Courriers sortants</button>
  <button class="adm-tab" data-panel="emails"><i class="fas fa-envelope"></i> E-mails</button>
  <button class="adm-tab" data-panel="documents-types"><i class="fas fa-folder-tree"></i> Documents de gestion</button>
  <a href="{{ route('gel-secretary.administration.templates.index') }}" class="adm-tab" style="text-decoration:none;">
    <i class="fas fa-file-signature"></i> Centre de Modèles
  </a>
</div>

{{-- PANEL : Courriers entrants --}}
<div id="panel-courriers-entrants" class="adm-panel active">
  <div class="panel-card" style="background:#fff;border:1px solid var(--sec-border);border-radius:12px;margin-top:16px;overflow:hidden;">
    <div class="tab-section-header"><i class="fas fa-inbox"></i> Courriers entrants ({{ $courriersEntrants->count() }})</div>
    @forelse($courriersEntrants as $c)
      <div class="list-row">
        <div style="width:38px;height:38px;border-radius:8px;background:#EFF6FF;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas fa-inbox" style="color:#2563EB;"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $c->objet }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">
            De : {{ $c->expediteur }} — {{ optional($c->date_courrier)->format('d/m/Y') }}
            @if($c->reference) · {{ $c->reference }} @endif
          </div>
        </div>
        <span class="sec-badge" style="background:#EFF6FF;color:#2563EB;">{{ ucfirst(str_replace('_',' ',$c->statut)) }}</span>
      </div>
    @empty
      <div class="empty-state"><i class="fas fa-inbox" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>Aucun courrier entrant.</div>
    @endforelse
  </div>
</div>

{{-- PANEL : Courriers sortants --}}
<div id="panel-courriers-sortants" class="adm-panel">
  <div class="panel-card" style="margin-top:16px;">
    <div class="tab-section-header"><i class="fas fa-paper-plane"></i> Courriers sortants ({{ $courriersSortants->count() }})</div>
    @forelse($courriersSortants as $c)
      <div class="list-row">
        <div style="width:38px;height:38px;border-radius:8px;background:#F0FDF4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas fa-paper-plane" style="color:#16A34A;"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $c->objet }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">
            À : {{ $c->destinataire }} — {{ optional($c->date_courrier)->format('d/m/Y') }}
            @if ($c->reference) · {{ $c->reference }} @endif
          </div>
        </div>
        <span class="sec-badge" style="background:#ECFDF5;color:#059669;">{{ ucfirst(str_replace('_',' ',$c->statut)) }}</span>
      </div>
    @empty
      <div class="empty-state"><i class="fas fa-paper-plane" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>Aucun courrier sortant.</div>
    @endforelse
  </div>
</div>

{{-- PANEL : E-mails (lien webmail) --}}
<div id="panel-emails" class="adm-panel">
  <div class="panel-card" style="margin-top:16px;padding:40px;text-align:center;">
    <i class="fas fa-envelope-open-text" style="font-size:48px;color:var(--sec-primary);margin-bottom:16px;"></i>
    <h3 style="margin:0 0 8px;">Webmail externe (IMAP)</h3>
    <p style="color:var(--sec-text-muted);font-size:14px;margin-bottom:20px;">Gérez vos e-mails professionnels connectés à votre boîte.</p>
    <a href="{{ route('gel-secretary.mail.index') }}" class="sec-btn sec-btn-primary">
      <i class="fas fa-envelope"></i> Ouvrir le webmail
    </a>
  </div>
</div>

{{-- PANEL : Documents de gestion par sous-type (S3 + S9) --}}
<div id="panel-documents-types" class="adm-panel">
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-top:16px;">
    @foreach($docsByCategory as $cat)
      <div class="panel-card" style="overflow:hidden;">
        <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--sec-border);background:#F8FAFC;">
          <div style="width:36px;height:36px;border-radius:10px;background:{{ $cat['color'] }}1A;display:flex;align-items:center;justify-content:center;">
            <i class="fas {{ $cat['icon'] }}" style="color:{{ $cat['color'] }};"></i>
          </div>
          <div style="flex:1;">
            <div style="font-weight:700;font-size:13px;color:var(--sec-text);">{{ $cat['label'] }}</div>
            <div style="font-size:11px;color:var(--sec-text-muted);">{{ $cat['documents']->count() }} document(s)</div>
          </div>
        </div>
        <div>
          @forelse($cat['documents']->take(6) as $doc)
            <div class="list-row" style="padding:10px 16px;">
              <i class="fas fa-file-pdf" style="color:#EF4444;font-size:16px;"></i>
              <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:600;color:var(--sec-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->name }}</div>
                <div style="font-size:10px;color:var(--sec-text-muted);">{{ optional($doc->created_at)->format('d/m/Y') }}</div>
              </div>
              <a href="{{ route('gel-secretary.documents.view', $doc->id) }}" target="_blank" class="sec-btn sec-btn-secondary sec-btn-sm"><i class="fas fa-eye"></i></a>
            </div>
          @empty
            <div class="empty-state" style="padding:24px;">Aucun document classé.</div>
          @endforelse
        </div>
      </div>
    @endforeach
  </div>
</div>

<script>
  document.querySelectorAll('.adm-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.adm-tab').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.adm-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('panel-' + btn.dataset.panel).classList.add('active');
    });
  });
</script>
@endsection