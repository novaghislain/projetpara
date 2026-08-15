@extends('layouts.gel-accountant')

@section('title', "Écriture {$ecriture->numero} — GEL Comptable")

@section('content')

{{-- ─── En-tête ────────────────────────────────────────────────────────────── --}}
<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; gap:16px; flex-wrap:wrap;">
    <div style="display:flex; align-items:center; gap:16px;">
        <div style="background:linear-gradient(135deg,#667eea,#764ba2); border-radius:14px; width:52px; height:52px; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 12px rgba(102,126,234,.35);">
            <i class="bi bi-journal-text" style="color:#fff; font-size:22px;"></i>
        </div>
        <div>
            <h1 style="font-size:22px; font-weight:800; color:#0f172a; margin:0; letter-spacing:-0.3px;">{{ $ecriture->numero }}</h1>
            <p style="color:#64748b; margin:3px 0 0; font-size:14px;">{{ $ecriture->libelle }}</p>
        </div>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        @if(!$ecriture->valide)
            <form action="{{ route('gel-accountant.comptabilite.ecritures.valider', $ecriture->id) }}" method="POST" style="display:inline;"
                  onsubmit="return confirm('Valider cette écriture ? Cette action est irréversible.');">
                @csrf
                <button type="submit" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:linear-gradient(135deg,#10b981,var(--gel-primary)); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 2px 8px rgba(16,185,129,.3);">
                    <i class="bi bi-patch-check-fill"></i> Valider l'écriture
                </button>
            </form>
            <a href="{{ route('gel-accountant.comptabilite.ecritures.edit', $ecriture->id) }}" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:#fff; color:#475569; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <i class="bi bi-pencil-square"></i> Modifier
            </a>
        @else
            <form action="{{ route('gel-accountant.comptabilite.ecritures.extourner', $ecriture->id) }}" method="POST" style="display:inline;"
                  onsubmit="return confirm('Extourner cette écriture ? Une écriture d\'annulation sera générée automatiquement.');">
                @csrf
                <button type="submit" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 2px 8px rgba(220,38,38,.3);">
                    <i class="bi bi-arrow-counterclockwise"></i> Extourner
                </button>
            </form>
        @endif
        <a href="{{ route('gel-accountant.comptabilite.ecritures') }}" style="display:inline-flex; align-items:center; gap:7px; padding:10px 18px; background:#fff; color:#475569; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; box-shadow:0 1px 3px rgba(0,0,0,.05);">
            <i class="bi bi-arrow-left"></i> Retour aux écritures
        </a>
    </div>
</div>

{{-- ─── Bandeau alerte si non validée ──────────────────────────────────────── --}}
@if(!$ecriture->valide)
<div style="display:flex; align-items:center; gap:12px; background:#fffbeb; border:1px solid #fcd34d; border-left:4px solid #f59e0b; border-radius:8px; padding:14px 18px; margin-bottom:22px;">
    <i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b; font-size:18px;"></i>
    <span style="font-size:13px; font-weight:600; color:#92400e;">Cette écriture est en brouillon et n'a pas encore été validée. Elle peut encore être modifiée ou supprimée.</span>
</div>
@endif

{{-- ─── Ligne de métriques rapides ─────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:24px;">
    @php
        $totD = $ecriture->lignes->where('sens','debit')->sum('montant');
        $totC = $ecriture->lignes->where('sens','credit')->sum('montant');
        $equilibre = abs($totD - $totC) < 0.01;
    @endphp
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px 18px; box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px; letter-spacing:.5px;">Total Débit</div>
        <div style="font-size:20px; font-weight:800; color:var(--gel-primary);">{{ number_format($totD, 0, ',', ' ') }}</div>
        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">FCFA</div>
    </div>
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px 18px; box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px; letter-spacing:.5px;">Total Crédit</div>
        <div style="font-size:20px; font-weight:800; color:#dc2626;">{{ number_format($totC, 0, ',', ' ') }}</div>
        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">FCFA</div>
    </div>
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px 18px; box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px; letter-spacing:.5px;">Équilibre</div>
        <div style="font-size:18px; font-weight:800; color:{{ $equilibre ? 'var(--gel-primary)' : '#dc2626' }}; display:flex; align-items:center; gap:6px;">
            @if($equilibre)
                <i class="bi bi-check-circle-fill" style="font-size:16px;"></i> Équilibrée
            @else
                <i class="bi bi-x-circle-fill" style="font-size:16px;"></i> Écart
            @endif
        </div>
        @if(!$equilibre)
            <div style="font-size:11px; color:#dc2626; margin-top:2px;">{{ number_format(abs($totD - $totC), 0, ',', ' ') }} FCFA</div>
        @endif
    </div>
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px 18px; box-shadow:0 1px 3px rgba(0,0,0,.04);">
        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px; letter-spacing:.5px;">Lignes</div>
        <div style="font-size:20px; font-weight:800; color:#0f172a;">{{ $ecriture->lignes->count() }}</div>
        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">mouvements</div>
    </div>
</div>

{{-- ─── Corps principal (2 colonnes) ───────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:300px 1fr; gap:20px; align-items:start;">

    {{-- Colonne gauche : infos écriture --}}
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05);">
        <div style="padding:14px 18px; background:linear-gradient(135deg,#f8fafc,#f1f5f9); border-bottom:1px solid #e2e8f0; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-card-list" style="color:#6366f1; font-size:15px;"></i>
            <span style="font-size:14px; font-weight:700; color:#0f172a;">Informations</span>
        </div>
        <div style="padding:18px; display:flex; flex-direction:column; gap:16px;">

            {{-- Numéro --}}
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:4px;">Numéro pièce</div>
                <div style="font-size:16px; font-weight:800; font-family:'Courier New',monospace; color:#0f172a; background:#f8fafc; border-radius:6px; padding:6px 10px; border:1px solid #e2e8f0; display:inline-block;">{{ $ecriture->numero }}</div>
            </div>

            {{-- Journal --}}
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:4px;">Journal</div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#e0e7ff; color:#4f46e5; font-family:monospace; font-weight:700; font-size:11px; padding:3px 7px; border-radius:5px;">{{ $ecriture->journal?->code }}</span>
                    <span style="font-size:13px; font-weight:600; color:#334155;">{{ $ecriture->journal?->libelle }}</span>
                </div>
            </div>

            {{-- Client --}}
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:4px;">Client / Entreprise</div>
                @if($ecriture->client)
                    <div style="display:flex; align-items:center; gap:7px; color:#2563eb; font-weight:600; font-size:13px;">
                        <i class="bi bi-building"></i> {{ $ecriture->client->nom_entreprise }}
                    </div>
                @else
                    <div style="font-size:13px; color:#94a3b8; font-style:italic;">Non rattaché</div>
                @endif
            </div>

            {{-- Date --}}
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:4px;">Date de saisie</div>
                <div style="font-size:13px; font-weight:600; color:#334155; display:flex; align-items:center; gap:7px;">
                    <i class="bi bi-calendar3" style="color:#6366f1;"></i> {{ \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') }}
                </div>
            </div>

            @if($ecriture->date_piece)
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:4px;">Date pièce</div>
                <div style="font-size:13px; font-weight:600; color:#334155; display:flex; align-items:center; gap:7px;">
                    <i class="bi bi-file-earmark-text" style="color:#6366f1;"></i> {{ \Carbon\Carbon::parse($ecriture->date_piece)->format('d/m/Y') }}
                </div>
            </div>
            @endif

            @if($ecriture->ref_piece)
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:4px;">Réf. pièce</div>
                <div style="font-size:13px; font-family:monospace; color:#334155;">{{ $ecriture->ref_piece }}</div>
            </div>
            @endif

            {{-- Auteur --}}
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:4px;">Auteur de la saisie</div>
                <div style="font-size:13px; font-weight:600; color:#334155; display:flex; align-items:center; gap:7px;">
                    <i class="bi bi-person-circle" style="color:#6366f1;"></i> {{ $ecriture->createur?->name ?? 'Système' }}
                </div>
            </div>

            {{-- Statut --}}
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:8px;">Statut de validation</div>
                @if($ecriture->valide)
                    <div style="display:inline-flex; align-items:center; gap:6px; background:#dcfce7; color:#15803d; padding:6px 12px; border-radius:20px; font-size:12px; font-weight:700; border:1px solid #86efac;">
                        <i class="bi bi-check-circle-fill"></i> Validée
                    </div>
                    @if($ecriture->validePar)
                    <div style="font-size:11px; color:#64748b; margin-top:8px; background:#f8fafc; border-radius:6px; padding:8px 10px; border:1px solid #f1f5f9;">
                        <i class="bi bi-person-check" style="color:#6366f1;"></i>
                        <strong>{{ $ecriture->validePar->name }}</strong><br>
                        {{ $ecriture->valide_at ? \Carbon\Carbon::parse($ecriture->valide_at)->format('d/m/Y à H:i') : '' }}
                    </div>
                    @endif
                @else
                    <div style="display:inline-flex; align-items:center; gap:6px; background:#fffbeb; color:#b45309; padding:6px 12px; border-radius:20px; font-size:12px; font-weight:700; border:1px solid #fcd34d;">
                        <i class="bi bi-clock-history"></i> Brouillon
                    </div>
                @endif
            </div>

            @if($ecriture->notes)
            <div>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.7px; margin-bottom:4px;">Notes</div>
                <div style="font-size:12px; color:#475569; background:#f8fafc; border-radius:6px; padding:10px; border:1px solid #e2e8f0;">{{ $ecriture->notes }}</div>
            </div>
            @endif

        </div>
    </div>

    {{-- Colonne droite : lignes comptables --}}
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05);">
        <div style="padding:14px 20px; background:linear-gradient(135deg,#f8fafc,#f1f5f9); border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:8px;">
                <i class="bi bi-table" style="color:var(--gel-primary); font-size:15px;"></i>
                <span style="font-size:14px; font-weight:700; color:#0f172a;">Lignes comptables</span>
                <span style="background:#e0f2fe; color:#0369a1; font-size:11px; font-weight:700; padding:2px 8px; border-radius:12px;">{{ $ecriture->lignes->count() }}</span>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="width:8px; padding:0;"></th>
                        <th style="padding:11px 14px; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; text-align:left; white-space:nowrap;">Compte</th>
                        <th style="padding:11px 14px; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; text-align:left;">Intitulé du compte</th>
                        <th style="padding:11px 14px; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; text-align:left;">Libellé de la ligne</th>
                        <th style="padding:11px 14px; font-size:11px; font-weight:700; color:var(--gel-primary); text-transform:uppercase; letter-spacing:.5px; text-align:right; white-space:nowrap;">Débit (FCFA)</th>
                        <th style="padding:11px 14px; font-size:11px; font-weight:700; color:#dc2626; text-transform:uppercase; letter-spacing:.5px; text-align:right; white-space:nowrap;">Crédit (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ecriture->lignes as $i => $ligne)
                        @php
                            $isDebit = $ligne->sens === 'debit';
                            $montant = (float) $ligne->montant;
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9; {{ $i % 2 === 0 ? 'background:#ffffff;' : 'background:#fafafa;' }}">
                            <td style="padding:0; width:4px; background:{{ $isDebit ? '#10b981' : '#ef4444' }};"></td>
                            <td style="padding:14px 14px 14px 16px;">
                                @if($ligne->compte)
                                    <span style="background:{{ $isDebit ? '#f0fdf4' : '#fff1f2' }}; color:{{ $isDebit ? '#15803d' : '#be123c' }}; font-family:'Courier New',monospace; font-weight:700; font-size:12px; padding:4px 8px; border-radius:6px; border:1px solid {{ $isDebit ? '#bbf7d0' : '#fecdd3' }}; white-space:nowrap;">{{ $ligne->compte->code }}</span>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">—</span>
                                @endif
                            </td>
                            <td style="padding:14px; font-size:13px; font-weight:500; color:#334155; max-width:200px;">
                                {{ $ligne->compte?->intitule ?? '—' }}
                            </td>
                            <td style="padding:14px; font-size:13px; color:#64748b; max-width:200px;">
                                {{ $ligne->libelle_ligne ?: '—' }}
                            </td>
                            <td style="padding:14px; text-align:right; white-space:nowrap;">
                                @if($isDebit)
                                    <span style="font-size:14px; font-weight:700; color:var(--gel-primary);">{{ number_format($montant, 0, ',', ' ') }}</span>
                                @else
                                    <span style="color:#cbd5e1; font-size:13px;">—</span>
                                @endif
                            </td>
                            <td style="padding:14px; text-align:right; white-space:nowrap;">
                                @if(!$isDebit)
                                    <span style="font-size:14px; font-weight:700; color:#dc2626;">{{ number_format($montant, 0, ',', ' ') }}</span>
                                @else
                                    <span style="color:#cbd5e1; font-size:13px;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:linear-gradient(135deg,#f8fafc,#f1f5f9); border-top:2px solid #e2e8f0;">
                        <td style="padding:0; width:4px;"></td>
                        <td colspan="3" style="padding:14px 14px 14px 16px; font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:.5px; text-align:right;">Totaux de l'écriture</td>
                        <td style="padding:14px; text-align:right; font-size:16px; font-weight:800; color:var(--gel-primary); white-space:nowrap;">{{ number_format($totD, 0, ',', ' ') }} <span style="font-size:11px; font-weight:600;">FCFA</span></td>
                        <td style="padding:14px; text-align:right; font-size:16px; font-weight:800; color:#dc2626; white-space:nowrap;">{{ number_format($totC, 0, ',', ' ') }} <span style="font-size:11px; font-weight:600;">FCFA</span></td>
                    </tr>
                    @if($equilibre)
                    <tr style="background:#f0fdf4;">
                        <td colspan="6" style="padding:10px 18px; text-align:center; font-size:12px; font-weight:700; color:#15803d;">
                            <i class="bi bi-check-circle-fill" style="margin-right:6px;"></i> Écriture équilibrée — Débit = Crédit
                        </td>
                    </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- ─── TIMELINE DE TRAÇABILITÉ HISTORIQUE ─────────────────────────────────── --}}
<div class="gel-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-top: 30px; margin-bottom: 30px;">
    <div style="padding: 16px 20px; background: linear-gradient(135deg,#f8fafc,#f1f5f9); border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-clock-history" style="color: #6366f1; font-size: 16px;"></i>
            <span style="font-size: 15px; font-weight: 700; color: #0f172a;">Traçabilité & Historique des actions</span>
        </div>
    </div>
    
    <div style="padding: 24px;">
        @if($logs->isEmpty())
            <div style="text-align: center; color: #94a3b8; padding: 20px 0;">
                <i class="bi bi-info-circle" style="font-size: 24px; display: block; margin-bottom: 6px;"></i>
                Aucun historique enregistré pour le moment.
            </div>
        @else
            <div style="position: relative; padding-left: 24px; border-left: 2px solid #e2e8f0; margin-left: 10px; display: flex; flex-direction: column; gap: 20px;">
                @foreach($logs as $log)
                    <div style="position: relative;">
                        <!-- Point de la timeline -->
                        <div style="position: absolute; left: -31px; top: 2px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; background: {{ $log->event === 'created' ? '#10b981' : ($log->event === 'updated' ? '#3b82f6' : ($log->event === 'validated' ? '#8b5cf6' : '#64748b')) }}; box-shadow: 0 0 0 4px {{ $log->event === 'created' ? 'rgba(16,185,129,0.15)' : ($log->event === 'updated' ? 'rgba(59,130,246,0.15)' : 'rgba(100,116,139,0.15)') }};"></div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px; margin-bottom: 6px;">
                            <div>
                                <span style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $log->actor_name ?? 'Utilisateur' }}</span>
                                <span style="font-size: 11px; font-family: monospace; color: #64748b; margin-left: 4px;">({{ $log->actor_email }})</span>
                                <span style="background: #e2e8f0; color: #475569; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px; text-transform: uppercase;">{{ $log->actor_role }}</span>
                            </div>
                            <div style="font-size: 12px; font-weight: 600; color: #64748b; display: flex; align-items: center; gap: 4px;">
                                <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y \à H:i:s') }}
                            </div>
                        </div>

                        <div style="font-size: 13px; color: #334155; font-weight: 500; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px; padding: 10px 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>{{ $log->description }}</span>
                                <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">IP: {{ $log->ip_address }}</span>
                            </div>
                            
                            @if($log->old_values || $log->new_values)
                                <div style="margin-top: 10px; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
                                    <details style="cursor: pointer;">
                                        <summary style="font-size: 11px; font-weight: 700; color: #4f46e5; outline: none; user-select: none;">Voir les détails de la modification</summary>
                                        <div style="margin-top: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-family: monospace; font-size: 11px;">
                                            @if($log->old_values)
                                                <div style="background: #fef2f2; border: 1px solid #fecdd3; border-radius: 6px; padding: 8px; color: #991b1b;">
                                                    <div style="font-weight: 700; margin-bottom: 4px; text-transform: uppercase; font-size: 9px;">Avant</div>
                                                    @foreach($log->old_values as $k => $v)
                                                        <div>{{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if($log->new_values)
                                                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 8px; color: #166534;">
                                                    <div style="font-weight: 700; margin-bottom: 4px; text-transform: uppercase; font-size: 9px;">Après</div>
                                                    @foreach($log->new_values as $k => $v)
                                                        <div>{{ $k }}: {{ is_array($v) ? json_encode($v) : $v }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </details>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection
