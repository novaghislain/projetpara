@extends('layouts.gel-secretary')

@section('title', 'Restructuration documentaire — Rapport')

@section('content')
  <style>
    .rs-card {
      background:#fff; border:1px solid #e2e8f0; border-radius:12px;
      padding:20px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.02);
    }
    .rs-badge {
      display:inline-flex; align-items:center; gap:6px;
      font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px;
    }
    .rs-proposed  { background:#fef3c7; color:#92400e; }
    .rs-approved  { background:#dbeafe; color:#1e40af; }
    .rs-executed  { background:#dcfce7; color:#166534; }
    .rs-rejected  { background:#fee2e2; color:#991b1b; }
    .rs-table { width:100%; border-collapse:collapse; font-size:12.5px; }
    .rs-table th {
      text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.3px;
      color:#94a3b8; padding:8px 10px; border-bottom:2px solid #e2e8f0;
    }
    .rs-table td { padding:8px 10px; border-bottom:1px solid #f1f5f9; color:#334155; }
    .rs-table tr:last-child td { border-bottom:none; }
    .rs-chip {
      display:inline-block; background:#f1f5f9; color:#475569;
      font-size:11px; padding:2px 8px; border-radius:6px; margin:2px 2px 0 0;
    }
  </style>

  <div class="sec-page-header">
    <div>
      <h1 class="sec-page-title">
        <i class="fas fa-clipboard-check" style="color:var(--sec-primary); margin-right:8px;"></i>
        Restructuration documentaire
      </h1>
      <p class="sec-page-sub">
        Section 1 — Unification de l'arborescence vers la structure canonique unique
        (<strong>Documents → Permanents / Courants → Année → Mois</strong>).
        Aucune suppression : les dossiers fusionnés sont déplacés puis restent récupérables dans la Corbeille.
      </p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="{{ route('gel-secretary.documents.index') }}" class="sec-btn" style="background:#f1f5f9; color:#475569; border:none;">
        <i class="fas fa-arrow-left"></i> Espace Documentaire
      </a>
      <span class="sec-btn" style="background:#fef3c7; color:#92400e; border:none; cursor:default;" title="L'analyse (avant/après simulé) est générée par la commande folders:restructure">
        <i class="fas fa-info-circle"></i> L'analyse est lancée via <code style="background:transparent; font-size:11px;">php artisan folders:restructure</code>
      </span>
    </div>
  </div>

  @if(session('success'))
    <div style="padding:12px 16px; background:#F0FDF4; border:1px solid #BBF7D0; color:#166534; border-radius:10px; margin-bottom:16px; font-size:13px;">
      <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div style="padding:12px 16px; background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; border-radius:10px; margin-bottom:16px; font-size:13px;">
      <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
    </div>
  @endif

  @if($reports->isEmpty())
    <div class="rs-card" style="text-align:center; padding:50px 20px; color:var(--sec-text-muted);">
      <i class="fas fa-clipboard-list" style="font-size:42px; color:#cbd5e1; margin-bottom:14px;"></i>
      <h3 style="font-size:17px; font-weight:700; color:var(--sec-text); margin-bottom:6px;">Aucun rapport disponible</h3>
      <p style="font-size:13px; max-width:440px; margin:0 auto;">
        Lancez l'analyse depuis la console :<br>
        <code style="background:#f1f5f9; padding:3px 8px; border-radius:6px; font-size:12px;">php artisan folders:restructure</code><br><br>
        Elle établit un <strong>inventaire avant/après simulé</strong> avec les doublons réels et les dossiers orphelins — sans rien modifier — avant toute validation.
      </p>
    </div>
  @else
    @foreach($reports as $report)
      @php
        $p = $report->payload ?? [];
        $before  = $p['before'] ?? [];
        $mapping = $p['mapping'] ?? [];
        $doublons = $p['doublons'] ?? [];
        $orphelins = $p['orphelins'] ?? [];
        $after   = $p['after'] ?? null;
        $statusLabels = [
          'proposed' => ['Rapport proposé — en attente de validation', 'rs-proposed', 'fa-hourglass-half'],
          'approved' => ['Validé — exécution automatique lancée', 'rs-approved', 'fa-circle-check'],
          'executed' => ['Exécuté — arborescence unifiée', 'rs-executed', 'fa-check-double'],
          'rejected' => ['Rejeté — aucune modification appliquée', 'rs-rejected', 'fa-ban'],
        ];
        [$statusText, $statusClass, $statusIcon] = $statusLabels[$report->status] ?? ['Inconnu', 'rs-proposed', 'fa-question'];
        $pending = $report->status === 'proposed';
      @endphp

      <div class="rs-card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:14px;">
          <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <h3 style="font-size:15px; font-weight:700; color:var(--sec-text); margin:0;">
              <i class="fas fa-file-invoice"></i> Rapport #{{ $report->id }}
            </h3>
            <span class="rs-badge {{ $statusClass }}"><i class="fas {{ $statusIcon }}"></i> {{ $statusText }}</span>
            @if($report->id === $reports->first()->id && $report->status === 'proposed')
              <span class="rs-badge" style="background:#e0f2fe; color:#0c4a6e;"><i class="fas fa-clock"></i> Analyse la plus récente</span>
            @endif
          </div>
          <span style="font-size:12px; color:#94a3b8;">
            <i class="fas fa-calendar-alt"></i> Généré le {{ \Illuminate\Support\Carbon::parse($p['date'] ?? $report->created_at)->format('d/m/Y à H:i') }}
          </span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:16px;">
          <div style="background:#f8fafc; border-radius:10px; padding:12px 14px;">
            <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; font-weight:700;">Racines héritées à unifier</div>
            <div style="font-size:22px; font-weight:800; color:var(--sec-text);">{{ count($before) }}</div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:12px 14px;">
            <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; font-weight:700;">Documents concernés</div>
            <div style="font-size:22px; font-weight:800; color:var(--sec-text);">{{ array_sum(array_column($before, 'docs')) }}</div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:12px 14px;">
            <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; font-weight:700;">{{ count($doublons) > 0 ? 'Groupes de doublons à fusionner' : 'Doublons' }}</div>
            @if(count($doublons) > 0)
              <div style="font-size:22px; font-weight:800; color:#d97706;">{{ count($doublons) }}</div>
            @else
              <div style="font-size:22px; font-weight:800; color:#16a34a;"><i class="fas fa-check"></i></div>
            @endif
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:12px 14px;">
            <div style="font-size:11px; color:#94a3b8; text-transform:uppercase; font-weight:700;">Orphelins (non touchés)</div>
            <div style="font-size:22px; font-weight:800; color:var(--sec-text);">{{ count($orphelins) }}</div>
          </div>
        </div>

        @if($after)
          <div style="padding:12px 16px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:10px; margin-bottom:16px; font-size:13px; color:#166534;">
            <i class="fas fa-check-double"></i> <strong>Après exécution :</strong>
            {{ $after['moved_folders'] ?? 0 }} dossier(s) déplacé(s),
            {{ $after['moved_docs'] ?? 0 }} document(s) réaffecté(s) (aucune perte).
            @if(count($after['roots_restantes'] ?? []) > 0)
              <span style="color:#b45309; margin-left:6px;">
                ⚠ {{ count($after['roots_restantes']) }} racine(s) encore hors canonique :
                {{ collect($after['roots_restantes'])->pluck('name')->implode(', ') }}.
              </span>
            @endif
          </div>
        @endif

        {{-- Mapping avant → après --}}
        <h4 style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin:0 0 8px;">
          <i class="fas fa-code-branch"></i> Mapping des racines héritées
        </h4>
        <table class="rs-table" style="margin-bottom:16px;">
          <thead>
            <tr>
              <th>Dossier source</th>
              <th>Destiné à</th>
              <th>Type</th>
              <th>Doc.</th>
              <th>Sous-dossiers</th>
            </tr>
          </thead>
          <tbody>
            @foreach($mapping as $m)
              <tr>
                <td><strong>{{ $m['name'] }}</strong> <span style="color:#94a3b8;">#{{ $m['id'] }}</span></td>
                <td>{{ $m['target'] }}</td>
                <td>
                  <span class="rs-chip">{{ $m['type'] }}</span>
                  @if($m['sub'])
                    <span class="rs-chip" style="background:#e0f2fe; color:#0c4a6e;">sous-dossier : {{ $m['sub'] }}</span>
                  @endif
                </td>
                <td>{{ $m['docs'] }}</td>
                <td>
                  @forelse($m['subfolders'] as $sf)
                    <span class="rs-chip">{{ $sf }}</span>
                  @empty
                    <span style="color:#cbd5e1;">—</span>
                  @endforelse
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        @if(count($doublons) > 0)
          <h4 style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin:0 0 8px;">
            <i class="fas fa-copy"></i> Doublons réels (fusion — aucune perte)
          </h4>
          <table class="rs-table" style="margin-bottom:16px;">
            <thead>
              <tr><th>Destination canonique</th><th>Dossiers sources</th><th>Sous-dossiers distincts</th><th>Docs tot.</th></tr>
            </thead>
            <tbody>
              @foreach($doublons as $d)
                <tr>
                  <td><strong>{{ $d['target'] }}</strong></td>
                  <td>{{ implode(', ', $d['dossiers']) }}</td>
                  <td>
                    @foreach(array_slice($d['subfolders'] ?? [], 0, 6) as $sf)
                      <span class="rs-chip">{{ $sf }}</span>
                    @endforeach
                    @if(count($d['subfolders'] ?? []) > 6) <span style="color:#94a3b8;">+{{ count($d['subfolders']) - 6 }}</span> @endif
                  </td>
                  <td><strong>{{ $d['docs_total'] }}</strong></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <div style="padding:10px 14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; margin-bottom:16px; font-size:12.5px; color:#166534;">
            <i class="fas fa-check"></i> Aucun doublon détecté dans ce périmètre.
          </div>
        @endif

        @if(count($orphelins) > 0)
          <h4 style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; margin:0 0 8px;">
            <i class="fas fa-exclamation-triangle" style="color:#d97706;"></i> Racines orphelines (ni client ni utilisateur) — listées, jamais déplacées sans décision
          </h4>
          <table class="rs-table" style="margin-bottom:16px;">
            <thead>
              <tr><th>#</th><th>Nom</th><th>Documents</th><th>Créé le</th></tr>
            </thead>
            <tbody>
              @foreach($orphelins as $o)
                <tr>
                  <td>{{ $o['id'] }}</td>
                  <td><strong>{{ $o['name'] }}</strong></td>
                  <td>{{ $o['docs'] }}</td>
                  <td>{{ \Illuminate\Support\Carbon::parse($o['created_at'])->format('d/m/Y') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif

        {{-- Actions / validation humaine explicite --}}
        @if($pending)
          <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; padding-top:14px; border-top:1px solid #f1f5f9;">
            <div style="font-size:12.5px; color:#475569; flex:1; min-width:220px;">
              <i class="fas fa-shield-halved" style="color:var(--sec-primary);"></i>
              <strong>Validation requise.</strong> Les fusions ci-dessus seront appliquées : déplacement des dossiers
              vers leur destination canonique, source mise en Corbeille. Aucun fichier supprimé.
            </div>
            <form method="POST" action="{{ route('gel-secretary.documents.restructure-approve', $report->id) }}" style="display:inline;"
              onsubmit="return confirm('Valider et EXÉCUTER la restructuration pour ce rapport ?\nDes dossiers seront déplacés/mis en Corbeille — aucune donnée perdue.');">
              @csrf
              <button type="submit" class="sec-btn sec-btn-primary">
                <i class="fas fa-check-double"></i> Valider &amp; Exécuter
              </button>
            </form>
            <form method="POST" action="{{ route('gel-secretary.documents.restructure-reject', $report->id) }}" style="display:inline;"
              onsubmit="return confirm('Rejeter ce rapport ? Aucune modification ne sera appliquée.');">
              @csrf
              <button type="submit" class="sec-btn" style="background:#f1f5f9; color:#475569; border:none;">
                <i class="fas fa-ban"></i> Rejeter
              </button>
            </form>
          </div>
        @else
          <div style="padding-top:12px; border-top:1px solid #f1f5f9; font-size:12.5px; color:#64748b;">
            <i class="fas fa-info-circle"></i>
            @if($report->status === 'executed')
              Rapport déjà exécuté. La structure canonique est en place — vous pouvez consulter la Corbeille pour vérifier les dossiers sources.
            @else
              Aucune action disponible pour ce statut.
            @endif
          </div>
        @endif
      </div>
    @endforeach
  @endif
@endsection