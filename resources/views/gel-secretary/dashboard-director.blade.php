@extends('layouts.gel-secretary')
@section('title', 'Tableau de bord Dirigeant — GEL Secrétariat')

@section('content')
<style>
  @keyframes fadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
  .animate-fade{animation:fadeUp .4s ease-out forwards;opacity:0}
  .delay-1{animation-delay:.06s} .delay-2{animation-delay:.12s} .delay-3{animation-delay:.18s} .delay-4{animation-delay:.24s}

  .dir-kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:28px; }
  .dir-kpi { background:#fff; border:1px solid var(--sec-border); border-radius:10px; padding:18px 16px;
              display:flex; align-items:center; gap:14px; box-shadow:0 1px 3px rgba(0,0,0,.03); }
  .dir-kpi-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0; }
  .dir-kpi-val { font-size:22px; font-weight:800; color:var(--sec-text); line-height:1.1; }
  .dir-kpi-label { font-size:11px; color:var(--sec-text-muted); font-weight:600; text-transform:uppercase; letter-spacing:.3px; }

  .dir-panel { background:#fff; border:1px solid var(--sec-border); border-radius:10px; box-shadow:0 1px 3px rgba(0,0,0,.03); overflow:hidden; }
  .dir-panel-header { padding:14px 18px; border-bottom:1px solid var(--sec-border); display:flex; justify-content:space-between; align-items:center; background:#FAFAFA; }
  .dir-panel-title { font-size:13px; font-weight:700; color:var(--sec-text); display:flex; align-items:center; gap:8px; }
  .dir-row { display:flex; align-items:center; gap:12px; padding:12px 18px; border-bottom:1px solid var(--sec-border); }
  .dir-row:last-child { border-bottom:none; }
  .empty-state { padding:36px; text-align:center; color:var(--sec-text-muted); font-size:13px; }
</style>

{{-- Header --}}
<div class="animate-fade" style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:28px;padding-bottom:14px;border-bottom:1px solid var(--sec-border);">
  <div>
    <div style="font-size:20px;font-weight:800;color:var(--sec-text);margin-bottom:4px;">
      <i class="fas fa-crown" style="color:#D97706;margin-right:10px;"></i>Tableau de bord Dirigeant
    </div>
    <div style="font-size:13px;color:var(--sec-text-muted);">
      Vue de supervision en temps réel • {{ \Carbon\Carbon::now()->locale('fr')->translatedFormat('l d F Y') }}
    </div>
  </div>
  <div style="padding:8px 16px;background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;font-size:12px;font-weight:600;color:#92400E;">
    <i class="fas fa-eye me-1"></i> Mode lecture seule
  </div>
</div>

{{-- KPIs --}}
<div class="dir-kpi-grid animate-fade delay-1">
  <div class="dir-kpi">
    <div class="dir-kpi-icon" style="background:#F0FDF4;color:#16A34A;"><i class="fas fa-building"></i></div>
    <div>
      <div class="dir-kpi-val">{{ $clients->count() }}</div>
      <div class="dir-kpi-label">Entreprises clientes</div>
    </div>
  </div>
  <div class="dir-kpi">
    <div class="dir-kpi-icon" style="background:#FEF2F2;color:#DC2626;"><i class="fas fa-tasks"></i></div>
    <div>
      <div class="dir-kpi-val">{{ $stats['pending_tasks'] }}</div>
      <div class="dir-kpi-label">Tâches en cours</div>
    </div>
  </div>
  <div class="dir-kpi">
    <div class="dir-kpi-icon" style="background:#EFF6FF;color:#2563EB;"><i class="fas fa-file-alt"></i></div>
    <div>
      <div class="dir-kpi-val">{{ $stats['docs_to_process'] }}</div>
      <div class="dir-kpi-label">Documents à traiter</div>
    </div>
  </div>
  <div class="dir-kpi">
    <div class="dir-kpi-icon" style="background:#FDF4FF;color:#9333EA;"><i class="fas fa-check-circle"></i></div>
    <div>
      <div class="dir-kpi-val">{{ $stats['tasks_done'] }}</div>
      <div class="dir-kpi-label">Tâches terminées (mois)</div>
    </div>
  </div>
</div>

{{-- 2ème ligne de KPIs --}}
<div class="dir-kpi-grid animate-fade delay-2" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">
  <div class="dir-kpi">
    <div class="dir-kpi-icon" style="background:#FFFBEB;color:#D97706;"><i class="fas fa-envelope-open-text"></i></div>
    <div>
      <div class="dir-kpi-val">{{ $stats['courriers_unprocessed'] }}</div>
      <div class="dir-kpi-label">Courriers non traités</div>
    </div>
  </div>
  <div class="dir-kpi">
    <div class="dir-kpi-icon" style="background:#F0FDF4;color:#059669;"><i class="fas fa-calendar-check"></i></div>
    <div>
      <div class="dir-kpi-val">{{ $stats['events_today'] }}</div>
      <div class="dir-kpi-label">Rendez-vous aujourd'hui</div>
    </div>
  </div>
  <div class="dir-kpi">
    <div class="dir-kpi-icon" style="background:#FEF2F2;color:#DC2626;"><i class="fas fa-exclamation-triangle"></i></div>
    <div>
      <div class="dir-kpi-val">{{ $stats['tasks_overdue'] }}</div>
      <div class="dir-kpi-label">Tâches en retard</div>
    </div>
  </div>
</div>

{{-- 2 colonnes : Activité du secrétariat + Entreprises --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px;" class="animate-fade delay-3">

  {{-- Activité du secrétariat --}}
  <div class="dir-panel">
    <div class="dir-panel-header">
      <div class="dir-panel-title"><i class="fas fa-stream" style="color:#8B5CF6;"></i> Activité du secrétariat (7 jours)</div>
    </div>
    @forelse($recentActivity as $item)
      <div class="dir-row">
        <div style="width:32px;height:32px;border-radius:8px;background:{{ $item['bg'] }};color:{{ $item['color'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas {{ $item['icon'] }}"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $item['title'] }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">{{ $item['time'] }}</div>
        </div>
      </div>
    @empty
      <div class="empty-state">Aucune activité récente.</div>
    @endforelse
  </div>

  {{-- Documents en attente --}}
  <div class="dir-panel">
    <div class="dir-panel-header">
      <div class="dir-panel-title"><i class="fas fa-inbox" style="color:#EF4444;"></i> Documents en attente de traitement</div>
      @if($stats['docs_to_process'] > 0)
        <span style="background:#FEE2E2;color:#DC2626;border-radius:12px;padding:2px 10px;font-size:11px;font-weight:700;">{{ $stats['docs_to_process'] }}</span>
      @endif
    </div>
    @forelse($docsToProcess as $doc)
      <div class="dir-row">
        <i class="fas fa-file-pdf" style="color:#EF4444;font-size:18px;"></i>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->name }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">{{ $doc->client->company_name ?? ($doc->client->nom_entreprise ?? '—') }} · {{ \Carbon\Carbon::parse($doc->created_at)->diffForHumans() }}</div>
        </div>
      </div>
    @empty
      <div class="empty-state">Aucun document en attente. ✅</div>
    @endforelse
  </div>
</div>

{{-- Clients + Messages --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;" class="animate-fade delay-4">
  <div class="dir-panel">
    <div class="dir-panel-header">
      <div class="dir-panel-title"><i class="fas fa-building" style="color:#64748B;"></i> Entreprises clientes</div>
    </div>
    @forelse($clients->take(8) as $c)
      <div class="dir-row">
        <div style="width:32px;height:32px;border-radius:6px;background:#E2E8F0;color:#475569;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;">
          {{ strtoupper(substr($c->nom_entreprise ?? $c->company_name ?? 'E',0,2)) }}
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;">{{ $c->nom_entreprise ?? $c->company_name }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">{{ $c->email ?? 'Non renseigné' }}</div>
        </div>
      </div>
    @empty
      <div class="empty-state">Aucun client.</div>
    @endforelse
  </div>

  <div class="dir-panel">
    <div class="dir-panel-header">
      <div class="dir-panel-title"><i class="fas fa-calendar-alt" style="color:#2563EB;"></i> Agenda (7 prochains jours)</div>
    </div>
    @forelse($upcomingEvents as $evt)
      <div class="dir-row">
        <div style="width:4px;height:36px;background:#2563EB;border-radius:4px;flex-shrink:0;"></div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;">{{ $evt->title }}</div>
          <div style="font-size:11px;color:var(--sec-text-muted);">
            <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($evt->start_at)->format('d M à H:i') }}
          </div>
        </div>
      </div>
    @empty
      <div class="empty-state">Aucun événement à venir.</div>
    @endforelse
  </div>
</div>
@endsection
