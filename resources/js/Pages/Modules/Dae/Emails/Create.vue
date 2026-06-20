<template>
    <div class="dae-emails-create">
        <div class="container-fluid py-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/dae/emails" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="mb-0 fw-bold"><i class="bi bi-chat-dots me-2 text-primary"></i>Nouvel email</h4>
                    <p class="text-muted small mb-0 mt-1">Composez un nouvel email</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Objet</label>
                                    <input type="text" v-model="form.objet" class="form-control" placeholder="Objet" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-medium">Message</label>
                                    <textarea v-model="form.corps_texte" class="form-control" rows="12" placeholder="Ecrivez votre message..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body p-3">
                                <label class="form-label small fw-medium">Client</label>
                                <select v-model="form.client_id" class="form-select form-select-sm">
                                    <option value="">-- Choisir --</option>
                                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.nom || c.email }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">De</label>
                                    <input type="email" v-model="form.from_address" class="form-control form-control-sm" placeholder="votre@email.com" />
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-medium">A</label>
                                    <textarea v-model="form.to_addresses" class="form-control form-control-sm" rows="2" placeholder="destinataire@email.com"></textarea>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-medium">Cc</label>
                                    <textarea v-model="form.cc_addresses" class="form-control form-control-sm" rows="2" placeholder="cc@email.com"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body p-3">
                                <label class="form-label small fw-medium">Dossier</label>
                                <select v-model="form.dossier" class="form-select form-select-sm">
                                    <option value="">-- Aucun --</option>
                                    <option value="Propositions">Propositions</option>
                                    <option value="Comptabilité">Comptabilité</option>
                                    <option value="Commandes">Commandes</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">Envoyer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'DaeEmailsCreate',
    data() {
        return {
            clients: [],
            form: { client_id: '', from_address: '', to_addresses: '', cc_addresses: '', objet: '', corps_texte: '', dossier: '' },
        };
    },
    mounted() {
        console.log('DaeEmailsCreate mounted');
        axios.get('/api/clients').then(r => {
            this.clients = Array.isArray(r.data) ? r.data : (r.data.data || []);
            console.log('Clients loaded:', this.clients.length);
        }).catch(e => {
            console.error('Clients error:', e);
        });
    },
    methods: {
        handleSubmit() {
            axios.post('/dae/emails', {
                client_id: this.form.client_id,
                from_address: this.form.from_address,
                to_addresses: JSON.stringify(this.form.to_addresses.split(',').map(s => s.trim()).filter(Boolean)),
                objet: this.form.objet,
                corps_texte: this.form.corps_texte || null,
                dossier: this.form.dossier || null,
            }).then(() => {
                window.location.href = '/dae/emails';
            }).catch(() => {
                alert('Erreur');
            });
        },
    },
};
</script>

<style scoped>
.dae-emails-create { padding: 20px; min-height: 80vh; }
.form-label { font-weight: 500; }
</style>
