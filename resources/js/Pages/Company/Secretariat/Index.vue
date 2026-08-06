<template>
  <CompanyLayout>
    <div class="container-fluid py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h3 text-gray-800 mb-1">Déplacements & Événements</h1>
          <p class="text-muted mb-0">Gestion des voyages professionnels et réservations de ressources</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" @click="showNewTripModal = true">
            <i class="fas fa-plane me-2"></i>Nouveau Voyage
          </button>
          <button class="btn btn-outline-primary" @click="showNewReservationModal = true">
            <i class="fas fa-calendar-alt me-2"></i>Réserver Salle/Matériel
          </button>
        </div>
      </div>

      <div class="row">
        <!-- Voyages -->
        <div class="col-lg-6 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
              <h5 class="mb-0 text-primary"><i class="fas fa-plane-departure me-2"></i>Voyages Professionnels</h5>
            </div>
            <div class="card-body">
              <div v-if="trips.data.length === 0" class="text-center py-5">
                <i class="fas fa-plane-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun voyage enregistré.</p>
              </div>
              <div class="table-responsive" v-else>
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Destination</th>
                      <th>Dates</th>
                      <th>Statut</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="trip in trips.data" :key="trip.id">
                      <td>
                        <strong>{{ trip.destination }}</strong><br>
                        <small class="text-muted">{{ trip.user?.name }}</small>
                      </td>
                      <td>
                        <small>{{ formatDate(trip.start_date) }} - {{ formatDate(trip.end_date) }}</small>
                      </td>
                      <td>
                        <span class="badge" :class="getStatusClass(trip.status)">
                          {{ trip.status }}
                        </span>
                      </td>
                      <td>
                        <button class="btn btn-sm btn-light"><i class="fas fa-eye"></i></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Réservations -->
        <div class="col-lg-6 mb-4">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
              <h5 class="mb-0 text-success"><i class="fas fa-calendar-check me-2"></i>Réservations</h5>
            </div>
            <div class="card-body">
              <div v-if="reservations.data.length === 0" class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune réservation en cours.</p>
              </div>
              <div class="table-responsive" v-else>
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Ressource</th>
                      <th>Type</th>
                      <th>Dates</th>
                      <th>Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="res in reservations.data" :key="res.id">
                      <td>
                        <strong>{{ res.resource_name }}</strong><br>
                        <small class="text-muted">{{ res.user?.name }}</small>
                      </td>
                      <td><span class="badge bg-secondary">{{ res.type }}</span></td>
                      <td>
                        <small>{{ formatDateTime(res.start_time) }}</small><br>
                        <small class="text-muted">au {{ formatDateTime(res.end_time) }}</small>
                      </td>
                      <td>
                        <span class="badge" :class="getStatusClass(res.status)">
                          {{ res.status }}
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

    <!-- Modale Nouveau Voyage -->
    <div class="modal fade" id="newTripModal" tabindex="-1" :class="{ show: showNewTripModal }" :style="{ display: showNewTripModal ? 'block' : 'none', backgroundColor: showNewTripModal ? 'rgba(0,0,0,0.5)' : '' }">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form @submit.prevent="submitTrip">
            <div class="modal-header">
              <h5 class="modal-title">Nouveau Voyage Professionnel</h5>
              <button type="button" class="btn-close" @click="showNewTripModal = false"></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label">Destination</label>
                  <input type="text" class="form-control" v-model="tripForm.destination" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Date de début</label>
                  <input type="date" class="form-control" v-model="tripForm.start_date" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Date de fin</label>
                  <input type="date" class="form-control" v-model="tripForm.end_date" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Motif du voyage</label>
                  <textarea class="form-control" v-model="tripForm.purpose" rows="2" required></textarea>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Budget estimé (Optionnel)</label>
                  <input type="number" class="form-control" v-model="tripForm.budget" step="0.01">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Détails transport</label>
                  <input type="text" class="form-control" v-model="tripForm.transport_details" placeholder="Vol AF123, Train...">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Hébergement</label>
                  <input type="text" class="form-control" v-model="tripForm.accommodation_details" placeholder="Hôtel, Adresse...">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showNewTripModal = false">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="tripForm.processing">
                Enregistrer
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modale Nouvelle Réservation -->
    <div class="modal fade" id="newReservationModal" tabindex="-1" :class="{ show: showNewReservationModal }" :style="{ display: showNewReservationModal ? 'block' : 'none', backgroundColor: showNewReservationModal ? 'rgba(0,0,0,0.5)' : '' }">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="submitReservation">
            <div class="modal-header">
              <h5 class="modal-title">Nouvelle Réservation</h5>
              <button type="button" class="btn-close" @click="showNewReservationModal = false"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Type de ressource</label>
                <select class="form-select" v-model="reservationForm.type" required>
                  <option value="salle">Salle de réunion</option>
                  <option value="materiel">Matériel (Vidéoprojecteur, etc.)</option>
                  <option value="vehicule">Véhicule de service</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Nom / Référence de la ressource</label>
                <input type="text" class="form-control" v-model="reservationForm.resource_name" required>
              </div>
              <div class="row g-3 mb-3">
                <div class="col-6">
                  <label class="form-label">Début</label>
                  <input type="datetime-local" class="form-control" v-model="reservationForm.start_time" required>
                </div>
                <div class="col-6">
                  <label class="form-label">Fin</label>
                  <input type="datetime-local" class="form-control" v-model="reservationForm.end_time" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Motif (Optionnel)</label>
                <textarea class="form-control" v-model="reservationForm.purpose" rows="2"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showNewReservationModal = false">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="reservationForm.processing">
                Réserver
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </CompanyLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import CompanyLayout from '@/Layouts/CompanyLayout.vue';

const props = defineProps({
  trips: {
    type: Object,
    default: () => ({ data: [] })
  },
  reservations: {
    type: Object,
    default: () => ({ data: [] })
  }
});

const showNewTripModal = ref(false);
const showNewReservationModal = ref(false);

const tripForm = useForm({
  destination: '',
  start_date: '',
  end_date: '',
  purpose: '',
  budget: '',
  transport_details: '',
  accommodation_details: ''
});

const reservationForm = useForm({
  type: 'salle',
  resource_name: '',
  start_time: '',
  end_time: '',
  purpose: ''
});

const submitTrip = () => {
  tripForm.post('/company/trips', {
    onSuccess: () => {
      showNewTripModal.value = false;
      tripForm.reset();
      router.reload({ only: ['trips'] });
    }
  });
};

const submitReservation = () => {
  reservationForm.post('/company/reservations', {
    onSuccess: () => {
      showNewReservationModal.value = false;
      reservationForm.reset();
      router.reload({ only: ['reservations'] });
    }
  });
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR');
};

const formatDateTime = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleString('fr-FR', {
    day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
  });
};

const getStatusClass = (status) => {
  switch (status) {
    case 'en_attente': return 'bg-warning text-dark';
    case 'approuvé':
    case 'confirmé': return 'bg-success';
    case 'rejeté':
    case 'annulé': return 'bg-danger';
    case 'terminé': return 'bg-info';
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
