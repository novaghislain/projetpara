<script setup>
import { ref } from 'vue';
import PublicLayout from '../../Layouts/Public/PublicLayout.vue';

const step = ref(1);
const totalSteps = 3;

// Simulated form data
const formData = ref({
    companyName: '',
    industry: '',
    contactName: '',
    email: '',
    phone: '',
    notes: ''
});

const nextStep = () => {
    if (step.value < totalSteps) step.value++;
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

const submitOrder = () => {
    // Dans une vraie application, cela appellerait l'API
    window.location.href = "/cart";
};
</script>

<template>
    <PublicLayout title="Commander | GEL Cabinet">
        <div class="wizard-container">
            <div class="wizard-header">
                <a href="/catalogue/1" class="back-link"><i class="bi bi-arrow-left"></i> Retour au service</a>
                <h1 class="wizard-title">Finalisez votre commande</h1>
                <p class="wizard-subtitle">Création d'entreprise (SARL, SAS, SUARL)</p>
            </div>

            <!-- Progress Bar -->
            <div class="wizard-progress">
                <div class="progress-track">
                    <div class="progress-fill" :style="{ width: ((step - 1) / (totalSteps - 1)) * 100 + '%' }"></div>
                </div>
                <div class="progress-steps">
                    <div class="step-indicator" :class="{ 'is-active': step >= 1, 'is-complete': step > 1 }">
                        <div class="step-circle">1</div>
                        <div class="step-label">Vos Besoins</div>
                    </div>
                    <div class="step-indicator" :class="{ 'is-active': step >= 2, 'is-complete': step > 2 }">
                        <div class="step-circle">2</div>
                        <div class="step-label">Coordonnées</div>
                    </div>
                    <div class="step-indicator" :class="{ 'is-active': step >= 3 }">
                        <div class="step-circle">3</div>
                        <div class="step-label">Vérification</div>
                    </div>
                </div>
            </div>

            <!-- Forms -->
            <div class="wizard-card">
                <!-- Step 1 -->
                <div v-if="step === 1" class="step-content">
                    <h2 class="step-title">Détails de votre demande</h2>
                    <div class="form-group">
                        <label class="form-label">Nom envisagé pour l'entreprise</label>
                        <input v-model="formData.companyName" type="text" class="form-input" placeholder="Ex: Acme Corp">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Secteur d'activité</label>
                        <select v-model="formData.industry" class="form-input">
                            <option value="" disabled>Sélectionnez un secteur...</option>
                            <option>Commerce général</option>
                            <option>Prestations de services</option>
                            <option>BTP / Construction</option>
                            <option>Informatique / Tech</option>
                            <option>Autre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Informations complémentaires</label>
                        <textarea v-model="formData.notes" class="form-input" rows="4" placeholder="Précisez la forme juridique souhaitée ou toute autre question..."></textarea>
                    </div>
                </div>

                <!-- Step 2 -->
                <div v-if="step === 2" class="step-content">
                    <h2 class="step-title">Vos coordonnées de contact</h2>
                    <div class="form-group">
                        <label class="form-label">Nom complet du dirigeant</label>
                        <input v-model="formData.contactName" type="text" class="form-input" placeholder="Ex: Jean Dupont">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Adresse Email</label>
                            <input v-model="formData.email" type="email" class="form-input" placeholder="jean@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input v-model="formData.phone" type="tel" class="form-input" placeholder="+229 ...">
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div v-if="step === 3" class="step-content">
                    <h2 class="step-title">Récapitulatif de votre commande</h2>
                    
                    <div class="summary-box">
                        <div class="summary-item">
                            <span class="summary-label">Service :</span>
                            <span class="summary-value">Création d'entreprise (SARL, SAS, SUARL)</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Prix de base :</span>
                            <span class="summary-value">À partir de 150 000 FCFA</span>
                        </div>
                        <hr class="summary-divider">
                        <div class="summary-item">
                            <span class="summary-label">Entreprise :</span>
                            <span class="summary-value">{{ formData.companyName || 'Non spécifié' }} ({{ formData.industry || 'Non spécifié' }})</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Contact :</span>
                            <span class="summary-value">{{ formData.contactName }} ({{ formData.email }})</span>
                        </div>
                    </div>

                    <div class="info-alert">
                        <i class="bi bi-info-circle-fill"></i>
                        <p>En ajoutant au panier, un expert GEL analysera votre demande et préparera un contrat final via l'espace client.</p>
                    </div>
                </div>

                <!-- Footer / Actions -->
                <div class="wizard-footer">
                    <button class="btn btn--outline" @click="prevStep" :disabled="step === 1" :style="{ opacity: step === 1 ? '0' : '1', pointerEvents: step === 1 ? 'none' : 'auto' }">
                        Étape précédente
                    </button>
                    
                    <button v-if="step < totalSteps" class="btn btn--primary" @click="nextStep">
                        Étape suivante <i class="bi bi-arrow-right" style="margin-left: 0.5rem;"></i>
                    </button>
                    
                    <button v-if="step === totalSteps" class="btn btn--success" @click="submitOrder">
                        Ajouter au Panier <i class="bi bi-cart-plus" style="margin-left: 0.5rem;"></i>
                    </button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.wizard-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 3rem 2rem 5rem;
    min-height: calc(100vh - 80px);
}

