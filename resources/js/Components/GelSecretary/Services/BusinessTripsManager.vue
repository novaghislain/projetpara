<template>
  <div class="business-trips">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="sec-card p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="m-0" style="font-size:18px; font-weight:700; color:var(--sec-text);">Liste des Déplacements</h3>
                    <div style="font-size:13px; color:var(--sec-text-muted);">Gérez les voyages d'affaires pour les clients</div>
                </div>
                <button class="btn sec-btn-primary" @click="showAddModal = true">
                    <i class="fas fa-plus"></i> Nouveau Déplacement
                </button>
            </div>
        </div>
    </div>

    <div class="sec-card p-0">
        <table class="table sec-table mb-0">
            <thead>
                <tr>
                    <th>Voyageur</th>
                    <th>Destination</th>
                    <th>Dates</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="trips.length === 0">
                    <td colspan="5" class="text-center p-4 text-muted">
                        Aucun déplacement enregistré.
                    </td>
                </tr>
                <tr v-for="trip in trips" :key="trip.id">
                    <td>
                        <div class="font-weight-bold">{{ trip.traveler_name }}</div>
                        <div style="font-size: 12px; color: var(--sec-text-muted);">{{ trip.client_name }}</div>
                    </td>
                    <td>{{ trip.destination }}</td>
                    <td>
                        <div style="font-size: 13px;">{{ formatDate(trip.start_date) }} - {{ formatDate(trip.end_date) }}</div>
                    </td>
                    <td>
                        <span class="badge" :class="'bg-' + getStatusColor(trip.status)">{{ trip.status }}</span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light"><i class="fas fa-eye"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal d'ajout simplifié (simulation) -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showAddModal }" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Nouveau Déplacement</h5>
            <button type="button" class="btn-close" @click="showAddModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="sec-form-group mb-3">
                <label class="form-label">Voyageur</label>
                <input type="text" class="form-control" v-model="form.traveler_name" required>
            </div>
            <div class="sec-form-group mb-3">
                <label class="form-label">Client</label>
                <select class="form-select" v-model="form.client_id" required>
                    <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.nom_entreprise || client.company_name }}</option>
                </select>
            </div>
            <div class="sec-form-group mb-3">
                <label class="form-label">Destination</label>
                <input type="text" class="form-control" v-model="form.destination" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Début</label>
                    <input type="date" class="form-control" v-model="form.start_date" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fin</label>
                    <input type="date" class="form-control" v-model="form.end_date" required>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showAddModal = false">Fermer</button>
            <button type="button" class="btn sec-btn-primary" @click="submitForm">Enregistrer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            trips: [],
            clients: [],
            showAddModal: false,
            form: {
                traveler_name: '',
                client_id: '',
                destination: '',
                start_date: '',
                end_date: '',
                status: 'planifie'
            }
        }
    },
    mounted() {
        this.fetchTrips();
        this.fetchClients();
    },
    methods: {
        async fetchTrips() {
            try {
                const res = await axios.get('/gel-secretary/services/business-trips');
                this.trips = res.data;
            } catch (e) {
                console.error(e);
            }
        },
        async fetchClients() {
            try {
                const res = await axios.get('/gel-secretary/clients', { headers: { 'Accept': 'application/json' }});
                // S'assurer de récupérer les clients de l'API
                this.clients = res.data.clients || res.data;
            } catch (e) {
                console.error(e);
            }
        },
        async submitForm() {
            try {
                const res = await axios.post('/gel-secretary/services/business-trips', this.form);
                if (res.data.success) {
                    this.trips.unshift(res.data.trip);
                    this.showAddModal = false;
                    this.form = { traveler_name: '', client_id: '', destination: '', start_date: '', end_date: '', status: 'planifie' };
                }
            } catch (e) {
                console.error(e);
                alert("Erreur lors de l'enregistrement.");
            }
        },
        formatDate(date) {
            return date ? new Date(date).toLocaleDateString('fr-FR') : '';
        },
        getStatusColor(status) {
            const colors = {
                'planifie': 'primary',
                'en_cours': 'warning',
                'termine': 'success',
                'annule': 'danger'
            };
            return colors[status] || 'secondary';
        }
    }
}
</script>

<style scoped>
.sec-table th { background: #f8fafc; color: #64748b; font-size: 12px; text-transform: uppercase; }
.sec-table td { vertical-align: middle; }
</style>
