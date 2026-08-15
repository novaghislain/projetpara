@extends('layouts.gel-secretary')
@section('title', 'Facturation Honoraires Cabinet')

@section('content')
<div style="padding:20px 0;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
        <div>
            <h1 style="font-size:24px; font-weight:800; color:#1e293b; margin:0;">Facturation des Honoraires</h1>
            <p style="color:#64748b; margin:4px 0 0 0;">Facturez vos clients pour vos prestations (secrétariat, comptabilité).</p>
        </div>
        <div>
            <button onclick="document.getElementById('modal-facture').style.display='block'" style="background:#0d9488; color:white; padding:10px 16px; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
                <i class="fas fa-plus"></i> Nouvelle Facture
            </button>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div style="background:white; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">N° Facture</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Client</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Date</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Montant TTC</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Statut</th>
                    <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($factures as $f)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:16px; font-size:14px; font-weight:600; color:#334155;">{{ $f->numero_facture }}</td>
                    <td style="padding:16px; color:#475569; font-size:14px;">{{ $f->client->nom_entreprise ?? 'Inconnu' }}</td>
                    <td style="padding:16px; color:#64748b; font-size:13px;">{{ \Carbon\Carbon::parse($f->date_facture)->format('d/m/Y') }}</td>
                    <td style="padding:16px; font-weight:700; color:#1e293b; font-size:14px;">{{ number_format($f->montant_ttc, 0, ',', ' ') }} CFA</td>
                    <td style="padding:16px;">
                        @if($f->statut == 'emise')
                            <span style="background:#fef3c7; color:#d97706; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">Émise (Non Payée)</span>
                        @elseif($f->statut == 'payee')
                            <span style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">Payée</span>
                        @else
                            <span style="background:#f1f5f9; color:#64748b; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700;">{{ ucfirst($f->statut) }}</span>
                        @endif
                    </td>
                    <td style="padding:16px; text-align:right;">
                        @if($f->statut == 'emise')
                            <form action="{{ route('gel-secretary.cabinet.facturation.pay', $f->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" style="background:#10b981; color:white; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;" onclick="return confirm('Confirmer le paiement ?')">
                                    <i class="fas fa-check"></i> Marquer Payée
                                </button>
                            </form>
                        @endif
                        <button style="background:transparent; border:1px solid #cbd5e1; color:#475569; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;" onclick="alert('Export PDF en construction')">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:40px; text-align:center; color:#94a3b8; font-size:14px;">
                        <i class="fas fa-file-invoice-dollar" style="font-size:32px; margin-bottom:12px; color:#cbd5e1; display:block;"></i>
                        Aucune facture d'honoraire n'a encore été émise.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nouvelle Facture -->
<div id="modal-facture" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; max-width:500px; margin:50px auto; border-radius:12px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
        <h2 style="font-size:18px; margin-top:0; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">Créer une Facture d'Honoraires</h2>
        
        <form action="{{ route('gel-secretary.cabinet.facturation.store') }}" method="POST">
            @csrf
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Client à facturer</label>
                <select name="client_id" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
                    <option value="">Sélectionnez un client...</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->nom_entreprise }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Montant HT (CFA)</label>
                <input type="number" name="montant_ht" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">
                <small style="color:#64748b;">La TVA (18%) sera ajoutée automatiquement.</small>
            </div>
            
            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Notes / Description</label>
                <textarea name="notes" rows="3" placeholder="Prestation de gestion comptable du mois de..." style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; resize:vertical;"></textarea>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="document.getElementById('modal-facture').style.display='none'" style="background:white; border:1px solid #cbd5e1; padding:8px 16px; border-radius:6px; cursor:pointer;">Annuler</button>
                <button type="submit" style="background:#0d9488; color:white; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer;">Émettre Facture</button>
            </div>
        </form>
    </div>
</div>
@endsection
