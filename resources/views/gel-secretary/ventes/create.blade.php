@extends('layouts.gel-secretary')
@section('title', 'Créer une Facture')

@push('styles')
<style>
/* ==========================================================================
   VENTES CREATE - BENTO GRID DESIGN
   ========================================================================== */
.form-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.bento-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05),
                inset 0 0 0 1px rgba(255, 255, 255, 0.5);
}

.card-header { font-size: 18px; font-weight: 800; color: #1E293B; margin-bottom: 24px; display:flex; align-items:center; gap:12px;}

.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 13px; font-weight: 700; color: #64748B; margin-bottom: 8px; text-transform:uppercase; letter-spacing:0.5px;}
.form-control {
    width: 100%; background: #F8FAFC; border: 1px solid #E2E8F0;
    border-radius: 12px; padding: 12px 16px; outline: none;
    font-size: 14px; font-family: inherit; transition: all 0.2s;
}
.form-control:focus { background: white; border-color: #0D9488; box-shadow: 0 0 0 4px rgba(13,148,136,0.1); }

.row { display: flex; gap: 20px; }
.col { flex: 1; }

/* Table Lignes de facture */
.lines-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
.lines-table th { font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; text-align: left; padding: 12px; border-bottom: 2px solid #E2E8F0; }
.lines-table td { padding: 12px; border-bottom: 1px solid #E2E8F0; vertical-align: top;}
.lines-table input, .lines-table select { width: 100%; padding: 8px 12px; border: 1px solid #E2E8F0; border-radius: 8px; outline: none; font-family: inherit; }

.btn-add-line { background: #EFF6FF; color: #3B82F6; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
.btn-add-line:hover { background: #DBEAFE; }

.btn-remove-line { background: #FEF2F2; color: #EF4444; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
.btn-remove-line:hover { background: #FEE2E2; }

/* Totals Area */
.totals-area { background: #F8FAFC; border-radius: 16px; padding: 24px; text-align: right; margin-top: 24px;}
.total-line { display: flex; justify-content: flex-end; align-items: center; gap: 40px; margin-bottom: 12px; font-size: 15px; color: #475569;}
.total-line.ttc { font-size: 22px; font-weight: 800; color: #1E293B; border-top: 2px solid #E2E8F0; padding-top: 16px; margin-top: 8px; margin-bottom: 0;}
.total-val { font-weight: 700; min-width: 120px; }

/* Buttons */
.btn-submit { background: #0D9488; color: white; border: none; padding: 14px 28px; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; transition: all 0.2s; display:inline-flex; align-items:center; gap:8px;}
.btn-submit:hover { background: #0F766E; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(13, 148, 136, 0.3);}
.btn-back { background: white; color: #1E293B; border: 1px solid #E2E8F0; padding: 14px 28px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration:none; display:inline-flex; align-items:center; gap:8px;}
.btn-back:hover { background: #F8FAFC; }

.stagger-1 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.1s;}
.stagger-2 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.2s;}
.stagger-3 { animation: fadeUp 0.4s ease-out forwards; opacity: 0; animation-delay: 0.3s;}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:32px;" class="stagger-1">
    <div>
        <h1 style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">Créer une Facture</h1>
        <div style="font-size:14px; color:#64748B; margin-top:4px;">Facturez les produits et services à vos clients.</div>
    </div>
    <div>
        <a href="{{ route('gel-secretary.clients.ventes.index') }}" class="btn-back"><i class="fas fa-arrow-left"></i> Retour aux factures</a>
    </div>
</div>

<form action="{{ route('gel-secretary.clients.ventes.store') }}" method="POST" id="factureForm">
    @csrf
    <input type="hidden" name="client_id" value="{{ $clientId }}">

    <div class="form-grid">
        <!-- Informations Générales -->
        <div class="bento-card stagger-2" style="grid-column: span 12;">
            <div class="card-header"><i class="fas fa-file-invoice" style="color:#0D9488;"></i> Informations Générales</div>
            
            <div class="row">
                <div class="col form-group">
                    <label class="form-label">Client (Contact) <span style="color:#EF4444;">*</span></label>
                    <select name="contact_id" class="form-control" required>
                        <option value="">Sélectionnez un contact...</option>
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->email ?? 'Pas d\'email' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col form-group">
                    <label class="form-label">Numéro de facture <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="numero" class="form-control" value="{{ $numero }}" required readonly>
                </div>
                <div class="col form-group">
                    <label class="form-label">Date de facturation <span style="color:#EF4444;">*</span></label>
                    <input type="date" name="date_facture" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col form-group">
                    <label class="form-label">Date d'échéance</label>
                    <input type="date" name="date_echeance" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                </div>
            </div>
        </div>

        <!-- Lignes de Facture -->
        <div class="bento-card stagger-3" style="grid-column: span 12;">
            <div class="card-header"><i class="fas fa-list" style="color:#3B82F6;"></i> Lignes de Facturation</div>
            
            <table class="lines-table" id="linesTable">
                <thead>
                    <tr>
                        <th style="width:25%;">Produit / Service (Optionnel)</th>
                        <th style="width:35%;">Désignation <span style="color:#EF4444;">*</span></th>
                        <th style="width:10%;">Quantité <span style="color:#EF4444;">*</span></th>
                        <th style="width:15%;">Prix Unitaire (HT) <span style="color:#EF4444;">*</span></th>
                        <th style="width:10%;">Total HT</th>
                        <th style="width:5%;"></th>
                    </tr>
                </thead>
                <tbody id="linesContainer">
                    <!-- Initial Line -->
                    <tr class="facture-line">
                        <td>
                            <select name="lignes[0][produit_id]" class="product-select" onchange="autoFillLine(this)">
                                <option value="">Saisie libre...</option>
                                @foreach($produits as $p)
                                    <option value="{{ $p->id }}" data-nom="{{ $p->nom }}" data-prix="{{ $p->prix_ht }}">{{ $p->nom }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" name="lignes[0][designation]" class="designation-input" required placeholder="Description de l'article..."></td>
                        <td><input type="number" name="lignes[0][quantite]" class="qty-input" value="1" min="1" step="0.01" required oninput="calculateLineTotals()"></td>
                        <td><input type="number" name="lignes[0][prix_unitaire]" class="price-input" value="0" min="0" step="0.01" required oninput="calculateLineTotals()"></td>
                        <td><input type="text" class="linetotal-input" value="0.00" readonly style="background:#F8FAFC; border:none; font-weight:700;"></td>
                        <td><button type="button" class="btn-remove-line" onclick="removeLine(this)" disabled><i class="fas fa-times"></i></button></td>
                    </tr>
                </tbody>
            </table>
            
            <button type="button" class="btn-add-line" onclick="addLine()"><i class="fas fa-plus"></i> Ajouter une ligne</button>

            <!-- Totaux -->
            <div class="totals-area">
                <div class="total-line">
                    <div>Total HT</div>
                    <div class="total-val" id="totalHt">0.00 FCFA</div>
                </div>
                <div class="total-line">
                    <div>TVA (18%)</div>
                    <div class="total-val" id="totalTva">0.00 FCFA</div>
                </div>
                <div class="total-line ttc">
                    <div>Total TTC</div>
                    <div class="total-val" id="totalTtc">0.00 FCFA</div>
                </div>
            </div>
            
            <!-- Notes -->
            <div class="form-group" style="margin-top:24px;">
                <label class="form-label">Notes & Conditions</label>
                <textarea name="notes" class="form-control" style="min-height:80px;" placeholder="Conditions de paiement, RIB, mentions légales..."></textarea>
            </div>
        </div>

        <!-- Actions -->
        <div class="stagger-3" style="grid-column: span 12; display:flex; justify-content:flex-end; gap:16px;">
            <a href="{{ route('gel-secretary.clients.ventes.index') }}" class="btn-back">Annuler</a>
            <button type="submit" class="btn-submit"><i class="fas fa-check"></i> Créer la Facture (Brouillon)</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let lineIndex = 1;

    function autoFillLine(selectElement) {
        const tr = selectElement.closest('tr');
        const option = selectElement.options[selectElement.selectedIndex];
        
        if (option.value !== "") {
            const nom = option.getAttribute('data-nom');
            const prix = option.getAttribute('data-prix');
            
            tr.querySelector('.designation-input').value = nom;
            tr.querySelector('.price-input').value = prix;
        }
        calculateLineTotals();
    }

    function addLine() {
        const container = document.getElementById('linesContainer');
        const firstRow = container.querySelector('tr');
        const newRow = firstRow.cloneNode(true);
        
        // Reset values
        newRow.querySelector('.product-select').value = "";
        newRow.querySelector('.designation-input').value = "";
        newRow.querySelector('.qty-input').value = "1";
        newRow.querySelector('.price-input').value = "0";
        newRow.querySelector('.linetotal-input').value = "0.00";
        
        // Update names index
        newRow.querySelector('.product-select').name = `lignes[${lineIndex}][produit_id]`;
        newRow.querySelector('.designation-input').name = `lignes[${lineIndex}][designation]`;
        newRow.querySelector('.qty-input').name = `lignes[${lineIndex}][quantite]`;
        newRow.querySelector('.price-input').name = `lignes[${lineIndex}][prix_unitaire]`;
        
        // Enable remove button
        newRow.querySelector('.btn-remove-line').disabled = false;
        
        container.appendChild(newRow);
        lineIndex++;
        
        updateRemoveButtons();
    }

    function removeLine(button) {
        const container = document.getElementById('linesContainer');
        if (container.children.length > 1) {
            button.closest('tr').remove();
            calculateLineTotals();
            updateRemoveButtons();
        }
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.facture-line');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.btn-remove-line');
            if (rows.length === 1) {
                btn.disabled = true;
            } else {
                btn.disabled = false;
            }
        });
    }

    function calculateLineTotals() {
        let totalHt = 0;
        
        const rows = document.querySelectorAll('.facture-line');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const lineTotal = qty * price;
            
            row.querySelector('.linetotal-input').value = lineTotal.toFixed(2);
            totalHt += lineTotal;
        });
        
        const totalTva = totalHt * 0.18; // TVA 18% fixe pour la démo
        const totalTtc = totalHt + totalTva;
        
        document.getElementById('totalHt').innerText = formatCurrency(totalHt);
        document.getElementById('totalTva').innerText = formatCurrency(totalTva);
        document.getElementById('totalTtc').innerText = formatCurrency(totalTtc);
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount) + ' FCFA';
    }

    // Init first calculation
    calculateLineTotals();
</script>
@endpush
@endsection
