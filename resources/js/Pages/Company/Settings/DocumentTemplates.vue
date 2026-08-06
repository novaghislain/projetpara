<template>
  <CompanyLayout>
    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h3 text-gray-800 mb-1" style="font-family:'Outfit',sans-serif;font-weight:800;color:#163A5E;">Modèles de Documents</h1>
          <p class="text-muted mb-0">Bibliothèque de modèles (S13) pour l'entreprise</p>
        </div>
        <button class="isup-btn-primary" @click="showModal = true">
          <i class="bi-plus-circle me-2"></i> Nouveau Modèle
        </button>
      </div>

      <div class="isup-panel">
        <div class="isup-panel-header">
          <i class="bi-file-text me-2"></i> Liste des Modèles
        </div>
        <div class="isup-panel-body p-0">
          <div v-if="!templates.data || templates.data.length === 0" class="text-center py-5">
            <i class="bi-file-earmark-text display-1 text-muted mb-3"></i>
            <p class="text-muted">Aucun modèle disponible.</p>
          </div>
          <div class="isup-table-wrap" v-else>
            <table class="isup-table w-100">
              <thead>
                <tr>
                  <th>Nom du Modèle</th>
                  <th>Catégorie</th>
                  <th>Statut</th>
                  <th>Date d'ajout</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tpl in templates.data" :key="tpl.id">
                  <td>
                    <strong style="color:#163A5E;">{{ tpl.name }}</strong><br>
                    <small class="text-muted">{{ tpl.description }}</small>
                  </td>
                  <td><span class="isup-badge isup-badge-pill isup-badge-light">{{ tpl.category }}</span></td>
                  <td>
                    <span class="isup-status" :class="tpl.is_active ? 'isup-status-green' : 'isup-status-red'">
                      {{ tpl.is_active ? 'Actif' : 'Inactif' }}
                    </span>
                  </td>
                  <td>{{ new Date(tpl.created_at).toLocaleDateString() }}</td>
                  <td>
                    <button class="isup-icon-btn me-2" title="Télécharger">
                      <i class="bi-download"></i>
                    </button>
                    <button class="isup-icon-btn isup-icon-danger" title="Supprimer" @click="deleteTemplate(tpl.id)">
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

    <!-- Modale Nouveau Modèle -->
    <div class="isup-modal-overlay" v-if="showModal" @click.self="showModal = false">
      <div class="isup-modal">
        <form @submit.prevent="submitTemplate">
          <div class="isup-modal-header">
            <span>Nouveau Modèle</span>
            <button type="button" class="isup-modal-close" @click="showModal = false">&times;</button>
          </div>
          <div class="isup-modal-body">
            <div class="isup-field-row">
              <label class="isup-label">Nom du modèle</label>
              <input type="text" class="isup-input" v-model="form.name" required>
            </div>
            <div class="isup-field-row">
              <label class="isup-label">Catégorie</label>
              <select class="isup-select" v-model="form.category" required>
                <option value="RH">Ressources Humaines</option>
                <option value="Juridique">Juridique</option>
                <option value="Finance">Finance</option>
                <option value="Autre">Autre</option>
              </select>
            </div>
            <div class="isup-field-row">
              <label class="isup-label">Description (Optionnel)</label>
              <textarea class="isup-input" v-model="form.description" rows="2"></textarea>
            </div>
            <div class="isup-field-row d-flex align-items-center gap-2">
              <label class="isup-switch mb-0">
                <input type="checkbox" v-model="form.is_active">
                <span class="isup-switch-slider"></span>
              </label>
              <span class="isup-label mb-0">Actif (disponible pour l'équipe)</span>
            </div>
          </div>
          <div class="isup-modal-footer">
            <button type="button" class="isup-btn-grey" @click="showModal = false">Annuler</button>
            <button type="submit" class="isup-btn-primary" :disabled="form.processing">
              Enregistrer
            </button>
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
  templates: {
    type: Object,
    default: () => ({ data: [] })
  }
});

const showModal = ref(false);

const form = useForm({
  name: '',
  category: 'RH',
  description: '',
  is_active: true,
});

const submitTemplate = () => {
  form.post('/company/document-templates', {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
    }
  });
};

const deleteTemplate = async (id) => {
  if (confirm("Supprimer ce modèle ?")) {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content;
    try {
      await fetch(`/company/document-templates/${id}`, {
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
