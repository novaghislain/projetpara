@extends('layouts.gel-secretary')
@section('title', ($client->nom_entreprise ?? 'Entreprise') . ' — Secrétariat')

@section('content')
  <style>
    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(8px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fade {
      animation: fadeUp 0.3s ease-out forwards;
      opacity: 0;
    }

    .delay-1 {
      animation-delay: 0.05s;
    }

    .delay-2 {
      animation-delay: 0.1s;
    }

    .tabs-container {
      background: white;
      border-radius: 10px;
      border: 1px solid var(--sec-border);
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
      margin-bottom: 24px;
      overflow: hidden;
    }

    .tab-btn {
      padding: 14px 10px;
      font-size: 11.5px;
      font-weight: 600;
      color: var(--sec-text-muted);
      border: none;
      border-right: 1px solid #F1F5F9;
      border-bottom: 1px solid #F1F5F9;
      background: white;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      white-space: nowrap;
    }

    .tab-btn:nth-child(6n) {
      border-right: none;
    }
    
    /* Remove bottom border for the last row items */
    .tab-btn:nth-child(n+7) {
      border-bottom: none;
    }

    .tab-btn:hover {
      background: #F8FAFC;
      color: var(--sec-primary);
    }

    .tab-btn.active {
      background: var(--sec-primary);
      color: white;
    }

    .tab-btn.active .badge-sm {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border-color: rgba(255, 255, 255, 0.3);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeUp 0.3s ease-out forwards;
    }

    .panel-card {
      background: white;
      border-radius: 8px;
      border: 1px solid var(--sec-border);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
      display: flex;
      flex-direction: column;
    }

    .list-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      border-bottom: 1px solid var(--sec-border);
      text-decoration: none;
      color: inherit;
    }

    .list-row:last-child {
      border-bottom: none;
    }

    .list-row:hover {
      background: #F8FAFC;
    }

    .empty-state {
      padding: 40px;
      text-align: center;
      color: var(--sec-text-muted);
      font-size: 13px;
    }

    .badge-sm {
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 600;
    }

    .b-danger {
      background: #FEF2F2;
      color: #DC2626;
      border: 1px solid #FECACA;
    }

    .b-warning {
      background: #FFFBEB;
      color: #D97706;
      border: 1px solid #FDE68A;
    }

    .b-success {
      background: #ECFDF5;
      color: #059669;
      border: 1px solid #A7F3D0;
    }

    .b-blue {
      background: #EFF6FF;
      color: #2563EB;
      border: 1px solid #BFDBFE;
    }

    .b-normal {
      background: #F8FAFC;
      color: #64748B;
      border: 1px solid #E2E8F0;
    }

    .tab-section-header {
      padding: 10px 16px;
      background: #F8FAFC;
      border-bottom: 1px solid var(--sec-border);
      font-size: 11px;
      font-weight: 700;
      color: var(--sec-text-muted);
      text-transform: uppercase;
    }
  </style>

  <div class="sec-page-header animate-fade">
    <div style="display:flex;align-items:center;gap:12px;">
      <a href="{{ route('gel-secretary.clients.index') }}" class="sec-btn sec-btn-secondary sec-btn-sm">
        <i class="fas fa-arrow-left"></i>
      </a>
      <div>
        <div class="sec-page-title">{{ $client->nom_entreprise }}</div>
        <div class="sec-page-sub">Vue 360° Entreprise</div>
      </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      @php
        $depositToken = \App\Http\Controllers\PublicDepositController::generateToken($client->cabinet_id, $client->id);
        $depositUrl = route('public.deposit.show', $depositToken);
        $bookingUrl = route('public.booking.show', 1);
      @endphp
      <button class="sec-btn sec-btn-secondary" onclick="copyDepositLink('{{ $depositUrl }}')">
        <i class="fas fa-cloud-upload-alt"></i> Lien Dépôt
      </button>
      <button class="sec-btn sec-btn-secondary" onclick="copyLink('{{ $bookingUrl }}', this)"
        style="background:#F0FDF4;color:#16A34A;border-color:#BBF7D0;">
        <i class="fas fa-calendar-plus"></i> Lien RDV
      </button>
      <form method="POST" action="{{ route('gel-secretary.switch-client') }}" style="margin:0;">
        @csrf
        <input type="hidden" name="client_id" value="{{ $client->id }}">
        <button type="submit" class="sec-btn sec-btn-primary">
          <i class="fas fa-exchange-alt"></i> Activer ce contexte
        </button>
      </form>
    </div>
  </div>

  @if(session('success'))
    <div
      style="background:#ECFDF5;color:#059669;border:1px solid #A7F3D0;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;">
      <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
  @endif

  <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;">

    {{-- COLONNE GAUCHE : FICHE D'IDENTITÉ --}}
    <div class="animate-fade delay-1"
      style="display:flex;flex-direction:column;gap:16px; position:sticky; top:80px; align-self:start; height:fit-content; max-height:calc(100vh - 100px); overflow-y:auto; scrollbar-width:thin;">

      <div class="panel-card">
        <div style="text-align:center;padding:24px 16px 20px;">
          <div style="width:64px;height:64px;border-radius:50%;background:var(--sec-primary);color:white;
                      display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;
                      margin:0 auto 12px;">
            {{ strtoupper(substr($client->nom_entreprise ?? 'E', 0, 2)) }}
          </div>
          <div style="font-size:18px;font-weight:700;margin-bottom:8px;">{{ $client->nom_entreprise }}</div>

          <div
            style="display:inline-flex; align-items:center; gap:6px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
               background: {{ $healthStatus === 'Sain' ? '#ECFDF5' : ($healthStatus === 'Attention' ? '#FFFBEB' : '#FEF2F2') }};
               color: {{ $healthStatus === 'Sain' ? '#059669' : ($healthStatus === 'Attention' ? '#D97706' : '#DC2626') }};
               border: 1px solid {{ $healthStatus === 'Sain' ? '#A7F3D0' : ($healthStatus === 'Attention' ? '#FDE68A' : '#FECACA') }};">
            <i class="fas {{ $healthStatus === 'Sain' ? 'fa-heartbeat' : 'fa-exclamation-triangle' }}"></i> Santé :
            {{ $healthStatus }}
          </div>
        </div>

        <div style="padding: 0 16px 16px;">
          @foreach([
              ['fas fa-envelope', 'Email', $client->email ?? '—'],
              ['fas fa-phone', 'Téléphone', $client->telephone ?? '—'],
              ['fas fa-map-marker-alt', 'Adresse', trim(($client->adresse ?? '') . ' ' . ($client->ville ?? '')) ?: '—'],
              ['fas fa-file-invoice', 'IFU', $client->ifu ?? '—'],
              ['fas fa-certificate', 'RCCM', $client->rc ?? '—'],
              ['fas fa-industry', 'Secteur', $client->secteur ?? '—'],
              ['fas fa-file-contract', 'Contrat', ucfirst($client->contract_type ?? '—')],
              ['fas fa-calendar', 'Depuis', $client->contract_start ? \Carbon\Carbon::parse($client->contract_start)->format('d/m/Y') : '—'],
            ] as $row)
            <div style="display:flex;gap:10px;padding:8px 0;border-top:1px solid #F3F4F6;align-items:flex-start;">
              <i class="{{ $row[0] }}"
                style="color:var(--sec-primary);margin-top:2px;width:14px;font-size:13px;flex-shrink:0;"></i>
              <div>
                <div style="font-size:10px;color:var(--sec-text-muted);text-transform:uppercase;font-weight:600;">
                  {{ $row[1] }}</div>
                <div style="font-size:12px; font-weight: 500; color:var(--sec-text); word-break: break-all;">{{ $row[2] }}
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Statistiques rapides -->
      <div class="panel-card" style="padding:16px;">
        <div
          style="font-size:11px;font-weight:700;color:var(--sec-text-muted);text-transform:uppercase;margin-bottom:12px;">
          Chiffres clés</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          @php
            $totalFactures = $invoices->count();
            $montantDu = $invoices->where('status', '!=', 'paid')->sum('balance_due');
            $tasksEnCours = $tasks->whereIn('statut', ['a_faire', 'en_cours'])->count();
            $docsCount = $documents->count();
          @endphp
          <div style="text-align:center;background:#F8FAFC;border-radius:6px;padding:10px;">
            <div style="font-size:18px;font-weight:700;color:var(--sec-text);">{{ $totalFactures }}</div>
            <div style="font-size:10px;color:var(--sec-text-muted);">Factures</div>
          </div>
          <div
            style="text-align:center;background:{{ $montantDu > 0 ? '#FEF2F2' : '#F0FDF4' }};border-radius:6px;padding:10px;">
            <div style="font-size:14px;font-weight:700;color:{{ $montantDu > 0 ? '#DC2626' : '#059669' }};">
              {{ number_format($montantDu, 0, ',', ' ') }}€</div>
            <div style="font-size:10px;color:var(--sec-text-muted);">Solde dû</div>
          </div>
          <div style="text-align:center;background:#F8FAFC;border-radius:6px;padding:10px;">
            <div style="font-size:18px;font-weight:700;color:var(--sec-text);">{{ $tasksEnCours }}</div>
            <div style="font-size:10px;color:var(--sec-text-muted);">Tâches actives</div>
          </div>
          <div style="text-align:center;background:#F8FAFC;border-radius:6px;padding:10px;">
            <div style="font-size:18px;font-weight:700;color:var(--sec-text);">{{ $docsCount }}</div>
            <div style="font-size:10px;color:var(--sec-text-muted);">Documents</div>
          </div>
        </div>
      </div>

      <!-- Journal d'appels : Nouveau appel -->
      <div class="panel-card" style="padding:16px;">
        <div
          style="font-size:11px;font-weight:700;color:var(--sec-text-muted);text-transform:uppercase;margin-bottom:12px;">
          <i class="fas fa-phone"></i> Enregistrer un appel</div>
        <form action="{{ route('gel-secretary.clients.call-log.store', $client->id) }}" method="POST">
          @csrf
          <div style="display:flex;flex-direction:column;gap:8px;">
            <select name="direction" class="sec-form-control" style="font-size:12px;padding:8px;">
              <option value="entrant">📞 Appel entrant</option>
              <option value="sortant">📲 Appel sortant</option>
            </select>
            <input type="text" name="contact_name" placeholder="Nom du contact" class="sec-form-control"
              style="font-size:12px;padding:8px;">
            <input type="datetime-local" name="called_at" value="{{ now()->format('Y-m-d\TH:i') }}"
              class="sec-form-control" style="font-size:12px;padding:8px;">
            <select name="statut" class="sec-form-control" style="font-size:12px;padding:8px;">
              <option value="terminé">✅ Terminé</option>
              <option value="sans-réponse">🔇 Sans réponse</option>
              <option value="à rappeler">🔁 À rappeler</option>
            </select>
            <input type="number" name="duration_minutes" placeholder="Durée (min)" min="1" class="sec-form-control"
              style="font-size:12px;padding:8px;">
            <textarea name="notes" placeholder="Notes (CR) de l'appel..." class="sec-form-control" rows="2"
              style="font-size:12px;resize:none;"></textarea>

            <label style="font-size: 11px; display: flex; align-items: center; gap: 6px; cursor: pointer; padding: 4px;">
              <input type="checkbox" name="create_task" value="1" style="accent-color: var(--sec-primary);">
              Créer une tâche pour cet appel
            </label>
            <button type="submit" class="sec-btn sec-btn-secondary" style="font-size:12px;justify-content:center;">
              <i class="fas fa-save"></i> Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>

    {{-- COLONNE DROITE : ONGLETS 360 --}}
    <div class="animate-fade delay-2">

      <div class="tabs-container" style="grid-template-columns: repeat(7, 1fr);">
        <button class="tab-btn active" onclick="openTab('profil')"><i class="fas fa-id-badge"></i> Profil</button>
        <button class="tab-btn" onclick="openTab('historique')"><i class="fas fa-history"></i> Historique</button>
        <button class="tab-btn" onclick="openTab('docs')"><i class="fas fa-folder-open"></i> Documents <span
            class="badge-sm b-normal">{{ $documents->count() }}</span></button>
        <button class="tab-btn" onclick="openTab('equipe')"><i class="fas fa-users"></i> Équipe <span
            class="badge-sm b-normal">{{ $equipe->count() }}</span></button>
        <button class="tab-btn" onclick="openTab('coordination')"><i class="fas fa-people-arrows"></i> Coordination <span
            class="badge-sm b-normal">{{ $coordinationActivity->count() }}</span></button>
        <button class="tab-btn" onclick="openTab('activities')"><i class="fas fa-stream"></i> Activités</button>
        <button class="tab-btn" onclick="openTab('rapports')"><i class="fas fa-chart-bar"></i> Rapports</button>
      </div>

      <div class="panel-card" style="min-height: 400px;">

        {{-- TAB: PROFIL (identité complète + santé) --}}
        <div id="tab-profil" class="tab-content active">
          <div class="tab-section-header"><i class="fas fa-id-badge"></i> Profil de l'entreprise</div>
          <div style="padding:20px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
              @foreach([
                  ['fas fa-industry', 'Secteur d\'activité', $client->secteur ?? '—'],
                  ['fas fa-file-invoice', 'IFU', $client->ifu ?? '—'],
                  ['fas fa-certificate', 'RCCM', $client->rc ?? '—'],
                  ['fas fa-file-contract', 'Type de contrat', ucfirst($client->contract_type ?? '—')],
                  ['fas fa-calendar', 'Contrat depuis', $client->contract_start ? \Carbon\Carbon::parse($client->contract_start)->format('d/m/Y') : '—'],
                  ['fas fa-map-marker-alt', 'Adresse', trim(($client->adresse ?? '') . ' ' . ($client->ville ?? '')) ?: '—'],
                  ['fas fa-envelope', 'Email', $client->email ?? '—'],
                  ['fas fa-phone', 'Téléphone', $client->telephone ?? '—'],
                ] as $pf)
                <div style="background:#F8FAFC;border:1px solid var(--sec-border);border-radius:8px;padding:14px;">
                  <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <i class="fas {{ $pf[0] }}" style="width:16px;color:var(--sec-primary);font-size:15px;"></i>
                    <div style="font-size:10px;color:var(--sec-text-muted);text-transform:uppercase;font-weight:600;">{{ $pf[1] }}</div>
                  </div>
                  <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $pf[2] }}</div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        {{-- TAB: ACTIVITES 360° --}}
        <div id="tab-activities" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-stream"></i> Flux d'activités récent</div>
          <div
            style="position: relative; padding-left: 20px; border-left: 2px solid var(--sec-border); margin-left: 10px;">
            @forelse($timeline as $item)
              <div style="margin-bottom: 20px; position: relative;">
                <div
                  style="position: absolute; left: -31px; top: 0; width: 20px; height: 20px; background: {{ $item['color'] }}; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 3px solid white;">
                  <i class="fas {{ $item['icon'] }}"></i>
                </div>
                <div
                  style="background: #F8FAFC; border: 1px solid var(--sec-border); border-radius: 8px; padding: 12px; margin-left: 10px;">
                  <div style="font-size: 11px; color: var(--sec-text-muted); margin-bottom: 4px;">
                    {{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y à H:i') }}</div>
                  <div style="font-size: 13px; font-weight: 600; color: var(--sec-text);">{{ $item['title'] }}</div>
                  @if(isset($item['desc']) && $item['desc'])
                    <div style="font-size: 12px; color: var(--sec-text); margin-top: 4px;">{{ $item['desc'] }}</div>
                  @endif
                </div>
              </div>
            @empty
              <div class="empty-state"><i class="fas fa-stream"
                  style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucune activité récente.
              </div>
            @endforelse
          </div>
        </div>

        {{-- TAB: DOCUMENTS --}}
        <div id="tab-docs" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-folder-open"></i> Documents ({{ $documents->count() }})</div>
          
          <div style="padding: 24px; text-align: center; background: #F8FAFC; border-radius: 8px; border: 1px dashed var(--sec-border); margin-bottom: 20px;">
            <i class="fas fa-folder-tree" style="font-size: 40px; color: var(--sec-primary); margin-bottom: 16px; opacity: 0.8;"></i>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--sec-text); margin-bottom: 8px;">Accéder à l'Espace Documentaire</h3>
            <p style="font-size: 13px; color: var(--sec-text-muted); margin-bottom: 16px;">
              Retrouvez l'intégralité des documents permanents et courants (arborescence annuelle) pour cette entreprise dans l'Espace Documentaire centralisé.
            </p>
            <a href="{{ route('gel-secretary.documents.index') }}" class="sec-btn sec-btn-primary">
              <i class="fas fa-external-link-alt" style="margin-right: 8px;"></i> Ouvrir l'Espace Documentaire
            </a>
          </div>

          <div style="font-size: 13px; font-weight: 600; color: var(--sec-text-muted); margin-bottom: 12px; text-transform: uppercase;">Derniers documents ajoutés</div>
          @forelse($documents->take(5) as $doc)
            <div class="list-row">
              <i class="fas fa-file-pdf" style="color:#EF4444; font-size:20px;"></i>
              <div style="flex:1; min-width:0;">
                <div style="font-size:13px; font-weight:600; color:var(--sec-text);">{{ $doc->name }}</div>
                <div style="font-size:11px; color:var(--sec-text-muted);">Ajouté le
                  {{ \Carbon\Carbon::parse($doc->created_at)->format('d/m/Y à H:i') }}</div>
              </div>
              <a href="{{ route('gel-secretary.documents.view', $doc->id) }}" class="sec-btn sec-btn-secondary sec-btn-sm"
                target="_blank"><i class="fas fa-download"></i></a>
            </div>
          @empty
            <div class="empty-state"><i class="fas fa-folder-open"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucun document.</div>
          @endforelse
        </div>

        {{-- TAB: TACHES --}}
        <div id="tab-tasks" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-tasks"></i> Tâches ({{ $tasks->count() }})</div>
          @forelse($tasks as $task)
            @php
              $isOverdue = in_array($task->statut, ['a_faire', 'en_cours']) && $task->date_echeance && \Carbon\Carbon::parse($task->date_echeance)->isPast();
              $prioColor = ['critique' => '#DC2626', 'haute' => '#D97706', 'moyenne' => '#2563EB', 'basse' => '#64748B'][$task->priorite] ?? '#64748B';
            @endphp
            <div class="list-row">
              <div style="width:4px;height:36px;background:{{ $prioColor }};border-radius:4px;flex-shrink:0;"></div>
              <div style="flex:1; min-width:0;">
                <div
                  style="font-size:13px; font-weight:600; color:var(--sec-text); {{ $task->statut === 'termine' ? 'text-decoration:line-through; opacity:0.6;' : '' }}">
                  {{ $task->titre }}</div>
                <div style="font-size:11px; color:{{ $isOverdue ? '#DC2626' : 'var(--sec-text-muted)' }};">
                  <i class="far fa-calendar-alt"></i> Échéance :
                  {{ $task->date_echeance ? \Carbon\Carbon::parse($task->date_echeance)->format('d/m/Y') : 'N/A' }}
                </div>
              </div>
              @if($isOverdue) <span class="badge-sm b-danger">En retard</span>
              @elseif($task->statut === 'termine') <span class="badge-sm b-success">Terminé</span>
              @else <span class="badge-sm b-normal">{{ ucfirst(str_replace('_', ' ', $task->statut)) }}</span>
              @endif
            </div>
          @empty
            <div class="empty-state"><i class="fas fa-tasks"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucune tâche.</div>
          @endforelse
        </div>

        {{-- TAB: FACTURES --}}
        <div id="tab-factures" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-file-invoice-dollar"></i> Factures ({{ $invoices->count() }})
          </div>
          @forelse($invoices as $inv)
            @php
              $isOverdue = $inv->status !== 'paid' && $inv->due_date && \Carbon\Carbon::parse($inv->due_date)->isPast();
            @endphp
            <div class="list-row">
              <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $inv->invoice_number }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">
                  Émise le {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d/m/Y') }}
                  @if($inv->due_date) — Échéance : {{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }} @endif
                </div>
              </div>
              <div style="text-align:right;min-width:80px;">
                <div style="font-size:13px;font-weight:700;color:var(--sec-text);">
                  {{ number_format($inv->total, 0, ',', ' ') }} €</div>
                <div style="font-size:11px;color:{{ $inv->balance_due > 0 ? '#DC2626' : '#059669' }};">
                  {{ $inv->balance_due > 0 ? 'Dû: ' . number_format($inv->balance_due, 0, ',', ' ') . ' €' : 'Payée' }}
                </div>
              </div>
              @if($isOverdue) <span class="badge-sm b-danger">En retard</span>
              @elseif($inv->status === 'paid') <span class="badge-sm b-success">Payée</span>
              @else <span class="badge-sm b-warning">En attente</span>
              @endif
            </div>
          @empty
            <div class="empty-state"><i class="fas fa-file-invoice"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucune facture.</div>
          @endforelse
        </div>

        {{-- TAB: AGENDA --}}
        <div id="tab-agenda" class="tab-content">
          <div class="tab-section-header"><i class="far fa-calendar-alt"></i> Rendez-vous ({{ $events->count() }})</div>
          @forelse($events as $evt)
            <div class="list-row">
              <div
                style="width:4px; height:32px; background:{{ $evt->couleur ?? 'var(--sec-primary)' }}; border-radius:4px;">
              </div>
              <div style="flex:1; min-width:0;">
                <div style="font-size:13px; font-weight:600; color:var(--sec-text);">{{ $evt->title }}</div>
                <div style="font-size:11px; color:var(--sec-text-muted);">
                  <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($evt->start_at)->format('d/m/Y H:i') }} -
                  {{ \Carbon\Carbon::parse($evt->end_at)->format('H:i') }}
                  @if($evt->location) — {{ $evt->location }} @endif
                </div>
              </div>
              <span class="badge-sm b-blue">{{ ucfirst($evt->type ?? 'RDV') }}</span>
            </div>
          @empty
            <div class="empty-state"><i class="far fa-calendar-alt"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucun événement.</div>
          @endforelse
        </div>

        {{-- TAB: COURRIERS --}}
        <div id="tab-courriers" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-envelope-open-text"></i> Courriers ({{ $courriers->count() }})
          </div>
          @forelse($courriers as $c)
            <div class="list-row">
              <div
                style="width:40px;height:40px;border-radius:8px;background:{{ $c->type === 'entrant' ? '#EFF6FF' : '#F0FDF4' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas {{ $c->type === 'entrant' ? 'fa-inbox' : 'fa-paper-plane' }}"
                  style="color:{{ $c->type === 'entrant' ? '#2563EB' : '#16A34A' }};font-size:16px;"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $c->objet }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">
                  {{ $c->type === 'entrant' ? 'De : ' . $c->expediteur : 'À : ' . $c->destinataire }}
                  — {{ \Carbon\Carbon::parse($c->date_courrier ?? $c->created_at)->format('d/m/Y') }}
                </div>
              </div>
              @php $statutColors = ['reçu' => 'b-blue', 'traité' => 'b-success', 'en_attente' => 'b-warning', 'archivé' => 'b-normal']; @endphp
              <span
                class="badge-sm {{ $statutColors[$c->statut] ?? 'b-normal' }}">{{ ucfirst(str_replace('_', ' ', $c->statut)) }}</span>
            </div>
          @empty
            <div class="empty-state"><i class="fas fa-envelope-open"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucun courrier.</div>
          @endforelse
        </div>

        {{-- TAB: MESSAGES --}}
        <div id="tab-msg" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-comments"></i> Messages ({{ $messages->count() }})</div>
          @forelse($messages as $msg)
            <div class="list-row" style="align-items:flex-start;">
              <div
                style="width:32px; height:32px; border-radius:50%; background:{{ $msg->sender_type === 'client' ? '#DBEAFE' : '#DCFCE7' }}; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:bold; flex-shrink:0;">
                {{ $msg->sender_type === 'client' ? 'CL' : 'SE' }}
              </div>
              <div
                style="flex:1; min-width:0; background: #F8FAFC; padding: 10px 12px; border-radius: 8px; border: 1px solid #E2E8F0;">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                  <span
                    style="font-size:11px; font-weight:700;">{{ $msg->sender_type === 'client' ? $client->nom_entreprise : 'Cabinet' }}</span>
                  <span
                    style="font-size:10px; color:var(--sec-text-muted);">{{ \Carbon\Carbon::parse($msg->created_at)->format('d/m/Y H:i') }}</span>
                </div>
                <div style="font-size:13px; color:var(--sec-text);">{{ $msg->message }}</div>
              </div>
            </div>
          @empty
            <div class="empty-state"><i class="far fa-comments"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucun message.</div>
          @endforelse
        </div>

        {{-- TAB: JOURNAL D'APPELS --}}
        <div id="tab-calls" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-phone-alt"></i> Journal d'appels ({{ $callLogs->count() }})
          </div>
          @forelse($callLogs as $call)
            <div class="list-row">
              <div
                style="width:36px;height:36px;border-radius:50%;background:{{ $call->direction === 'entrant' ? '#EFF6FF' : '#F0FDF4' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas {{ $call->direction === 'entrant' ? 'fa-phone-volume' : 'fa-phone' }}"
                  style="color:{{ $call->direction === 'entrant' ? '#2563EB' : '#16A34A' }};font-size:14px;"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:var(--sec-text);">
                  {{ $call->contact_name ?? 'Contact inconnu' }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">
                  {{ \Carbon\Carbon::parse($call->called_at)->format('d/m/Y à H:i') }}
                  @if($call->duration_minutes) — {{ $call->duration_minutes }} min @endif
                  @if($call->user) — par {{ $call->user->name }} @endif
                </div>
                @if($call->notes)
                  <div
                    style="font-size:12px;color:var(--sec-text);margin-top:4px;padding:6px 8px;background:#F8FAFC;border-radius:4px;">
                    {{ $call->notes }}</div>
                @endif
              </div>
              @php $callColors = ['terminé' => 'b-success', 'sans-réponse' => 'b-warning', 'à rappeler' => 'b-danger']; @endphp
              <span class="badge-sm {{ $callColors[$call->statut] ?? 'b-normal' }}">{{ ucfirst($call->statut) }}</span>
            </div>
          @empty
            <div class="empty-state"><i class="fas fa-phone-slash"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucun appel enregistré.</div>
          @endforelse
        </div>

        {{-- TAB: CONTACTS --}}
        <div id="tab-contacts" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-address-book"></i> Contacts de l'entreprise
            ({{ count($contacts) }})</div>
          @forelse($contacts as $contact)
            <div class="list-row">
              <div
                style="width:36px; height:36px; border-radius:50%; background:#EEF2FF; color:#6366F1; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:14px; flex-shrink:0;">
                {{ strtoupper(substr($contact->name ?? $contact->first_name ?? 'C', 0, 1)) }}
              </div>
              <div style="flex:1; min-width:0;">
                <div style="font-size:13px; font-weight:600; color:var(--sec-text);">
                  {{ $contact->name ?? (($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) }}</div>
                <div style="font-size:11px; color:var(--sec-text-muted);">
                  {{ $contact->position ?? '' }}
                  @if($contact->email) — <a href="mailto:{{ $contact->email }}"
                  style="color:var(--sec-primary);">{{ $contact->email }}</a> @endif
                  @if($contact->phone) — {{ $contact->phone }} @endif
                </div>
              </div>
              @if(!empty($contact->is_primary)) <span class="badge-sm b-blue">Principal</span> @endif
            </div>
          @empty
            <div class="empty-state"><i class="far fa-address-book"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucun contact enregistré.</div>
          @endforelse
        </div>

        {{-- TAB: NOTES INTERNES --}}
        <div id="tab-notes" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-sticky-note"></i> Notes internes (confidentielles)</div>
          <div style="padding: 16px;">
            <textarea class="sec-form-control" rows="10" id="notesTextarea"
              placeholder="Prenez des notes confidentielles sur cette entreprise. Ces notes ne sont visibles que par votre cabinet...">{{ $client->notes ?? '' }}</textarea>
            <div style="text-align: right; margin-top: 12px;">
              <button class="sec-btn sec-btn-primary" onclick="saveNotes()"><i class="fas fa-save"></i> Enregistrer les
                notes</button>
            </div>
          </div>
        </div>

        {{-- TAB: COORDINATION (S4.3 / S1.2) — fil partagé Secrétaire ↔ Comptable --}}
        <div id="tab-coordination" class="tab-content">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div class="tab-section-header" style="margin-bottom:0;"><i class="fas fa-people-arrows"></i> Coordination Secrétaire ↔ Comptable ({{ $coordinationActivity->count() }})</div>
            <a href="{{ route('gel-secretary.coordination.index', ['client_id' => $client->id]) }}" class="btn btn-sm" style="background:var(--sec-primary); color:#fff; border-radius:8px; text-decoration:none;"><i class="fas fa-arrow-right me-1"></i> Ouvrir l'espace</a>
          </div>
          @forelse($coordinationActivity as $ce)
            <div class="list-row" style="align-items:flex-start;">
              <div style="width:32px;height:32px;border-radius:50%;background:rgba(13,148,136,.1);color:var(--sec-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;"><i class="{{ $ce->icon }}"></i></div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:600;color:var(--sec-text);">{{ $ce->subject }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">{{ $ce->body }}</div>
                <div style="font-size:11px;color:#94A3B8;margin-top:2px;">par {{ $ce->actor_name ?? '—' }} · {{ \Carbon\Carbon::parse($ce->created_at)->format('d/m/Y H:i') }}</div>
              </div>
            </div>
          @empty
            <div class="empty-state"><i class="fas fa-people-arrows" style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucune activité de coordination entre la secrétaire et le comptable.</div>
          @endforelse
        </div>

        {{-- TAB: HISTORIQUE --}}
        <div id="tab-historique" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-history"></i> Historique des actions ({{ $history->count() }})
          </div>
          @forelse($history as $h)
            <div class="list-row" style="align-items:flex-start;">
              <div
                style="width:8px;height:8px;border-radius:50%;background:var(--sec-primary);margin-top:5px;flex-shrink:0;">
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:600;color:var(--sec-text);">{{ $h->action }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">
                  {{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y H:i') }}
                  @if($h->actor_email) — {{ $h->actor_email }} @endif
                </div>
              </div>
            </div>
          @empty
            <div class="empty-state"><i class="fas fa-history"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucune action enregistrée.
            </div>
          @endforelse
        </div>

        {{-- TAB: EQUIPE AFFECTEE --}}
        <div id="tab-equipe" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-users"></i> Équipe affectée à l'entreprise
            ({{ $equipe->count() }})</div>
          @forelse($equipe as $member)
            @php
              $roleIcon = match (strtolower($member->role ?? '')) {
                'secretaire', 'secretary' => 'fa-user-tie',
                'comptable', 'accountant' => 'fa-calculator',
                'admin', 'director', 'super_admin' => 'fa-user-shield',
                default => 'fa-user'
              };
              $roleClass = in_array(strtolower($member->role ?? ''), ['comptable', 'accountant']) ? 'b-blue'
                          : (in_array(strtolower($member->role ?? ''), ['admin', 'director', 'super_admin']) ? 'b-warning'
                          : 'b-success');
            @endphp
            <div class="list-row">
              <div style="width:40px;height:40px;border-radius:50%;background:#F0FDF4;color:#059669;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas {{ $roleIcon }}"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $member->user->name ?? 'Membre' }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">
                  {{ $member->user?->email ?? 'Email inconnu' }}
                  @if($member->joined_at) — Intégré le {{ \Carbon\Carbon::parse($member->joined_at)->format('d/m/Y') }} @endif
                  @if($member->last_accessed_at) — Dernier accès {{ $member->last_accessed_at->diffForHumans() }} @endif
                </div>
              </div>
              <span class="badge-sm {{ $roleClass }}">{{ ucfirst(str_replace('_', ' ', $member->role ?? 'membre')) }}</span>
            </div>
          @empty
            <div class="empty-state"><i class="fas fa-users"
                style="font-size:32px; opacity:0.3; margin-bottom:12px; display:block;"></i> Aucun membre affecté à cette
              entreprise pour le moment.</div>
          @endforelse
        </div>

        {{-- TAB: RAPPORTS --}}
        <div id="tab-rapports" class="tab-content">
          <div class="tab-section-header"><i class="fas fa-chart-bar"></i> Rapport de synthèse</div>
          <div style="padding:20px;">
            @php
              $rapportSoluDue = number_format($invoices->where('status', '!=', 'paid')->sum('balance_due'), 0, ',', ' ');
              $rapportTachesTerminees = $tasks->where('statut', 'termine')->count();
            @endphp
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;">
              <div style="text-align:center;background:#F8FAFC;border-radius:10px;padding:16px;">
                <div style="font-size:26px;font-weight:700;color:var(--sec-text);">{{ $documents->count() }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">Documents</div>
              </div>
              <div style="text-align:center;background:#F8FAFC;border-radius:10px;padding:16px;">
                <div style="font-size:26px;font-weight:700;color:var(--sec-text);">{{ $tasks->count() }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">Tâches</div>
              </div>
              <div style="text-align:center;background:#F8FAFC;border-radius:10px;padding:16px;">
                <div style="font-size:26px;font-weight:700;color:#059669;">{{ $rapportTachesTerminees }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">Tâches terminées</div>
              </div>
              <div style="text-align:center;background:{{ $invoices->where('status','!=','paid')->sum('balance_due') > 0 ? '#FEF2F2' : '#F0FDF4' }};border-radius:10px;padding:16px;">
                <div style="font-size:20px;font-weight:700;color:{{ $invoices->where('status','!=','paid')->sum('balance_due') > 0 ? '#DC2626' : '#059669' }};">
                  {{ $rapportSoluDue }}</div>
                <div style="font-size:11px;color:var(--sec-text-muted);">Solde dû (€)</div>
              </div>
            </div>

            <div style="font-size:11px;font-weight:700;color:var(--sec-text-muted);text-transform:uppercase;margin-bottom:12px;">
              Rapports générables</div>
            @foreach($reports as $r)
              <div class="list-row">
                <div style="width:40px;height:40px;border-radius:10px;background:#F8FAFC;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="fas {{ $r['icon'] }}" style="color:{{ $r['color'] }};"></i>
                </div>
                <div style="flex:1;min-width:0;">
                  <div style="font-size:13px;font-weight:600;color:var(--sec-text);">{{ $r['titre'] }}</div>
                  <div style="font-size:11px;color:var(--sec-text-muted);">{{ $r['desc'] }}</div>
                </div>
                <button type="button" class="sec-btn sec-btn-secondary sec-btn-sm" onclick="secToast('Rapport en cours de génération...','info')">
                  <i class="fas fa-download"></i> Générer
                </button>
              </div>
            @endforeach

            <div style="margin-top:16px;">
              <button type="button" class="sec-btn sec-btn-primary" onclick="generateAiSummary()">
                <i class="fas fa-sparkles" style="color:#F59E0B;"></i> Résumé IA du dossier
              </button>
              <div id="aiSummaryResult" style="margin-top:12px;font-size:13px;color:var(--sec-text);white-space:pre-wrap;"></div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>

  <script>
    function openTab(tabName) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
      document.getElementById('tab-' + tabName).classList.add('active');
      document.querySelector(`button[onclick="openTab('${tabName}')"]`).classList.add('active');
    }

    function copyDepositLink(url) {
      navigator.clipboard.writeText(url).then(() => {
        secToast('Lien de dépôt copié !', 'success');
      }).catch(() => secToast('Erreur de copie.', 'error'));
    }

    function copyLink(url, btn) {
      navigator.clipboard.writeText(url).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copié !';
        secToast('Lien RDV copié !', 'success');
        setTimeout(() => { btn.innerHTML = original; }, 2500);
      }).catch(() => secToast('Erreur de copie.', 'error'));
    }

    function saveNotes() {
      const notes = document.getElementById('notesTextarea').value;
      fetch('', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' }, body: JSON.stringify({ notes }) })
        .catch(() => { }); // Silently fail for now
      secToast('Notes sauvegardées localement.', 'success');
    }

    function generateAiSummary() {
      const btn = document.querySelector('button[onclick="generateAiSummary()"]');
      const out = document.getElementById('aiSummaryResult');
      if (!btn || !out) return;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyse en cours...';
      out.innerHTML = '';
      fetch("{{ route('gel-secretary.clients.ai-summary', $client->id) }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        out.innerHTML = data.summary || 'Aucun résumé généré.';
      })
      .catch(() => { out.innerHTML = 'Impossible de générer le résumé pour le moment.'; })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sparkles" style="color:#F59E0B;"></i> Résumé IA du dossier';
      });
    }
  </script>
@endsection
