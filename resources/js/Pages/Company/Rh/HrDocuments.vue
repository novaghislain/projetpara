<template>
  <CompanyLayout>
    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h3 text-gray-800 mb-1">Attestations & Documents RH</h1>
          <p class="text-muted mb-0">Gérez les attestations, certificats et autres documents du personnel.</p>
        </div>
        <button class="btn btn-primary" @click="showNewDocumentModal = true">
          <i class="bi-plus-circle me-2"></i>Nouveau Document
        </button>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div v-if="documents.length === 0" class="text-center py-5">
            <i class="bi-folder-x display-1 text-muted mb-3"></i>
            <p class="text-muted">Aucun document RH enregistré.</p>
          </div>
          <div class="table-responsive" v-else>
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Nom du Document</th>
                  <th>Type</th>
                  <th>Employé</th>
                  <th>Statut</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="doc in documents" :key="doc.id">
                  <td>
                    <strong>{{ doc.document_name }}</strong>
                  </td>
                  <td>{{ formatType(doc.type) }}</td>
                  <td>{{ doc.employee?.nom }} {{ doc.employee?.prenom }}</td>
                  <td>
                    <span class="badge" :class="getStatusClass(doc.status)">
                      {{ formatStatus(doc.status) }}
                    </span>
                  </td>
                  <td>{{ new Date(doc.created_at).toLocaleDateString() }}</td>
                  <td>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteDocument(doc.id)">
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

    <!-- Modale Nouveau Document -->
    <div class="modal fade" tabindex="-1" :class="{ show: showNewDocumentModal }" :style="{ display: showNewDocumentModal ? 'block' : 'none', backgroundColor: showNewDocumentModal ? 'rgba(0,0,0,0.5)' : '' }">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="submitDocument">
            <div class="modal-header">
              <h5 class="modal-title">Nouveau Document RH</h5>
              <button type="button" class="btn-close" @click="showNewDocumentModal = false"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Employé</label>
                <!-- Simulation d'un select d'employés -->
                <input type="number" class="form-control" v-model="form.employee_id" placeholder="ID de l'employé" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Type de Document</label>
                <select class="form-select" v-model="form.type" required>
                  <option value="attestation_travail">Attestation de travail</option>
                  <option value="certificat_travail">Certificat de travail</option>
                  <option value="contrat">Contrat</option>
                  <option value="autre">Autre</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Nom du document</label>
                <input type="text" class="form-control" v-model="form.document_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Statut</label>
                <select class="form-select" v-model="form.status" required>
                  <option value="draft">Brouillon</option>
                  <option value="issued">Délivré</option>
                  <option value="signed">Signé</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showNewDocumentModal = false">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                Enregistrer
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </CompanyLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CompanyLayout from '@/Layouts/CompanyLayout.vue';

const documents = ref([]);
const showNewDocumentModal = ref(false);

const form = useForm({
  employee_id: '',
  type: 'attestation_travail',
  document_name: '',
  status: 'draft',
});

const loadDocuments = async () => {
  try {
    const res = await fetch('/company/hr-documents');
    if (res.ok) {
      const data = await res.json();
      documents.value = data.data || [];
    }
  } catch (e) {
    console.error('Erreur chargement documents', e);
  }
};

const submitDocument = () => {
  form.post('/company/hr-documents', {
    onSuccess: () => {
      showNewDocumentModal.value = false;
      form.reset();
      loadDocuments();
    }
  });
};

const deleteDocument = async (id) => {
  if (confirm("Supprimer ce document ?")) {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content;
    try {
      await fetch(`/company/hr-documents/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf }
      });
      loadDocuments();
    } catch (e) {
      console.error(e);
    }
  }
};

const formatType = (type) => {
  const map = {
    'attestation_travail': 'Attestation de travail',
    'certificat_travail': 'Certificat de travail',
    'contrat': 'Contrat',
    'autre': 'Autre',
  };
  return map[type] || type;
};

const formatStatus = (status) => {
  const map = {
    'draft': 'Brouillon',
    'issued': 'Délivré',
    'signed': 'Signé',
  };
  return map[status] || status;
};

const getStatusClass = (status) => {
  const map = {
    'draft': 'bg-secondary',
    'issued': 'bg-primary',
    'signed': 'bg-success',
  };
  return map[status] || 'bg-secondary';
};

onMounted(() => {
  loadDocuments();
});
</script>