.wizard-header {
    text-align: center;
    margin-bottom: 3rem;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 1.5rem;
    transition: color 0.2s;
}

.back-link:hover {
    color: #0f172a;
}

.wizard-title {
    font-size: 2.25rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.wizard-subtitle {
    color: #3b82f6;
    font-size: 1.125rem;
    font-weight: 600;
}

/* Progress */
.wizard-progress {
    position: relative;
    margin-bottom: 4rem;
    padding: 0 2rem;
}

.progress-track {
    position: absolute;
    top: 1rem;
    left: 4rem;
    right: 4rem;
    height: 4px;
    background: #e2e8f0;
    z-index: 1;
    border-radius: 2px;
}

.progress-fill {
    height: 100%;
    background: #3b82f6;
    border-radius: 2px;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 2;
}

.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    color: #94a3b8;
    transition: color 0.3s;
}

.step-circle {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: white;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 600;
}

.step-indicator.is-active {
    color: #3b82f6;
}

.step-indicator.is-active .step-circle {
    border-color: #3b82f6;
    background: #3b82f6;
    color: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.step-indicator.is-complete {
    color: #0f172a;
}

.step-indicator.is-complete .step-circle {
    border-color: #3b82f6;
    background: white;
    color: #3b82f6;
}

/* Card */
.wizard-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.step-content {
    padding: 3rem;
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.step-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

/* Forms */
.form-group {
    margin-bottom: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    font-family: inherit;
    font-size: 1rem;
    color: #0f172a;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Summary */
.summary-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    font-size: 1.05rem;
}

.summary-item:last-child {
    margin-bottom: 0;
}

.summary-label {
    color: #64748b;
}

.summary-value {
    color: #0f172a;
    font-weight: 600;
}

.summary-divider {
    border: none;
    border-top: 1px dashed #cbd5e1;
    margin: 1.5rem 0;
}

.info-alert {
    display: flex;
    gap: 1rem;
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 1.25rem;
    border-radius: 0 0.5rem 0.5rem 0;
    color: #1e40af;
    line-height: 1.5;
}

.info-alert i {
    font-size: 1.25rem;
}

/* Footer */
.wizard-footer {
    padding: 2rem 3rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn--outline {
    background: white;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.btn--outline:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.btn--primary {
    background: #0f172a;
    color: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn--primary:hover {
    background: #1e293b;
    transform: translateY(-1px);
}

.btn--success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
}

.btn--success:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px -2px rgba(16, 185, 129, 0.3);
}

@media (max-width: 640px) {
    .step-content, .wizard-footer {
        padding: 1.5rem;
    }
    .form-grid {
        grid-template-columns: 1fr;
    }
    .wizard-progress {
        display: none; /* Hide progress bar on very small screens to save space */
    }
}
</style>
