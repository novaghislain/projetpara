<template>
  <CompanyLayout>
    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h3 text-gray-800 mb-1" style="font-family:'Outfit',sans-serif;font-weight:800;color:#163A5E;">Fournitures de Bureau</h1>
          <p class="text-muted mb-0">Gestion du stock et des demandes de fournitures</p>
        </div>
        <button class="isup-btn-primary" @click="showModal = true">
          <i class="bi-plus-circle me-2"></i> Nouvelle Fourniture
        </button>
      </div>

      <div class="row">
        <!-- Stock de fournitures -->
        <div class="col-lg-7 mb-4">
          <div class="isup-panel h-100">
            <div class="isup-panel-header">
              <i class="bi-box-seam me-2"></i> État du Stock
            </div>
            <div class="isup-panel-body p-0">
              <div v-if="!supplies.data || supplies.data.length === 0" class="text-center py-4">
                <p class="text-muted">Aucune fourniture enregistrée.</p>
              </div>
              <div class="isup-table-wrap" v-else>
                <table class="isup-table w-100">
                  <thead>
                    <tr>
                      <th>Article</th>
                      <th>En stock</th>
                      <th>Seuil min.</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in supplies.data" :key="item.id">
                      <td>
                        <strong style="color:#163A5E;">{{ item.name }}</strong>
                        <div class="small text-muted" v-if="item.description">{{ item.description }}</div>
                      </td>
                      <td>
                        <span class="isup-status" :class="item.stock_quantity <= item.min_threshold ? 'isup-status-red' : 'isup-status-green'">
                          {{ item.stock_quantity }}
                        </span>
                      </td>
                      <td>{{ item.min_threshold }}</td>
                      <td>
                        <button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteSupply(item.id)">
                          <i class="bi-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Demandes en attente -->
        <div class="col-lg-5 mb-4">
          <div class="isup-panel h-100">
            <div class="isup-panel-header d-flex justify-content-between align-items-center w-100">
              <div><i class="bi-list-ul me-2"></i> Demandes Récentes</div>
              <button class="btn btn-sm" @click="showRequestModal = true" style="color:white; background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3);">
                <i class="bi-plus"></i> Demander
              </button>
            </div>
            <div class="isup-panel-body p-0">
              <div v-if="!requests.data || requests.data.length === 0" class="text-center py-4">
                <p class="text-muted">Aucune demande en cours.</p>
              </div>
              <div class="list-group list-group-flush border-0" v-else>
                <div class="list-group-item px-3 py-3 border-bottom" style="border-color:#f0f4f8 !important;" v-for="req in requests.data" :key="req.id">
                  <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                    <h6 class="mb-0" style="color:#163A5E; font-size:13px; font-weight:700;">{{ req.supply?.name || 'Article inconnu' }} (x{{ req.quantity_requested }})</h6>
                    <span class="isup-badge isup-badge-pill" :class="req.status === 'en_attente' ? 'isup-status-orange' : 'isup-badge-light'">{{ req.status }}</span>
                  </div>
                  <p class="mb-0 small text-muted">Demandé par <strong>{{ req.user?.name }}</strong></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modale Nouvelle Fourniture -->
    <div class="isup-modal-overlay" v-if="showModal" @click.self="showModal = false">
      <div class="isup-modal">
        <form @submit.prevent="submitSupply">
          <div class="isup-modal-header">
            <span>Ajouter une Fourniture</span>
            <button type="button" class="isup-modal-close" @click="showModal = false">&times;</button>
          </div>
          <div class="isup-modal-body">
            <div class="isup-field-row">
              <label class="isup-label">Nom de l'article</label>
              <input type="text" class="isup-input" v-model="form.name" required>
            </div>
            <div class="isup-field-row">
              <label class="isup-label">Description (Optionnel)</label>
              <textarea class="isup-input" v-model="form.description" rows="2"></textarea>
            </div>
            <div class="row g-3">
              <div class="col-6">
                <div class="isup-field-row">
                  <label class="isup-label">Quantité en stock</label>
                  <input type="number" class="isup-input" v-model="form.stock_quantity" min="0" required>
                </div>
              </div>
              <div class="col-6">
                <div class="isup-field-row">
                  <label class="isup-label">Seuil minimum</label>
                  <input type="number" class="isup-input" v-model="form.min_threshold" min="0" required>
                </div>
              </div>
            </div>
          </div>
          <div class="isup-modal-footer">
            <button type="button" class="isup-btn-grey" @click="showModal = false">Annuler</button>
            <button type="submit" class="isup-btn-primary" :disabled="form.processing">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modale Nouvelle Demande -->
    <div class="isup-modal-overlay" v-if="showRequestModal" @click.self="showRequestModal = false">
      <div class="isup-modal">
        <form @submit.prevent="submitRequest">
          <div class="isup-modal-header">
            <span>Faire une demande de fourniture</span>
            <button type="button" class="isup-modal-close" @click="showRequestModal = false">&times;</button>
          </div>
          <div class="isup-modal-body">
            <div class="isup-field-row">
              <label class="isup-label">Article souhaité</label>
              <select class="isup-select" v-model="requestForm.dae_office_supply_id" required>
                <option value="" disabled>Sélectionnez un article</option>
                <option v-for="item in supplies.data" :key="item.id" :value="item.id">
                  {{ item.name }} (Stock: {{ item.stock_quantity }})
                </option>
              </select>
            </div>
            <div class="isup-field-row">
              <label class="isup-label">Quantité</label>
              <input type="number" class="isup-input" v-model="requestForm.quantity_requested" min="1" required>
            </div>
            <div class="isup-field-row">
              <label class="isup-label">Motif (Optionnel)</label>
              <textarea class="isup-input" v-model="requestForm.reason" rows="2"></textarea>
            </div>
          </div>
          <div class="isup-modal-footer">
            <button type="button" class="isup-btn-grey" @click="showRequestModal = false">Annuler</button>
            <button type="submit" class="isup-btn-primary" :disabled="requestForm.processing">Envoyer la demande</button>
          </div>
        </form>
      </div>
    </div>
  </CompanyLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CompanyLayout from '@/Layouts/CompanyLayout.vue';

const props = defineProps({
  supplies: {
    type: Object,
    default: () => ({ data: [] })
  },
  requests: {
    type: Object,
    default: () => ({ data: [] })
  }
});

const showModal = ref(false);
const showRequestModal = ref(false);

const form = useForm({
  name: '',
  description: '',
  stock_quantity: 0,
  min_threshold: 5,
});

const requestForm = useForm({
  dae_office_supply_id: '',
  quantity_requested: 1,
  reason: '',
});

const submitSupply = () => {
  form.post('/company/office-supplies', {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
    }
  });
};

const submitRequest = () => {
  requestForm.post('/company/office-supplies/request', {
    onSuccess: () => {
      showRequestModal.value = false;
      requestForm.reset();
    }
  });
};

const deleteSupply = async (id) => {
  if (confirm("Supprimer cet article ?")) {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content;
    try {
      await fetch(`/company/office-supplies/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf }
      });
      window.location.reload();
    } catch (e) {
      console.error(e);
    }
  }
};
</script>
