<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../../Layouts/GelLayout.vue';

const props = defineProps({
    ticket: { type: Object, default: null },
    clients: { type: Array, default: () => [] },
    technicians: { type: Array, default: () => [] },
});

const submitting = ref(false);
const saved = ref(false);
const error = ref(null);

const typeOptions = ['incident', 'request', 'change', 'problem'];
const priorityOptions = ['low', 'medium', 'high', 'critical'];

const form = ref({
    title: '',
    description: '',
    type: 'incident',
    priority: 'medium',
    client_id: '',
    assigned_to: '',
});

const isEdit = !!props.ticket;

const initForm = () => {
    if (props.ticket) {
        form.value = {
            title: props.ticket.title || '',
            description: props.ticket.description || '',
            type: props.ticket.type || 'incident',
            priority: props.ticket.priority || 'medium',
            client_id: props.ticket.client_id || '',
            assigned_to: props.ticket.assigned_to || '',
        };
    }
};

const submitForm = async () => {
    submitting.value = true;
    saved.value = false;
    error.value = null;
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEdit ? '/it/tickets/' + props.ticket.id : '/it/tickets';
        const method = isEdit ? 'PUT' : 'POST';

        let payload;
        if (isEdit) {
            // Update endpoint only accepts status, priority, assigned_to, resolution
            payload = {
                status: props.ticket.status || 'open',
                priority: form.value.priority,
                assigned_to: form.value.assigned_to || null,
            };
        } else {
            payload = {
                title: form.value.title,
                description: form.value.description || null,
                type: form.value.type,
                priority: form.value.priority,
                client_id: form.value.client_id,
                assigned_to: form.value.assigned_to || null,
            };
        }

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (res.status === 302) {
            // Redirect response — follow it
            window.location.href = '/it/tickets';
            return;
        }

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || Object.values(errData.errors || {}).flat().join(', ') || 'Erreur lors de l\'enregistrement');
        }

        saved.value = true;
        if (!isEdit) {
            const data = await res.json().catch(() => ({}));
            if (data && data.id) {
                window.location.href = '/it/tickets/' + data.id;
            } else {
                window.location.href = '/it/tickets';
            }
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        submitting.value = false;
    }
};

const goBack = () => {
    window.history.back();
};

onMounted(initForm);
</script>

<template>
    <GelLayout :page-title="isEdit ? ('Modifier le ticket #' + props.ticket?.ticket_number) : 'Nouveau ticket IT'">
        <div class="p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="bi bi-ticket-detailed me-2"></i>
                    {{ isEdit ? 'Modifier le ticket ' + (props.ticket?.ticket_number || '') : 'Nouveau ticket IT' }}
                </h2>

                <div v-if="saved" class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi-check-circle-fill"></i> Ticket enregistré avec succès.
                </div>
                <div v-if="error" class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="bi-exclamation-triangle-fill"></i> {{ error }}
                </div>

                <form @submit.prevent="submitForm">
                    <div class="row g-3">
                        <!-- Informations générales -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary border-bottom pb-2">Informations générales</h6>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium">Titre *</label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="form-control"
                                placeholder="Ex: Problème d'accès au réseau"
                                :disabled="isEdit"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium">Description</label>
                            <textarea
                                v-model="form.description"
                                class="form-control"
                                rows="4"
                                placeholder="Décrivez le problème ou la demande en détail..."
                                :disabled="isEdit"
                            ></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Type *</label>
                            <select v-model="form.type" class="form-select" :disabled="isEdit">
                                <option v-for="t in typeOptions" :key="t" :value="t">
                                    {{ t.charAt(0).toUpperCase() + t.slice(1) }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Priorité *</label>
                            <select v-model="form.priority" class="form-select">
                                <option v-for="p in priorityOptions" :key="p" :value="p">
                                    {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                                </option>
                            </select>
                        </div>

                        <!-- Affectation -->
                        <div class="col-12 mt-3">
                            <h6 class="fw-bold text-primary border-bottom pb-2">Affectation</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Client *</label>
                            <select v-model="form.client_id" class="form-select" :disabled="isEdit" required>
                                <option value="" disabled>Sélectionner un client</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">
                                    {{ c.company_name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Assigné à (technicien)</label>
                            <select v-model="form.assigned_to" class="form-select">
                                <option value="">Non assigné</option>
                                <option v-for="t in technicians" :key="t.id" :value="t.id">
                                    {{ t.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="col-12 mt-4 d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" @click="goBack">
                                <i class="bi-arrow-left me-1"></i>Retour
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" :disabled="submitting">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi-save me-1"></i>
                                {{ isEdit ? 'Mettre à jour' : 'Créer le ticket' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </GelLayout>
</template>
