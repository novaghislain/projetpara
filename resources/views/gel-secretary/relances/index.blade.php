@extends('layouts.gel-secretary')
@section('title', 'Relances Automatiques — Secrétariat')
@section('content')

<div class="sec-page-header">
    <div>
        <h1 class="sec-page-title"><i class="fas fa-bell" style="color:#EF4444; margin-right:8px;"></i>Centre de Relances</h1>
        <p class="sec-page-sub">Factures et tâches en retard nécessitant une action vers le client</p>
    </div>
</div>

@if(session('success'))
<div style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:500;">
    <i class="fas fa-check-circle" style="margin-right:8px;"></i> {{ session('success') }}
</div>
@endif

<div class="sec-grid" style="grid-template-columns: 1fr;">

    <!-- Factures Echues -->
    <div class="sec-card">
        <div class="sec-card-header" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 16px;">
            <div class="sec-card-title">
                <i class="fas fa-file-invoice-dollar" style="color:var(--sec-text-muted);"></i> Factures Échues ({{ $overdueInvoices->count() }})
            </div>
        </div>

        @if($overdueInvoices->isEmpty())
            <div style="padding:48px 20px; text-align:center; background:#F8FAFC; border-radius:8px; border: 1px dashed #CBD5E1; margin: 16px;">
                <div style="width:64px; height:64px; background:#D1FAE5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <i class="fas fa-check" style="font-size:28px; color:#10B981;"></i>
                </div>
                <h3 style="font-size:16px; font-weight:700; color:#334155; margin:0 0 6px 0;">Zéro facture en retard !</h3>
                <p style="color:#64748B; font-size:14px; margin:0;">Tous vos clients sont à jour dans leurs paiements.</p>
            </div>
        @else
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th>Échéance</th>
                        <th>Montant dû</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueInvoices as $inv)
                        @php
                            $daysOverdue = \Carbon\Carbon::parse($inv->due_date)->diffInDays(now());
                        @endphp
                        <tr>
                            <td><strong>{{ $inv->invoice_number }}</strong></td>
                            <td>{{ $inv->client->company_name ?? 'Inconnu' }}</td>
                            <td>
                                <span style="color:#DC2626; font-weight:600;">{{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }}</span>
                                <span style="font-size:12px; color:#DC2626; background:#FEF2F2; padding:2px 6px; border-radius:4px; margin-left:8px;">+{{ $daysOverdue }} jours</span>
                            </td>
                            <td style="font-weight:600;">{{ number_format($inv->balance_due, 2, ',', ' ') }} €</td>
                            <td style="text-align:right;">
                                <form action="{{ route('gel-secretary.relances.send') }}" method="POST" style="margin:0; display:inline-block;" class="form-relance-ajax">
                                    @csrf
                                    <input type="hidden" name="type" value="invoice">
                                    <input type="hidden" name="id" value="{{ $inv->id }}">
                                    <button type="submit" class="sec-btn" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA;">
                                        <i class="fas fa-bell"></i> 1-clic
                                    </button>
                                </form>
                                <button type="button" class="sec-btn btn-ai-draft" data-type="invoice" data-id="{{ $inv->id }}" style="background:#F0FDFA; color:#0D9488; border:1px solid #99F6E4; margin-left:8px;">
                                    <i class="fas fa-sparkles"></i> IA
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Tâches Echues -->
    <div class="sec-card">
        <div class="sec-card-header" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 16px;">
            <div class="sec-card-title">
                <i class="fas fa-tasks" style="color:var(--sec-text-muted);"></i> Tâches Critiques en Attente ({{ $overdueTasks->count() }})
            </div>
        </div>

        @if($overdueTasks->isEmpty())
            <div style="padding:48px 20px; text-align:center; background:#F8FAFC; border-radius:8px; border: 1px dashed #CBD5E1; margin: 16px;">
                <div style="width:64px; height:64px; background:#D1FAE5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                    <i class="fas fa-check" style="font-size:28px; color:#10B981;"></i>
                </div>
                <h3 style="font-size:16px; font-weight:700; color:#334155; margin:0 0 6px 0;">Tout est sous contrôle !</h3>
                <p style="color:#64748B; font-size:14px; margin:0;">Aucune tâche n'est en retard.</p>
            </div>
        @else
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Tâche</th>
                        <th>Client</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overdueTasks as $task)
                        @php
                            $daysOverdue = \Carbon\Carbon::parse($task->date_echeance)->diffInDays(now());
                        @endphp
                        <tr>
                            <td><strong>{{ $task->titre }}</strong></td>
                            <td>{{ $task->client->company_name ?? 'Interne' }}</td>
                            <td>
                                <span style="color:#DC2626; font-weight:600;">{{ \Carbon\Carbon::parse($task->date_echeance)->format('d/m/Y') }}</span>
                                <span style="font-size:12px; color:#DC2626; background:#FEF2F2; padding:2px 6px; border-radius:4px; margin-left:8px;">+{{ $daysOverdue }} jours</span>
                            </td>
                            <td>
                                <span class="badge-sm" style="background:#F1F5F9; color:#475569;">{{ ucfirst(str_replace('_', ' ', $task->statut)) }}</span>
                            </td>
                                <div class="dropdown" style="display:inline-block;">
                                    <button class="sec-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA;">
                                        <i class="fas fa-bell"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 13px;">
                                        <li>
                                            <form action="{{ route('gel-secretary.relances.send') }}" method="POST" class="form-relance-ajax m-0 p-0">
                                                @csrf
                                                <input type="hidden" name="type" value="task">
                                                <input type="hidden" name="id" value="{{ $task->id }}">
                                                <button type="submit" class="dropdown-item"><i class="fas fa-envelope text-primary me-2"></i> par Email 1-clic</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                                <button type="button" class="sec-btn btn-ai-draft" data-type="task" data-id="{{ $task->id }}" style="background:#F0FDFA; color:#0D9488; border:1px solid #99F6E4; margin-left:8px;">
                                    <i class="fas fa-sparkles"></i> IA
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Relances des devis -->
    <div class="sec-card" style="margin-top: 16px;">
        <div class="sec-card-header" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 16px;">
            <div class="sec-card-title">
                <i class="fas fa-file-invoice" style="color:var(--sec-primary);"></i> Relances des devis ({{ $pendingQuotes->count() }})
            </div>
        </div>

        @if($pendingQuotes->isEmpty())
            <div style="padding:30px 20px; text-align:center; background:#F8FAFC; border-radius:8px; border: 1px dashed #CBD5E1; margin: 0 16px 16px;">
                <p style="color:#64748B; font-size:14px; margin:0;">Aucun devis en attente de réponse depuis plus de 7 jours.</p>
            </div>
        @else
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>N° Devis</th>
                        <th>Client</th>
                        <th>Date d'envoi</th>
                        <th>Montant</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingQuotes as $quote)
                        @php $daysPending = \Carbon\Carbon::parse($quote->created_at)->diffInDays(now()); @endphp
                        <tr>
                            <td><strong>{{ $quote->invoice_number }}</strong></td>
                            <td>{{ $quote->client->company_name ?? 'Inconnu' }}</td>
                            <td>
                                <span>{{ \Carbon\Carbon::parse($quote->created_at)->format('d/m/Y') }}</span>
                                <span style="font-size:12px; color:#D97706; background:#FFFBEB; padding:2px 6px; border-radius:4px; margin-left:8px;">+{{ $daysPending }} jours</span>
                            </td>
                            <td style="font-weight:600;">{{ number_format($quote->total, 2, ',', ' ') }} €</td>
                            <td style="text-align:right;">
                                <button type="button" class="sec-btn btn-ai-draft" data-type="quote" data-id="{{ $quote->id }}" style="background:#F0FDFA; color:#0D9488; border:1px solid #99F6E4;">
                                    <i class="fas fa-sparkles"></i> Relancer avec l'IA
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Relances contrats -->
    <div class="sec-card" style="margin-top: 16px;">
        <div class="sec-card-header" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 16px;">
            <div class="sec-card-title">
                <i class="fas fa-file-signature" style="color:#10B981;"></i> Contrats à renouveler ({{ $expiringContracts->count() }})
            </div>
        </div>

        @if($expiringContracts->isEmpty())
            <div style="padding:30px 20px; text-align:center; background:#F8FAFC; border-radius:8px; border: 1px dashed #CBD5E1; margin: 0 16px 16px;">
                <p style="color:#64748B; font-size:14px; margin:0;">Aucun contrat n'expire dans les 30 prochains jours.</p>
            </div>
        @else
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Type de contrat</th>
                        <th>Date d'expiration</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expiringContracts as $client)
                        @php $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($client->contract_end), false); @endphp
                        <tr>
                            <td><strong>{{ $client->company_name }}</strong></td>
                            <td>{{ ucfirst($client->contract_type ?? 'Standard') }}</td>
                            <td>
                                <span>{{ \Carbon\Carbon::parse($client->contract_end)->format('d/m/Y') }}</span>
                                <span style="font-size:12px; color:#D97706; background:#FFFBEB; padding:2px 6px; border-radius:4px; margin-left:8px;">J-{{ abs(intval($daysLeft)) }}</span>
                            </td>
                            <td style="text-align:right;">
                                <button type="button" class="sec-btn btn-ai-draft" data-type="contract" data-id="{{ $client->id }}" style="background:#F0FDFA; color:#0D9488; border:1px solid #99F6E4;">
                                    <i class="fas fa-sparkles"></i> Proposer RDV avec l'IA
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Clients inactifs -->
    <div class="sec-card" style="margin-top: 16px;">
        <div class="sec-card-header" style="border-bottom: 1px solid var(--sec-border); padding-bottom: 12px; margin-bottom: 16px;">
            <div class="sec-card-title">
                <i class="fas fa-user-clock" style="color:#F59E0B;"></i> Clients inactifs ({{ $inactiveClients->count() }})
            </div>
        </div>

        @if($inactiveClients->isEmpty())
            <div style="padding:30px 20px; text-align:center; background:#F8FAFC; border-radius:8px; border: 1px dashed #CBD5E1; margin: 0 16px 16px;">
                <p style="color:#64748B; font-size:14px; margin:0;">Tous vos clients ont eu une activité récente.</p>
            </div>
        @else
            <table class="sec-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Secteur</th>
                        <th>Dernière activité</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inactiveClients as $client)
                        @php $monthsInactive = \Carbon\Carbon::parse($client->updated_at)->diffInMonths(now()); @endphp
                        <tr>
                            <td><strong>{{ $client->company_name }}</strong></td>
                            <td>{{ $client->secteur ?? '—' }}</td>
                            <td>
                                <span>{{ \Carbon\Carbon::parse($client->updated_at)->format('d/m/Y') }}</span>
                                <span style="font-size:12px; color:#DC2626; background:#FEF2F2; padding:2px 6px; border-radius:4px; margin-left:8px;">+{{ $monthsInactive }} mois</span>
                            </td>
                            <td style="text-align:right;">
                                <button type="button" class="sec-btn btn-ai-draft" data-type="inactive" data-id="{{ $client->id }}" style="background:#F0FDFA; color:#0D9488; border:1px solid #99F6E4;">
                                    <i class="fas fa-sparkles"></i> IA : Prendre des nouvelles
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Autres Catégories (statiques) -->
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; margin-top: 16px;">
        <!-- Échéances fiscales -->
        <div class="sec-card" style="padding:16px; cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0; font-size:15px; font-weight:600;"><i class="fas fa-landmark text-primary me-2"></i> Échéances fiscales</h4>
                <span class="badge bg-warning text-dark">3 proches</span>
            </div>
            <p style="font-size:12px; color:var(--sec-text-muted); margin-top:8px; margin-bottom:0;">TVA, Liasses fiscales et autres déclarations.</p>
        </div>
        
        <!-- Assurances à renouveler -->
        <div class="sec-card" style="padding:16px; cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0; font-size:15px; font-weight:600;"><i class="fas fa-shield-alt text-success me-2"></i> Assurances à renouveler</h4>
                <span class="badge bg-secondary">0 en attente</span>
            </div>
            <p style="font-size:12px; color:var(--sec-text-muted); margin-top:8px; margin-bottom:0;">Responsabilité civile, multirisques pro.</p>
        </div>
        
        <!-- Licences logicielles -->
        <div class="sec-card" style="padding:16px; cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0; font-size:15px; font-weight:600;"><i class="fas fa-laptop-code text-info me-2"></i> Licences logicielles</h4>
                <span class="badge bg-secondary">0 en attente</span>
            </div>
            <p style="font-size:12px; color:var(--sec-text-muted); margin-top:8px; margin-bottom:0;">Abonnements SaaS expirant bientôt.</p>
        </div>
        
        <!-- Rendez-vous oubliés -->
        <div class="sec-card" style="padding:16px; cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0; font-size:15px; font-weight:600;"><i class="fas fa-calendar-times text-danger me-2"></i> RDV oubliés</h4>
                <span class="badge bg-secondary">0 en attente</span>
            </div>
            <p style="font-size:12px; color:var(--sec-text-muted); margin-top:8px; margin-bottom:0;">Clients ne s'étant pas présentés.</p>
        </div>
        
        <!-- Paiements attendus -->
        <div class="sec-card" style="padding:16px; cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0; font-size:15px; font-weight:600;"><i class="fas fa-money-check-alt text-info me-2"></i> Paiements attendus</h4>
                <span class="badge bg-secondary">0 en attente</span>
            </div>
            <p style="font-size:12px; color:var(--sec-text-muted); margin-top:8px; margin-bottom:0;">Promesses de règlement à vérifier.</p>
        </div>
    </div>

