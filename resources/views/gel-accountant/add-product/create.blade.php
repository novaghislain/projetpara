@extends('layouts.gel-accountant')

@section('title', 'Nouveau Produit / Service')

@section('content')

<div class="gel-page-header">
    <div>
        <h1 class="gel-page-title"><i class="fas fa-plus-circle" style="color:var(--gel-primary); margin-right:8px;"></i> Nouveau Produit / Service</h1>
        <p class="gel-page-subtitle">Ajoutez un produit, un service ou un article en stock À  votre catalogue.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <form action="{{ route('gel-accountant.products.import') }}" method="POST" enctype="multipart/form-data" style="display:inline;" id="importForm">
            @csrf
            <label class="gel-btn gel-btn-secondary" style="cursor:pointer; margin-bottom:0;">
                <i class="fas fa-file-import"></i> Importer CSV
                <input type="file" name="import_file" accept=".csv" style="display:none;" onchange="showToast('Importation de produits en cours...', 'info'); document.getElementById('importForm').submit();">
            </label>
        </form>
        <a href="javascript:history.back()" class="gel-btn gel-btn-secondary"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
</div>

<form method="POST" action="#" id="productForm">
@csrf

<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:24px;">
    <!-- Type selection -->
    <label class="product-type-card active" id="type-stock" onclick="selectType('stock')">
        <input type="radio" name="type" value="stock" checked style="display:none;">
        <i class="fas fa-box" style="font-size:24px; color:var(--gel-primary); margin-bottom:12px;"></i>
        <div style="font-weight:600; font-size:15px; margin-bottom:4px;">Article en stock</div>
        <div style="font-size:12px; color:var(--gel-text-secondary);">Produit physique dont vous suivez la quantité en stock.</div>
    </label>
    
    <label class="product-type-card" id="type-non-stock" onclick="selectType('non-stock')">
        <input type="radio" name="type" value="non-stock" style="display:none;">
        <i class="fas fa-box-open" style="font-size:24px; color:var(--gel-primary); margin-bottom:12px;"></i>
        <div style="font-weight:600; font-size:15px; margin-bottom:4px;">Article hors stock</div>
        <div style="font-size:12px; color:var(--gel-text-secondary);">Produit acheté/vendu dont vous ne suivez pas la quantité.</div>
    </label>
    
    <label class="product-type-card" id="type-service" onclick="selectType('service')">
        <input type="radio" name="type" value="service" style="display:none;">
        <i class="fas fa-hands-helping" style="font-size:24px; color:var(--gel-primary); margin-bottom:12px;"></i>
        <div style="font-weight:600; font-size:15px; margin-bottom:4px;">Service</div>
        <div style="font-size:12px; color:var(--gel-text-secondary);">Prestation immatérielle facturée au client (ex: conseil).</div>
    </label>
</div>

<div class="gel-card p-4 mb-4">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);">Informations générales</h3>
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Nom du produit/service *</label>
            <input type="text" name="name" class="doc-input" required placeholder="Ex: Ordinateur Dell XPS 15">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">SKU (Référence)</label>
            <input type="text" name="sku" class="doc-input" placeholder="Ex: DELL-XPS-001">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Catégorie</label>
            <select name="category_id" class="doc-input">
                <option value="">— Ajouter une nouvelle catégorie —</option>
                <option value="1">Matériel informatique</option>
                <option value="2">Services professionnels</option>
            </select>
        </div>
    </div>
</div>

<div class="gel-card" id="stockSection" style="padding:24px; margin-bottom:20px;">
    <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);">Informations sur le stock</h3>
    <div class="doc-form-grid">
        <div class="doc-form-group">
            <label class="doc-label">Quantité initiale en stock</label>
            <input type="number" name="initial_quantity" class="doc-input" value="0" min="0">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Point de commande (alerte)</label>
            <input type="number" name="reorder_point" class="doc-input" value="5" min="0">
        </div>
        <div class="doc-form-group">
            <label class="doc-label">Compte d'inventaire</label>
            <select name="inventory_account_id" class="doc-input">
                <option value="31">31 — Marchandises</option>
            </select>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
    <!-- Ventes -->
    <div class="gel-card p-4 mb-4">
        <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);">Informations de vente</h3>
        <div class="doc-form-grid" style="grid-template-columns:1fr;">
            <div class="doc-form-group">
                <label class="doc-label">Prix de vente unitaire (HT)</label>
                <input type="number" name="sales_price" class="doc-input" value="0" min="0">
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Compte de revenus (Ventes)</label>
                <select name="sales_account_id" class="doc-input">
                    <option value="70">70 — Ventes</option>
                </select>
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Taxe sur les ventes</label>
                <select name="sales_tax_id" class="doc-input">
                    <option value="18">TVA 18%</option>
                    <option value="0">Exonéré 0%</option>
                </select>
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Description sur les factures de vente</label>
                <textarea name="sales_description" class="doc-input" rows="3"></textarea>
            </div>
        </div>
    </div>

    <!-- Achats -->
    <div class="gel-card p-4 mb-4">
        <h3 style="font-size:15px; font-weight:700; margin-bottom:16px; color:var(--gel-text-primary);">Informations d'achat</h3>
        <div class="doc-form-grid" style="grid-template-columns:1fr;">
            <div class="doc-form-group">
                <label class="doc-label">Coût unitaire (HT)</label>
                <input type="number" name="purchase_cost" class="doc-input" value="0" min="0">
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Compte de charges (Achats)</label>
                <select name="purchase_account_id" class="doc-input">
                    <option value="60">60 — Achats</option>
                </select>
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Taxe sur les achats</label>
                <select name="purchase_tax_id" class="doc-input">
                    <option value="18">TVA 18%</option>
                    <option value="0">Exonéré 0%</option>
                </select>
            </div>
            <div class="doc-form-group">
                <label class="doc-label">Description sur les bons de commande</label>
                <textarea name="purchase_description" class="doc-input" rows="3"></textarea>
            </div>
        </div>
    </div>
</div>

<div style="display:flex; justify-content:flex-end; gap:10px; padding-bottom:30px;">
    <a href="javascript:history.back()" class="gel-btn gel-btn-secondary">Annuler</a>
    <button type="submit" class="gel-btn gel-btn-primary"><i class="fas fa-save"></i> Enregistrer le produit</button>
</div>
</form>


<script>
function selectType(t){
    document.querySelectorAll('.product-type-card').forEach(el=>el.classList.remove('active'));
    document.getElementById('type-'+t).classList.add('active');
    document.querySelector(`input[name="type"][value="${t}"]`).checked=true;
    
    if(t==='stock'){
        document.getElementById('stockSection').style.display='block';
    }else{
        document.getElementById('stockSection').style.display='none';
    }
}
</script>
@endsection

