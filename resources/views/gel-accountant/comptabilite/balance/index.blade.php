@extends('layouts.gel-accountant')

@section('title', 'Balance Général — GEL Cabinet')

@section('content')
<div class="gel-page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 class="gel-page-title" style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">Balance Générale</h1>
        <p class="gel-page-subtitle" style="color: #64748b; margin-top: 4px; font-size: 14px;">Balance des comptes — {{ $comptes->count() ?? 0 }} compte(s) disponible(s)</p>
    </div>
</div>

{{-- Filtres --}}
<div class="gel-card mb-4" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="gel-card-body" style="padding: 20px;">
        <form method="GET" action="{{ route('gel-accountant.comptabilite.balance') }}" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Client / Entreprise</label>
                <select name="client_id" class="gel-form-select" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; font-size: 14px;">
                    <option value="">Tous les clients</option>
                    @if(isset($clients) && count($clients) > 0)
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom_entreprise }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div style="width: 160px;">
                <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Classe de compte</label>
                <select name="classe" class="gel-form-select" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #fff; font-size: 14px;">
                    <option value="">Toutes les classes</option>
                    @foreach(range(1,8) as $c)
                        <option value="{{ $c }}" {{ request('classe') == $c ? 'selected' : '' }}>Classe {{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div style="width: 160px;">
                <label style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">Date de fin</label>
                <input type="date" name="date_fin" class="gel-form-control" value="{{ request('date_fin', now()->format('Y-m-d')) }}" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="gel-btn gel-btn-primary" style="padding: 9px 18px; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-filter"></i> Afficher
                </button>
                <a href="{{ route('gel-accountant.comptabilite.balance') }}" class="gel-btn gel-btn-secondary" style="padding: 9px 14px; font-size: 14px;">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Totaux KPI --}}
@if(isset($totalDebit) && isset($totalCredit))
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="gel-card" style="padding: 20px; border-left: 4px solid #3b82f6; background: #fff;">
            <div style="font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Débit</div>
            <div style="font-size: 22px; font-weight: 700; color: #0f172a; margin-top: 6px;">{{ number_format($totalDebit, 0, ',', ' ') }} <span style="font-size: 13px; color: #94a3b8; font-weight: 400;">FCFA</span></div>
        </div>

        <div class="gel-card" style="padding: 20px; border-left: 4px solid #8b5cf6; background: #fff;">
            <div style="font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Crédit</div>
            <div style="font-size: 22px; font-weight: 700; color: #0f172a; margin-top: 6px;">{{ number_format($totalCredit, 0, ',', ' ') }} <span style="font-size: 13px; color: #94a3b8; font-weight: 400;">FCFA</span></div>
        </div>

        <div class="gel-card" style="padding: 20px; border-left: 4px solid {{ $totalDebit === $totalCredit ? '#10b981' : '#ef4444' }}; background: #fff;">
            <div style="font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Statut Équilibre</div>
            <div style="font-size: 16px; font-weight: 700; margin-top: 8px;">
                @if($totalDebit === $totalCredit)
                    <span style="color: #10b981; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-check-circle-fill"></i> Balance équilibrée
                    </span>
                @else
                    <span style="color: #ef4444; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-exclamation-triangle-fill"></i> Écart: {{ number_format(abs($totalDebit - $totalCredit), 0, ',', ' ') }} FCFA
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Tableau de balance --}}
<div class="gel-card" style="background: #fff; border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0;">
    <div class="gel-card-body" style="padding: 0;">
        @if(isset($balanceData) && count($balanceData) > 0)
            <div style="overflow-x: auto;">
                <table class="gel-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; width: 100px;">Code</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Compte</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right; width: 130px;">Débit</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right; width: 130px;">Crédit</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right; width: 130px;">Solde Déb.</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right; width: 130px;">Solde Créd.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balanceData as $row)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 16px; font-weight: 700; font-family: monospace; color: #0f172a; font-size: 13px;">{{ $row['code'] }}</td>
                                <td style="padding: 12px 16px; font-size: 14px; color: #334155; font-weight: 500;">{{ $row['intitule'] }}</td>
                                <td style="padding: 12px 16px; text-align: right; font-size: 13px; color: #475569;">{{ $row['total_debit'] > 0 ? number_format($row['total_debit'], 0, ',', ' ') : '—' }}</td>
                                <td style="padding: 12px 16px; text-align: right; font-size: 13px; color: #475569;">{{ $row['total_credit'] > 0 ? number_format($row['total_credit'], 0, ',', ' ') : '—' }}</td>
                                <td style="padding: 12px 16px; text-align: right; font-size: 13px; font-weight: 600; color: #2563eb;">{{ $row['solde_debit'] > 0 ? number_format($row['solde_debit'], 0, ',', ' ') : '—' }}</td>
                                <td style="padding: 12px 16px; text-align: right; font-size: 13px; font-weight: 600; color: #7c3aed;">{{ $row['solde_credit'] > 0 ? number_format($row['solde_credit'], 0, ',', ' ') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: 700; background: #f8fafc; border-top: 2px solid #cbd5e1; color: #0f172a;">
                            <td colspan="2" style="padding: 14px 16px;">TOTAUX GÉNÉRAUX</td>
                            <td style="padding: 14px 16px; text-align: right;">{{ number_format($totalDebit ?? 0, 0, ',', ' ') }}</td>
                            <td style="padding: 14px 16px; text-align: right;">{{ number_format($totalCredit ?? 0, 0, ',', ' ') }}</td>
                            <td style="padding: 14px 16px; text-align: right; color: #2563eb;">{{ number_format($totalSoldeDebit ?? 0, 0, ',', ' ') }}</td>
                            <td style="padding: 14px 16px; text-align: right; color: #7c3aed;">{{ number_format($totalSoldeCredit ?? 0, 0, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="gel-empty" style="text-align: center; padding: 60px 20px;">
                <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #94a3b8; font-size: 28px;">
                    <i class="bi bi-bar-chart-steps"></i>
                </div>
                <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">Aucune donnée dans la balance</h3>
                <p style="color: #64748b; font-size: 14px; max-width: 400px; margin: 0 auto;">Aucun mouvement comptable n'a été enregistré pour la période et les filtres sélectionnés.</p>
            </div>
        @endif
    </div>
</div>
@endsection
