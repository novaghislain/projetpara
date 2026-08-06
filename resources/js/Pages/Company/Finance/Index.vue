<template>
  <CompanyLayout>
    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h3 text-gray-800 mb-1">Notes de frais & Suivi Financier</h1>
          <p class="text-muted mb-0">Saisie des notes de frais et suivi léger des factures (avant comptabilité)</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" @click="showNewExpenseModal = true">
            <i class="fas fa-receipt me-2"></i>Nouvelle Note de frais
          </button>
          <button class="btn btn-outline-primary" @click="showNewInvoiceModal = true">
            <i class="fas fa-file-invoice-dollar me-2"></i>Saisir Facture
          </button>
        </div>
      </div>

      <div class="row">
        <!-- Notes de frais -->
        <div class="col-lg-6 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
              <h5 class="mb-0 text-primary"><i class="fas fa-money-check-alt me-2"></i>Notes de frais récentes</h5>
            </div>
            <div class="card-body">
              <div v-if="expenses.data.length === 0" class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune note de frais.</p>
              </div>
              <div class="table-responsive" v-else>
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Titre</th>
                      <th>Montant</th>
                      <th>Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="expense in expenses.data" :key="expense.id">
                      <td>
                        <strong>{{ expense.title }}</strong><br>
                        <small class="text-muted">{{ expense.user?.name }} - {{ formatDate(expense.date) }}</small>
                      </td>
                      <td>
                        <strong>{{ formatCurrency(expense.amount, expense.currency) }}</strong>
                      </td>
                      <td>
                        <span class="badge" :class="getStatusClass(expense.status)">
                          {{ expense.status }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Factures Légères -->
        <div class="col-lg-6 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
              <h5 class="mb-0 text-success"><i class="fas fa-file-invoice me-2"></i>Suivi Factures (à régler/émettre)</h5>
            </div>
            <div class="card-body">
              <div v-if="invoices.data.length === 0" class="text-center py-5">
                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune facture enregistrée.</p>
              </div>
              <div class="table-responsive" v-else>
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Partenaire</th>
                      <th>Type</th>
                      <th>Montant TTC</th>
                      <th>Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="inv in invoices.data" :key="inv.id">
                      <td>
                        <strong>{{ inv.partner_name }}</strong><br>
                        <small class="text-muted">#{{ inv.invoice_number }}</small>
                      </td>
                      <td>
                        <span class="badge bg-secondary">{{ inv.type }}</span>
                      </td>
                      <td>
                        <strong>{{ formatCurrency(inv.amount_ttc, 'XOF') }}</strong>
                      </td>
                      <td>
                        <span class="badge" :class="getStatusClass(inv.status)">
                          {{ inv.status.replace('_', ' ') }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CompanyLayout>
</template>

<script setup>
import { ref } from 'vue';
import CompanyLayout from '@/Layouts/CompanyLayout.vue';

const props = defineProps({
  expenses: {
    type: Object,
    default: () => ({ data: [] })
  },
  invoices: {
    type: Object,
    default: () => ({ data: [] })
  }
});

const showNewExpenseModal = ref(false);
const showNewInvoiceModal = ref(false);

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR');
};

const formatCurrency = (value, currency) => {
  if (!value) return '';
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currency || 'XOF' }).format(value);
};

const getStatusClass = (status) => {
  switch (status) {
    case 'brouillon': return 'bg-secondary';
    case 'soumis': return 'bg-warning text-dark';
    case 'approuvé':
    case 'payé': return 'bg-success';
    case 'rejeté':
    case 'en_retard': return 'bg-danger';
    case 'à_payer': return 'bg-info';
    default: return 'bg-secondary';
  }
};
</script>

<style scoped>
.card {
  border-radius: 12px;
}
.badge {
  font-weight: 500;
}
</style>