</div>

<!-- Modal IA Draft -->
<div class="modal fade" id="aiDraftModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-sparkles" style="color:#0D9488;"></i> Rédiger avec l'IA</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="aiDraftLoading" style="display:none; text-align:center; padding: 20px;">
            <i class="fas fa-spinner fa-spin fa-2x" style="color:#0D9488;"></i>
            <p style="margin-top:10px; color:var(--sec-text-muted);">L'IA rédige votre message...</p>
        </div>
        <div id="aiDraftContent" style="display:none;">
            <form id="aiDraftForm" method="POST" action="{{ route('gel-secretary.relances.send') }}">
                @csrf
                <input type="hidden" name="type" id="aiDraftType">
                <input type="hidden" name="id" id="aiDraftId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Message généré :</label>
                    <textarea class="form-control" name="message" id="aiDraftTextarea" rows="8"></textarea>
                    <div class="form-text">Vous pouvez modifier ce brouillon avant de l'envoyer.</div>
                </div>
            </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="aiDraftSubmit" style="background:#0D9488; border:none; display:none;">
            <i class="fas fa-paper-plane"></i> Envoyer la relance
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const relanceForms = document.querySelectorAll('.form-relance-ajax');
    
    relanceForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = form.querySelector('button[type="submit"]');
            const originalHtml = btn.innerHTML;
            const originalBg = btn.style.background;
            const originalColor = btn.style.color;
            
            // Set loading state
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
            btn.style.opacity = '0.7';
            btn.disabled = true;
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok || response.redirected) {
                    // Success visual feedback
                    btn.innerHTML = '<i class="fas fa-check"></i> Relancé';
                    btn.style.background = '#ECFDF5';
                    btn.style.color = '#10B981';
                    btn.style.borderColor = '#A7F3D0';
                    btn.style.opacity = '1';
                    
                    // Show a toast if function exists, else alert
                    if (typeof showToast === 'function') {
                        showToast('Relance envoyée avec succès !', 'success');
                    } else {
                        // Fallback toast creation if showToast isn't globally available
                        const toast = document.createElement('div');
                        toast.className = 'sec-toast success';
                        toast.innerHTML = `
                            <div class="sec-toast-icon-wrapper"><i class="fas fa-check"></i></div>
                            <div class="sec-toast-content">
                                <div class="sec-toast-message">Relance envoyée avec succès !</div>
                            </div>
                            <div class="sec-toast-progress"></div>
                        `;
                        document.getElementById('sec-toast-container')?.appendChild(toast) || document.body.appendChild(toast);
                        setTimeout(() => {
                            toast.classList.add('exit');
                            setTimeout(() => toast.remove(), 300);
                        }, 3000);
                    }
                    
                } else {
                    throw new Error('Erreur réseau');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                btn.innerHTML = originalHtml;
                btn.style.background = originalBg;
                btn.style.color = originalColor;
                btn.style.opacity = '1';
                btn.disabled = false;
                
                secToast('Erreur lors de l\'envoi de la relance.', 'error');
            });
        });
    });

    // IA Draft Logic
    const draftModal = new bootstrap.Modal(document.getElementById('aiDraftModal'));
    const draftLoading = document.getElementById('aiDraftLoading');
    const draftContent = document.getElementById('aiDraftContent');
    const draftTextarea = document.getElementById('aiDraftTextarea');
    const draftSubmit = document.getElementById('aiDraftSubmit');
    const draftForm = document.getElementById('aiDraftForm');

    document.querySelectorAll('.btn-ai-draft').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            const id = this.getAttribute('data-id');
            
            document.getElementById('aiDraftType').value = type;
            document.getElementById('aiDraftId').value = id;
            
            draftLoading.style.display = 'block';
            draftContent.style.display = 'none';
            draftSubmit.style.display = 'none';
            
            draftModal.show();
            
            const formData = new FormData();
            formData.append('type', type);
            formData.append('id', id);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            fetch("{{ route('gel-secretary.relances.draft') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                draftLoading.style.display = 'none';
                draftContent.style.display = 'block';
                draftSubmit.style.display = 'block';
                draftTextarea.value = data.draft;
            })
            .catch(err => {
                console.error(err);
                draftLoading.style.display = 'none';
                secToast("Erreur lors de la génération IA.", "error");
                draftModal.hide();
            });
        });
    });

    draftSubmit.addEventListener('click', function() {
        const originalHtml = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
        this.disabled = true;
        
        // Simuler la soumission normale qui recharge la page avec le flash message success
        draftForm.submit();
    });
});
</script>
@endsection
