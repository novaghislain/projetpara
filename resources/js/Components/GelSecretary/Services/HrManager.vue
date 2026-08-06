<template>
  <div class="hr-manager">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="sec-card p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="m-0" style="font-size:18px; font-weight:700; color:var(--sec-text);">Suivi RH</h3>
                    <div style="font-size:13px; color:var(--sec-text-muted);">Gestion des congés, absences et attestations</div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary" @click="activeTab = 'documents'">
                        <i class="fas fa-file-alt"></i> Documents RH
                    </button>
                    <button class="btn sec-btn-primary" @click="activeTab = 'leaves'">
                        <i class="fas fa-calendar-times"></i> Congés & Absences
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="activeTab === 'leaves'" class="sec-card p-0">
        <table class="table sec-table mb-0">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="leaves.length === 0">
                    <td colspan="5" class="text-center p-4 text-muted">
                        Aucune demande de congé ou absence en cours.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="activeTab === 'documents'" class="sec-card p-0">
        <table class="table sec-table mb-0">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Type de document</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="documents.length === 0">
                    <td colspan="4" class="text-center p-4 text-muted">
                        Aucun document RH enregistré.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            activeTab: 'leaves',
            leaves: [],
            documents: []
        }
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            try {
                const res = await axios.get('/gel-secretary/services/hr');
                this.leaves = res.data.leaves || [];
                this.documents = res.data.documents || [];
            } catch (e) {
                console.error(e);
            }
        },
        formatDate(date) {
            return date ? new Date(date).toLocaleDateString('fr-FR') : '';
        }
    }
}
</script>

<style scoped>
.sec-table th { background: #f8fafc; color: #64748b; font-size: 12px; text-transform: uppercase; }
.sec-table td { vertical-align: middle; }
</style>
