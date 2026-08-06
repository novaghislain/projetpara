@extends('layouts.gel-secretary')
@section('title', 'Tableau de bord — Secrétariat')

@section('content')
<style>
  /* ANIMATIONS SUBTILES */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-fade { animation: fadeUp 0.4s ease-out forwards; opacity: 0; }
  .delay-1 { animation-delay: 0.05s; }
  .delay-2 { animation-delay: 0.1s; }
  .delay-3 { animation-delay: 0.15s; }
  .delay-4 { animation-delay: 0.2s; }

  /* EN-TETE PROFESSIONNEL */
  .pro-header {
    display: flex; justify-content: space-between; align-items: flex-end;
    margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid var(--sec-border);
  }
  .pro-title { font-size: 18px; font-weight: 700; color: var(--sec-text); margin-bottom: 4px; }
  .pro-subtitle { font-size: 12px; color: var(--sec-text-muted); }

  .pro-actions { display: flex; gap: 8px; }
  .pro-btn {
    background: white; border: 1px solid var(--sec-border); color: var(--sec-text);
    padding: 7px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
  }
  .pro-btn:hover { background: #F8FAFC; border-color: #CBD5E1; }
  .pro-btn-primary {
    background: var(--sec-primary); border-color: var(--sec-primary); color: white;
  }
  .pro-btn-primary:hover { background: var(--sec-primary-dark); color: white; border-color: var(--sec-primary-dark); }

  /* KPI COMPACTS */
  .kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 24px; }
  .kpi-card {
    background: white; border-radius: 8px; padding: 16px;
    border: 1px solid var(--sec-border);
    display: flex; align-items: center; gap: 14px;
    transition: border-color 0.15s, transform 0.15s; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }
  .kpi-icon {
    width: 40px; height: 40px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
    background: var(--bg-color); color: var(--icon-color);
  }
  .kpi-val { font-size: 20px; font-weight: 700; color: var(--sec-text); line-height: 1.1; margin-bottom: 2px; }
  .kpi-label { font-size: 11px; color: var(--sec-text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }

  /* PANNEAUX / CARTES */
  .pro-panel {
    background: white; border-radius: 8px; border: 1px solid var(--sec-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column;
  }
  .panel-header {
    padding: 14px 16px; border-bottom: 1px solid var(--sec-border);
    display: flex; justify-content: space-between; align-items: center;
    background: #FAFAFA; border-radius: 8px 8px 0 0;
  }
  .panel-title { font-size: 13px; font-weight: 700; color: var(--sec-text); display: flex; align-items: center; gap: 8px; }
  .panel-body { padding: 0; flex: 1; overflow-y: auto; max-height: 400px; }

  /* LISTES COMPACTES */
  .list-row {
    display: flex; align-items: center; gap: 12px; padding: 12px 16px;
    border-bottom: 1px solid var(--sec-border); text-decoration: none; color: inherit;
  }
  .list-row:last-child { border-bottom: none; }
  .list-row:hover { background: #F8FAFC; }
  
  .avatar-sm {
    width: 32px; height: 32px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 12px; background: #E2E8F0; color: #475569; flex-shrink: 0;
  }
  
  /* BADGES */
  .badge-sm {
    padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;
  }
  .b-active { background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; }
  .b-urgent { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
  .b-normal { background: #F8FAFC; color: #64748B; border: 1px solid #E2E8F0; }
  .b-warning { background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; }
  
  .empty-state { padding: 32px; text-align: center; color: var(--sec-text-muted); font-size: 12px; }
</style>

<div class="pro-header animate-fade">
  <div>
    <div class="pro-title">Aperçu de l'activité</div>
    <div class="pro-subtitle">Tableau de bord orienté action • {{ \Carbon\Carbon::now()->locale('fr')->translatedFormat('l d F Y') }}</div>
  </div>
  <div class="pro-actions">
    @if($activeClient)
      <a href="{{ route('gel-secretary.documents.index') }}" class="pro-btn">
        <i class="fas fa-folder"></i> Documents ({{ $activeClient->nom_entreprise }})
      </a>
    @else
      <a href="{{ route('gel-secretary.clients.index') }}" class="pro-btn">
        <i class="fas fa-building"></i> Annuaire clients
      </a>
    @endif
  </div>
</div>

<!-- MA JOURNÉE (GREETING PREMIUM) -->
<div class="pro-panel animate-fade delay-1" style="margin-bottom: 24px; background: linear-gradient(to right, #F0FDFA, #ffffff); border-left: 4px solid var(--sec-primary);">
  <div class="panel-body" style="padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        <h2 style="font-size:22px; font-weight:700; color:var(--sec-text); margin-bottom:12px;">Bonjour {{ $user->name ?? 'chère collaboratrice' }} 👋</h2>
        <div style="font-size:14px; color:var(--sec-text-muted); margin-bottom:16px;">Aujourd'hui vous avez :</div>
        
        <ul style="list-style:none; padding:0; margin:0; display:flex; gap:24px; flex-wrap:wrap;">
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-calendar-alt" style="color:#0D9488;"></i> 
            <span style="font-weight:600; color:var(--sec-text);">{{ $stats['greeting']['meetings'] }}</span> réunion(s)
          </li>
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-phone-alt" style="color:#F59E0B;"></i> 
            <span style="font-weight:600; color:var(--sec-text);">{{ $stats['greeting']['calls'] }}</span> appel(s) à effectuer
          </li>
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-envelope" style="color:#3B82F6;"></i> 
            <span style="font-weight:600; color:var(--sec-text);">{{ $stats['greeting']['mails'] }}</span> courrier(s) en attente
          </li>
          <li style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-file-signature" style="color:#8B5CF6;"></i> 
            <span style="font-weight:600; color:var(--sec-text);">{{ $stats['greeting']['validations'] }}</span> document(s) à valider
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- CENTRE DE PRODUCTIVITÉ -->
<div class="pro-panel animate-fade delay-2" style="margin-bottom: 24px;">
  <div class="panel-header">
    <div class="panel-title">
      <i class="fas fa-chart-line" style="color:#8B5CF6;"></i> Centre de productivité (Aujourd'hui)
    </div>
  </div>
  <div class="panel-body" style="padding: 16px;">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
      <div style="background:#F8FAFC; padding:16px; border-radius:8px; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:#10B981; margin-bottom:4px;">{{ $stats['productivity']['time_saved'] !== null ? $stats['productivity']['time_saved'].' h' : '—' }}</div>
        <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Temps moyen de traitement</div>
      </div>
      <div style="background:#F8FAFC; padding:16px; border-radius:8px; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:var(--sec-text); margin-bottom:4px;">{{ $stats['productivity']['docs_generated'] }}</div>
        <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Documents générés</div>
      </div>
      <div style="background:#F8FAFC; padding:16px; border-radius:8px; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:var(--sec-text); margin-bottom:4px;">{{ $stats['productivity']['tasks_completed'] }}</div>
        <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Tâches terminées</div>
      </div>
      <div style="background:#F8FAFC; padding:16px; border-radius:8px; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:var(--sec-text); margin-bottom:4px;">{{ $stats['productivity']['messages_sent'] }}</div>
        <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Messages envoyés</div>
      </div>
    </div>
  </div>
</div>

<!-- KPIs STRATÉGIQUES -->
<div class="kpi-grid animate-fade delay-1">
  <div class="kpi-card">
    <div class="kpi-icon" style="--bg-color: #F0FDFA; --icon-color: #0D9488;">
      <i class="fas fa-check-circle"></i>
    </div>
    <div>
      <div class="kpi-val">{{ $stats['completion_rate'] }}%</div>
      <div class="kpi-label">Taux de réalisation</div>
    </div>
  </div>
  
  <div class="kpi-card">
    <div class="kpi-icon" style="--bg-color: #FEF2F2; --icon-color: #DC2626;">
      <i class="fas fa-exclamation-circle"></i>
    </div>
    <div>
      <div class="kpi-val">{{ $stats['tasks_overdue'] }}</div>
      <div class="kpi-label">Tâches en retard</div>
      @if(isset($aiKpiInsights['tasks_overdue']))
        <div style="font-size: 10px; color: #0D9488; margin-top: 4px; line-height: 1.2;"><i class="fas fa-sparkles" style="font-size: 8px;"></i> {{ $aiKpiInsights['tasks_overdue'] }}</div>
      @endif
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="--bg-color: #EFF6FF; --icon-color: #2563EB;">
      <i class="fas fa-inbox"></i>
    </div>
    <div>
      <div class="kpi-val">{{ $stats['messages_unread'] }}</div>
      <div class="kpi-label">Messages non lus</div>
      @if(isset($aiKpiInsights['messages_unread']))
        <div style="font-size: 10px; color: #0D9488; margin-top: 4px; line-height: 1.2;"><i class="fas fa-sparkles" style="font-size: 8px;"></i> {{ $aiKpiInsights['messages_unread'] }}</div>
      @endif
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="--bg-color: #F5F3FF; --icon-color: #7C3AED;">
      <i class="fas fa-file-alt"></i>
    </div>
    <div>
      <div class="kpi-val">{{ $stats['documents_recent'] }}</div>
      <div class="kpi-label">Documents (Auj.)</div>
    </div>
  </div>

  <a href="{{ route('gel-secretary.agenda.index', ['view' => 'online']) }}" class="kpi-card" style="text-decoration:none; grid-column: span 1; {{ $stats['new_bookings'] > 0 ? 'border-color:#BBF7D0; background:#F0FDF4;' : '' }}">
    <div class="kpi-icon" style="--bg-color: #F0FDF4; --icon-color: #16A34A;">
      <i class="fas fa-calendar-check"></i>
    </div>
    <div>
      <div class="kpi-val" style="color: {{ $stats['new_bookings'] > 0 ? '#16A34A' : 'var(--sec-text)' }};">{{ $stats['new_bookings'] }}</div>
      <div class="kpi-label">RDV en ligne (48h)</div>
    </div>
  </a>
</div>


<!-- S1 : FLUX DOCUMENTAIRE & COURRIERS À TRAITER -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; margin-bottom: 40px;">
  <!-- Documents à traiter -->
  <div class="pro-panel animate-fade delay-1">
    <div class="panel-header">
      <div class="panel-title">
        <i class="fas fa-inbox" style="color:#EF4444;"></i> Documents à traiter
        @if($docsToProcessCount > 0)
          <span id="docsToProcessBadge" style="background:#FEE2E2; color:#EF4444; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; margin-left:8px;">{{ $docsToProcessCount }}</span>
        @else
          <span id="docsToProcessBadge" style="display:none; background:#FEE2E2; color:#EF4444; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; margin-left:8px;">0</span>
        @endif
      </div>
      <a href="{{ route('gel-secretary.documents.index') }}" style="font-size:11px; color:var(--sec-primary); text-decoration:none; font-weight:600;">Ouvrir l'espace doc →</a>
    </div>
    <div class="panel-body">
      @forelse($docsToProcess as $doc)
        <a href="{{ route('gel-secretary.documents.folder', $doc->folder_id ?? 0) }}" class="list-row">
          <div style="width: 4px; height: 32px; background: {{ $doc->priority === 'urgente' ? '#EF4444' : '#F59E0B' }}; border-radius: 4px;"></div>
          <i class="fas fa-file-alt" style="color: #64748B; font-size: 18px;"></i>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 13px; font-weight: 600; color: var(--sec-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $doc->name }}</div>
            <div style="font-size: 11px; color: var(--sec-text-muted);">
              @if($doc->client)<i class="fas fa-building" style="margin-right: 4px;"></i>{{ $doc->client->company_name }} · @endif
              {{ \Carbon\Carbon::parse($doc->created_at)->diffForHumans() }}
            </div>
          </div>
          @if($doc->priority === 'urgente')<span class="badge-sm b-urgent">Urgent</span>@endif
        </a>
      @empty
        <div class="empty-state">Aucun document en attente de traitement. 🎉</div>
      @endforelse
    </div>
  </div>

  <!-- Courriers non traités -->
  <div class="pro-panel animate-fade delay-2">
    <div class="panel-header">
      <div class="panel-title">
        <i class="fas fa-envelope-open-text" style="color:#D97706;"></i> Courriers non traités
        @if($unprocessedCourriersCount > 0)
          <span id="unprocessedCourriersBadge" style="background:#FEF3C7; color:#D97706; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; margin-left:8px;">{{ $unprocessedCourriersCount }}</span>
        @else
          <span id="unprocessedCourriersBadge" style="display:none; background:#FEF3C7; color:#D97706; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; margin-left:8px;">0</span>
        @endif
      </div>
      <a href="{{ route('gel-secretary.courriers.index') }}" style="font-size:11px; color:var(--sec-primary); text-decoration:none; font-weight:600;">Tout voir →</a>
    </div>
    <div class="panel-body">
      @forelse($unprocessedCourriers as $cr)
        <a href="{{ route('gel-secretary.courriers.show', $cr->id) }}" class="list-row">
          <div style="width:32px; height:32px; border-radius:8px; background:#F1F5F9; display:flex; align-items:center; justify-content:center; color:{{ $cr->type === 'entrant' ? '#EF4444' : '#2563EB' }};">
            <i class="fas {{ $cr->type === 'entrant' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
          </div>
          <div style="flex:1; min-width:0;">
            <div style="font-size:13px; font-weight:600; color:var(--sec-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cr->objet }}</div>
            <div style="font-size:11px; color:var(--sec-text-muted);">
              {{ $cr->expediteur ?? $cr->destinataire ?? '—' }} · {{ \Carbon\Carbon::parse($cr->date_courrier ?? $cr->created_at)->format('d/m/Y') }}
            </div>
          </div>
          @if($cr->urgence === 'haute' || $cr->urgence === 'critique')<span class="badge-sm b-urgent">Urgent</span>@endif
        </a>
      @empty
        <div class="empty-state">Tous les courriers sont traités. ✅</div>
      @endforelse
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px;">

  <!-- AUJOURD'HUI -->
  <div class="pro-panel animate-fade delay-2">
    <div class="panel-header">
      <div class="panel-title"><i class="fas fa-sun" style="color:#D97706;"></i> Aujourd'hui</div>
    </div>
    <div class="panel-body">
      
      <!-- RDV du jour -->
      <div style="padding: 10px 16px; background: #F8FAFC; border-bottom: 1px solid var(--sec-border); font-size: 11px; font-weight: 700; color: var(--sec-text-muted); text-transform: uppercase;">Rendez-vous</div>
      @forelse($todayEvents as $evt)
        <div class="list-row">
          <div style="width: 4px; height: 32px; background: #0D9488; border-radius: 4px;"></div>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 13px; font-weight: 600; color: var(--sec-text);">{{ $evt->title }}</div>
            <div style="font-size: 11px; color: var(--sec-text-muted);"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($evt->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($evt->end_at)->format('H:i') }}</div>
          </div>
        </div>
      @empty
        <div class="empty-state">Aucun rendez-vous prévu aujourd'hui.</div>
      @endforelse

      <!-- Tâches urgentes -->
      <div style="padding: 10px 16px; background: #F8FAFC; border-bottom: 1px solid var(--sec-border); border-top: 1px solid var(--sec-border); font-size: 11px; font-weight: 700; color: var(--sec-text-muted); text-transform: uppercase;">Tâches Urgentes</div>
      @forelse($urgentTasks as $task)
        <a href="{{ route('gel-secretary.tasks.index') }}" class="list-row">
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 13px; font-weight: 500; color: var(--sec-text);">{{ $task->titre }}</div>
            @if($task->client)
              <div style="font-size: 11px; color: var(--sec-text-muted);"><i class="fas fa-building" style="margin-right: 4px;"></i>{{ $task->client->nom_entreprise ?? $task->client->company_name ?? '—' }}</div>
            @endif
          </div>
          <span class="badge-sm b-urgent">Urgent</span>
        </a>
      @empty
        <div class="empty-state">Aucune tâche urgente pour aujourd'hui.</div>
      @endforelse

      <!-- Documents reçus -->
      <div style="padding: 10px 16px; background: #F8FAFC; border-bottom: 1px solid var(--sec-border); border-top: 1px solid var(--sec-border); font-size: 11px; font-weight: 700; color: var(--sec-text-muted); text-transform: uppercase;">Derniers documents reçus</div>
      @forelse($recentDocs as $doc)
        <a href="{{ route('gel-secretary.documents.view', $doc->id) }}" class="list-row" target="_blank">
          <i class="fas fa-file-pdf" style="color: #EF4444; font-size: 18px;"></i>
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 13px; font-weight: 500; color: var(--sec-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $doc->name }}</div>
            @if($doc->client)
              <div style="font-size: 11px; color: var(--sec-text-muted);">{{ $doc->client->company_name }}</div>
            @endif
          </div>
        </a>
      @empty
        <div class="empty-state">Aucun document reçu aujourd'hui.</div>
      @endforelse

    </div>
  </div>

  <!-- CETTE SEMAINE -->
  <div class="pro-panel animate-fade delay-3">
    <div class="panel-header">
      <div class="panel-title"><i class="fas fa-calendar-week" style="color:#2563EB;"></i> Anticipation (7 jours)</div>
    </div>
    <div class="panel-body">
      
      <!-- Événements à venir -->
      <div style="padding: 10px 16px; background: #F8FAFC; border-bottom: 1px solid var(--sec-border); font-size: 11px; font-weight: 700; color: var(--sec-text-muted); text-transform: uppercase;">Prochains événements</div>
      @forelse($upcomingEvents as $evt)
        <div class="list-row">
          <div style="flex: 1; min-width: 0;">
            <div style="font-size: 13px; font-weight: 500; color: var(--sec-text);">{{ $evt->title }}</div>
            <div style="font-size: 11px; color: var(--sec-text-muted);">
              <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($evt->start_at)->format('d M') }} à {{ \Carbon\Carbon::parse($evt->start_at)->format('H:i') }}
            </div>
            @if(isset($evt->ai_prep))
            <div style="margin-top: 6px; padding: 6px 10px; background: #FFFBEB; border-left: 3px solid #F59E0B; border-radius: 4px; font-size: 11px; color: #92400E; display: flex; gap: 6px;">
                <i class="fas fa-robot" style="margin-top: 2px;"></i>
                <div>{{ $evt->ai_prep }}</div>
            </div>
            @endif
          </div>
        </div>
      @empty
        <div class="empty-state" style="padding:16px;font-size:12px;">Aucun événement prévu cette semaine.</div>
      @endforelse

      <!-- Factures arrivant à échéance -->
      @if($invoicesDueThisWeek->isNotEmpty())
      <div style="padding: 10px 16px; background: #FFFBEB; border-top: 1px solid #FDE68A; border-bottom: 1px solid #FDE68A; font-size: 11px; font-weight: 700; color: #92400E; text-transform: uppercase;">
        <i class="fas fa-file-invoice-dollar"></i> Factures à régler (7j) — {{ $invoicesDueThisWeek->count() }}
      </div>
      @foreach($invoicesDueThisWeek as $inv)
        <a href="{{ route('gel-secretary.clients.show', $inv->client_id) }}" class="list-row">
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $inv->invoice_number }}</div>
            <div style="font-size:11px;color:var(--sec-text-muted);">
              {{ $inv->client->company_name ?? '—' }} — Échéance : {{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }}
            </div>
          </div>
          <span style="font-size:12px;font-weight:700;color:#D97706;">{{ number_format($inv->balance_due,0,',',' ') }} €</span>
        </a>
      @endforeach
      @endif

      <!-- Déclarations fiscales -->
      @if($taxDueThisWeek->isNotEmpty())
      <div style="padding: 10px 16px; background: #FEF2F2; border-top: 1px solid #FECACA; border-bottom: 1px solid #FECACA; font-size: 11px; font-weight: 700; color: #991B1B; text-transform: uppercase;">
        <i class="fas fa-landmark"></i> Déclarations fiscales (7j) — {{ $taxDueThisWeek->count() }}
      </div>
      @foreach($taxDueThisWeek as $tax)
        <div class="list-row">
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ strtoupper($tax->tax_type ?? '') }} — {{ $tax->reference ?? 'N°'.$tax->id }}</div>
            <div style="font-size:11px;color:var(--sec-text-muted);">
              {{ $tax->client->company_name ?? '—' }} — Échéance : {{ \Carbon\Carbon::parse($tax->date_echeance)->format('d/m/Y') }}
            </div>
          </div>
          <span class="badge-sm" style="background:#FEF2F2;color:#DC2626;border:1px solid #FECACA;">{{ ucfirst($tax->status) }}</span>
        </div>
      @endforeach
      @endif

      <!-- Nouveaux RDV pris en ligne -->
      @if($newBookings->isNotEmpty())
      <div style="padding: 10px 16px; background: #F0FDF4; border-top: 1px solid #BBF7D0; border-bottom: 1px solid #BBF7D0; font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase;">
        <i class="fas fa-calendar-plus"></i> Nouveaux RDV en ligne — {{ $newBookings->count() }}
      </div>
      @foreach($newBookings as $booking)
        <div class="list-row">
          <div style="width:4px;height:32px;background:#16A34A;border-radius:4px;"></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $booking->title }}</div>
            <div style="font-size:11px;color:var(--sec-text-muted);">
              <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($booking->start_at)->format('d M à H:i') }}
            </div>
          </div>
          <span class="badge-sm" style="background:#DCFCE7;color:#16A34A;border:1px solid #BBF7D0;">Nouveau</span>
        </div>
      @endforeach
      @endif

    </div>
  </div>

  <!-- ENTREPRISES CLIENTES -->
  <div class="pro-panel animate-fade delay-4">
    <div class="panel-header">
      <div class="panel-title"><i class="fas fa-building" style="color:#64748B;"></i> Entreprises</div>
      <a href="{{ route('gel-secretary.clients.index') }}" class="pro-btn" style="padding: 4px 8px; font-size: 11px;">Annuaire</a>
    </div>
    <div class="panel-body">
      @forelse($clients->take(10) as $c)
      <a href="{{ route('gel-secretary.clients.show', $c->id) }}" class="list-row">
        <div class="avatar-sm">
          {{ strtoupper(substr($c->nom_entreprise ?? 'E', 0, 2)) }}
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-size: 13px; font-weight: 600; color: var(--sec-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $c->nom_entreprise }}</div>
          <div style="font-size: 11px; color: var(--sec-text-muted);">{{ $c->email ?? 'Non renseigné' }}</div>
        </div>
        @if($activeClient?->id == $c->id)
          <span class="badge-sm b-active">Actif</span>
        @endif
      </a>
      @empty
      <div class="empty-state">
        Aucun client enregistré.
      </div>
      @endforelse
    </div>
  </div>

</div>
@endsection
