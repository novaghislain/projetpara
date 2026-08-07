@extends('layouts.gel-secretary')
@section('title', 'Coffre-fort Numérique - Secrétariat')

@section('content')
<div class="pro-header animate-fade">
  <div>
    <div class="pro-title"><i class="fas fa-lock" style="color:#F59E0B; margin-right:8px;"></i> Coffre-fort Numérique</div>
    <div class="pro-subtitle">Accès restreint aux documents confidentiels</div>
  </div>
</div>

<div class="animate-fade delay-1" style="margin-top:24px;">
    <div class="premium-card">
        <div style="padding:20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:15px; font-weight:700; color:var(--sec-text); margin:0;">
                <i class="fas fa-shield-alt" style="color:#10B981; margin-right:8px;"></i> Fichiers Sécurisés ({{ $documents->count() }})
            </h3>
        </div>

        <div style="overflow-x:auto;">
            <table class="sec-table premium-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="width:40%;">Nom du fichier</th>
                        <th>Client</th>
                        <th>Taille</th>
                        <th>Date d'import</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:40px; height:40px; border-radius:10px; background:#FEF2F2; display:flex; align-items:center; justify-content:center; font-size:20px; color:#EF4444;">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:700; color:#1e293b; font-size:14px; display:flex; align-items:center; gap:6px;">
                                            {{ $doc->name }}
                                            <span style="font-size:9px; background:#FEE2E2; color:#EF4444; padding:2px 6px; border-radius:4px; text-transform:uppercase;">Confidentiel</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:13px; font-weight:600; color:#475569;">
                                    {{ $doc->client ? ($doc->client->nom_entreprise ?? $doc->client->nom_entreprise) : 'Cabinet' }}
                                </span>
                            </td>
                            <td style="font-family:monospace; color:#475569; font-size:13px;">
                                {{ $doc->formatted_size }}
                            </td>
                            <td style="color:#64748b; font-size:13px;">
                                <div style="font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">
                                    Créé par {{ $doc->uploadedBy->name ?? 'Système' }}
                                </div>
                                <div>le {{ \Carbon\Carbon::parse($doc->created_at)->format('d/m/Y à H:i') }}</div>
                            </td>
                            <td style="text-align:right;">
                                <button type="button" class="sec-btn sec-btn-primary" onclick="requestVaultAccess({{ $doc->id }}, '{{ addslashes($doc->name) }}')">
                                    <i class="fas fa-eye me-2"></i> Voir
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:#94a3b8;">
                                <i class="fas fa-lock" style="font-size:32px; color:#cbd5e1; margin-bottom:12px; display:block;"></i>
                                Aucun document confidentiel.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function requestVaultAccess(docId, docName) {
    // S12 : Alerte JS (simulation de demande de code d'accès)
    const code = prompt("Accès restreint.\nVeuillez entrer votre code d'accès pour déverrouiller le document : " + docName);
    
    if (code !== null && code.trim() !== "") {
        // Redirection vers le téléchargement/vue après "validation"
        window.open("/gel-secretary/documents/view/" + docId, "_blank");
    } else if (code !== null) {
        alert("Code invalide ou vide. Accès refusé.");
    }
}
</script>

<style>
  /* Premium Table (Même style que folder) */
  .premium-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid var(--sec-border);
    overflow: hidden;
  }
  .premium-table th {
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 20px;
    border-bottom: 2px solid #e2e8f0;
  }
  .premium-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
  }
  .premium-table tr:last-child td { border-bottom: none; }
  .premium-table tr:hover td { background: #f8fafc; }
</style>
@endsection
