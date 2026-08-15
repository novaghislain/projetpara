@extends('layouts.gel-accountant')

@section('title', 'Dépenses & Achats')

@push('styles')
<style>
/* ==========================================================================
   EXPENSES - DESIGN
   ========================================================================== */
.stats-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px;
}
.stat-card {
    background: white; border: 1px solid var(--gel-border); border-radius: 12px;
    padding: 20px; display: flex; align-items: center; gap: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}
.stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
}
.stat-icon.primary { background: #E0F2FE; color: #0284C7; }
.stat-icon.warning { background: #FEF9C3; color: #CA8A04; }
.stat-icon.danger { background: #FEE2E2; color: #DC2626; }
.stat-icon.success { background: #DCFCE7; color: #16A34A; }

.stat-info h3 { margin: 0; font-size: 20px; font-weight: 700; color: #1E293B; }
.stat-info p { margin: 0; font-size: 13px; color: #64748B; font-weight: 600; text-transform: uppercase; }

.table-container {
    background: white; border: 1px solid var(--gel-border);
    border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); overflow: hidden;
}
.filters-bar { padding: 16px 20px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; background: #F8FAFC; }
.filters-left { display: flex; gap: 12px; align-items: center; }
.form-select-sm, .form-control-sm { border: 1px solid #E2E8F0; border-radius: 6px; padding: 6px 12px; font-size: 13px; outline: none; }
.form-select-sm:focus, .form-control-sm:focus { border-color: var(--gel-primary); }

.gel-table { width: 100%; border-collapse: collapse; }
.gel-table th { background: white; padding: 12px 20px; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; border-bottom: 2px solid #E2E8F0; }
.gel-table td { padding: 12px 20px; border-bottom: 1px solid #F1F5F9; font-size: 13px; color: #1E293B; vertical-align: middle; }
.gel-table tr:hover { background: #F8FAFC; }

.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.status-unpaid { background: #FEF9C3; color: #CA8A04; }
.status-paid { background: #ECFDF5; color: #10B981; }
.status-disputed { background: #FEF2F2; color: #EF4444; }

.btn-action { background: white; border: 1px solid #E2E8F0; color: #64748B; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; font-size: 12px; transition: all 0.2s; }
.btn-action:hover { background: #F1F5F9; color: var(--gel-primary); border-color: #CBD5E1; }

.btn-primary-action { background: var(--gel-primary); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; }
.btn-primary-action:hover { background: var(--gel-primary-hover); transform: translateY(-1px); }
.btn-secondary-action { background: white; color: var(--gel-text-primary); border: 1px solid var(--gel-border); padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; }
.btn-secondary-action:hover { background: #F8FAFC; border-color: #CBD5E1; }

/* Modal Styles */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px);
    z-index: 2000; display: none; align-items: center; justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal-content {
    background: white; width: 100%; max-width: 700px;
    border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex; flex-direction: column; max-height: 90vh;
}
.modal-header {
    padding: 20px 24px; border-bottom: 1px solid #E2E8F0;
    display: flex; justify-content: space-between; align-items: center;
}
.modal-header h3 { margin: 0; font-size: 18px; font-weight: 700; color: #1E293B; }
.btn-close {
    background: none; border: none; font-size: 20px; color: #94A3B8;
    cursor: pointer; width: 32px; height: 32px; display: flex;
    align-items: center; justify-content: center; border-radius: 50%;
}
.btn-close:hover { background: #F1F5F9; color: #475569; }
.modal-body { padding: 24px; overflow-y: auto; flex: 1; }
.modal-footer { padding: 16px 24px; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 12px; background: #F8FAFC; border-radius: 0 0 12px 12px; }

.ocr-box {
    border: 2px dashed #CBD5E1; border-radius: 8px; padding: 24px; text-align: center;
    background: #F8FAFC; cursor: pointer; transition: all 0.2s; margin-bottom: 24px;
}
.ocr-box:hover { border-color: var(--gel-primary); background: #EFF6FF; }
.ocr-box i { font-size: 32px; color: var(--gel-primary); margin-bottom: 12px; }
.ocr-title { font-weight: 600; color: #1E293B; margin-bottom: 4px; }
.ocr-subtitle { font-size: 13px; color: #64748B; }

.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
.form-control, .form-select {
    width: 100%; padding: 10px 14px; border: 1px solid #E2E8F0;
    border-radius: 8px; font-size: 14px; outline: none; background: #F8FAFC;
}
.form-control:focus, .form-select:focus { background: white; border-color: var(--gel-primary); }

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
</style>
@endpush

@section('content')
<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title">Dépenses & Achats</h1>
        <div class="gel-page-subtitle">Gérez vos factures fournisseurs et notes de frais.</div>
    </div>
    <div style="display:flex; gap:12px;">
        <button class="btn-secondary-action" onclick="openExpenseModal(true)">
            <i class="fas fa-magic"></i> Scan IA (OCR)
        </button>
        <button class="btn-primary-action" onclick="openExpenseModal(false)">
            <i class="fas fa-plus"></i> Saisir une dépense
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; border-radius: 8px; padding:12px 16px; margin-bottom:20px;">
    <i class="fas fa-check-circle me-2" style="font-size:18px;"></i>
    {{ session('success') }}
</div>
@endif

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-wallet"></i></div>
        <div class="stat-info">
            <p>Total Dépenses (Mois)</p>
            <h3>{{ number_format($expenses->sum('total'), 0, ',', ' ') }} F</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <p>À Payer</p>
            <h3>{{ number_format($expenses->sum('balance_due'), 0, ',', ' ') }} F</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-info">
            <p>Non payées</p>
            <h3>{{ $expenses->where('status', 'unpaid')->count() }}</h3>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="filters-bar">
        <div class="filters-left">
            <input type="text" class="form-control-sm" placeholder="Rechercher (N°, Fournisseur)..." style="width:250px;">
            <select class="form-select-sm">
                <option value="">Tous les statuts</option>
                <option value="unpaid">Non payé</option>
                <option value="paid">Payé</option>
            </select>
            <button class="btn-secondary-action" style="padding:6px 12px;"><i class="fas fa-filter"></i> Filtrer</button>
        </div>
    </div>

    <table class="gel-table">
        <thead>
            <tr>
                <th>N° Dépense</th>
                <th>Date</th>
                <th>Fournisseur</th>
                <th class="text-end">Total TTC</th>
                <th class="text-end">Reste à Payer</th>
                <th class="text-center">Statut</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td style="font-weight:600; color:var(--gel-primary);">{{ $expense->invoice_number }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->invoice_date)->format('d/m/Y') }}</td>
                <td>{{ $expense->partner->company_name ?? ($expense->partner->first_name . ' ' . $expense->partner->last_name) ?? 'N/A' }}</td>
                <td class="text-end font-monospace">{{ number_format($expense->total, 0, ',', ' ') }} F</td>
                <td class="text-end font-monospace" style="color:{{ $expense->balance_due > 0 ? '#EF4444' : '#10B981' }}">{{ number_format($expense->balance_due, 0, ',', ' ') }} F</td>
                <td class="text-center">
                    @if($expense->status == 'unpaid') <span class="status-badge status-unpaid">À PAYER</span>
                    @elseif($expense->status == 'paid') <span class="status-badge status-paid">PAYÉ</span>
                    @elseif($expense->status == 'disputed') <span class="status-badge status-disputed">LITIGE</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('gel-accountant.expenses.show', $expense->id) }}" class="btn-action" title="Détails"><i class="fas fa-eye"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="text-center" style="padding: 40px 20px; color: #94A3B8;">
                        <i class="fas fa-wallet" style="font-size: 32px; margin-bottom: 12px; opacity:0.5;"></i>
                        <div>Aucune dépense enregistrée.</div>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($expenses->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid #E2E8F0; display:flex; justify-content:flex-end;">
        {{ $expenses->links() }}
    </div>
    @endif
</div>

<!-- Modal Nouvelle Dépense -->
<div class="modal-overlay" id="expenseModal">
    <div class="modal-content">
        <form action="{{ route('gel-accountant.expenses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h3 id="modalTitle">Saisir une Dépense</h3>
                <button type="button" class="btn-close" onclick="closeExpenseModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                
                <div id="ocrSection" style="display:none;">
                    <div class="ocr-box" onclick="document.getElementById('ocrFile').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div class="ocr-title">Cliquez ou glissez une facture fournisseur</div>
                        <div class="ocr-subtitle">Formats supportés : PDF, JPG, PNG. Max 5Mo.</div>
                        <input type="file" id="ocrFile" name="attachment" style="display:none;" onchange="handleOCRScan(this)">
                    </div>
                    <div id="ocrLoader" style="display:none; text-align:center; margin-bottom:24px; color:var(--gel-primary);">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <div style="margin-top:8px; font-weight:600;">L'IA analyse le document...</div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                        <select name="partner_id" class="form-select" id="partner_id" required>
                            <option value="">Sélectionner ou Créer...</option>
                            <!-- Rempli dynamiquement ou laisser le controller gérer -->
                            <option value="1">Fournisseur Divers</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Compte de paiement <span class="text-danger">*</span></label>
                        <select name="payment_account" class="form-select" required>
                            <option value="521000">Banque Principale</option>
                            <option value="571000">Caisse</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" id="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Référence / N° Facture FRS</label>
                        <input type="text" name="reference" id="reference" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <option value="paid">Payée</option>
                            <option value="unpaid">À Payer</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 16px;">
                    <label class="form-label">Ligne d'achat <span class="text-danger">*</span></label>
                    <div style="display:flex; gap:12px; align-items:flex-start;">
                        <input type="text" name="lines[0][description]" id="line_desc" class="form-control" placeholder="Description de l'achat" required style="flex:2;">
                        <input type="text" name="lines[0][account_id]" id="line_account" class="form-control" placeholder="Compte de charge (ex: 601)" required style="flex:1;">
                        <input type="number" name="lines[0][amount]" id="line_amount" class="form-control" placeholder="Montant HT" required min="0" step="0.01" style="flex:1;">
                        <select name="lines[0][tax_rate]" id="line_tax" class="form-select" style="flex:1;">
                            <option value="18">TVA 18%</option>
                            <option value="0">Exonéré 0%</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top:20px;">
                    <label class="form-label">Mémo / Note</label>
                    <textarea name="memo" class="form-control" rows="2"></textarea>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-action" onclick="closeExpenseModal()">Annuler</button>
                <button type="submit" class="btn-primary-action"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openExpenseModal(withOCR) {
        document.getElementById('expenseModal').classList.add('active');
        if(withOCR) {
            document.getElementById('ocrSection').style.display = 'block';
            document.getElementById('modalTitle').innerText = 'Scanner une Facture Fournisseur';
        } else {
            document.getElementById('ocrSection').style.display = 'none';
            document.getElementById('modalTitle').innerText = 'Saisir une Dépense';
        }
    }
    
    function closeExpenseModal() {
        document.getElementById('expenseModal').classList.remove('active');
    }

    function handleOCRScan(input) {
        if(!input.files || input.files.length === 0) return;
        
        let formData = new FormData();
        formData.append('document', input.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        document.getElementById('ocrLoader').style.display = 'block';

        fetch("{{ route('gel-accountant.expenses.ocrScan') }}", {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('ocrLoader').style.display = 'none';
            if(data.success && data.data) {
                // Auto-fill form fields
                if(data.data.date) document.getElementById('expense_date').value = data.data.date;
                if(data.data.amount_ht) document.getElementById('line_amount').value = data.data.amount_ht;
                if(data.data.description) document.getElementById('line_desc').value = data.data.description;
                if(data.data.account) document.getElementById('line_account').value = data.data.account;
                if(data.data.tva_rate !== undefined) document.getElementById('line_tax').value = data.data.tva_rate;
                
                alert('Analyse OCR réussie ! Les champs ont été pré-remplis (Fournisseur identifié : ' + data.data.vendor_name + ').');
            }
        })
        .catch(error => {
            document.getElementById('ocrLoader').style.display = 'none';
            alert('Erreur lors de l\'analyse OCR.');
        });
    }
</script>
@endpush
@endsection
