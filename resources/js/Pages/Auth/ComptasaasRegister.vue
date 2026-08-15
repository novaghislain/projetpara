<script setup>
import { ref } from 'vue';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const companyName = ref('');
const nif = ref('');
const firstName = ref('');
const lastName = ref('');
const email = ref('');
const password = ref('');

const submit = () => {
    // Dans une vraie application, cela appellerait l'API
    window.location.href = "/company/dashboard";
};
</script>

<template>
    <AuthLayout type="client" title="Inscription Entreprise | GEL Cabinet">
        <div class="auth-form-wrapper">
            <h2 class="auth-title">Créer votre espace</h2>
            <p class="auth-subtitle">Activez votre compte pour collaborer avec votre expert-comptable.</p>

            <form @submit.prevent="submit" class="auth-form">
                
                <h3 class="section-title">L'Entreprise</h3>
                <div class="form-group">
                    <label class="form-label">Nom de l'entreprise (Raison Sociale)</label>
                    <div class="input-with-icon">
                        <i class="bi bi-building"></i>
                        <input v-model="companyName" type="text" class="form-input" placeholder="Acme Corp" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Numéro IFU (Optionnel)</label>
                    <input v-model="nif" type="text" class="form-input" placeholder="XXXXXXXXXXXXX">
                </div>

                <h3 class="section-title mt-4">Le Gérant / Contact Principal</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input v-model="firstName" type="text" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input v-model="lastName" type="text" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Adresse Email professionnelle</label>
                    <div class="input-with-icon">
                        <i class="bi bi-envelope"></i>
                        <input v-model="email" type="email" class="form-input" placeholder="vous@votre-entreprise.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-with-icon">
                        <i class="bi bi-lock"></i>
                        <input v-model="password" type="password" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    Valider mon inscription
                </button>
            </form>
            
            <div class="auth-register">
                Vous avez déjà un compte ? <a href="/saas/login" class="register-link">Se connecter</a>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.auth-form-wrapper {
    width: 100%;
}

.auth-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.auth-subtitle {
    color: #64748b;
    font-size: 0.95rem;
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.mt-4 {
    margin-top: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.5rem;
}

.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.input-with-icon i {
    position: absolute;
    left: 1rem;
    color: #94a3b8;
    font-size: 1.1rem;
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

.input-with-icon .form-input {
    padding-left: 2.75rem;
}

.form-input:focus {
    outline: none;
    border-color: #10b981;
    background: white;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.btn-submit {
    display: block;
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, #059669, #10b981);
    color: white;
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
    transition: all 0.2s;
    margin-top: 2rem;
    margin-bottom: 2rem;
}

.btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px -2px rgba(16, 185, 129, 0.3);
}

.auth-register {
    text-align: center;
    font-size: 0.95rem;
    color: #475569;
}

.register-link {
    color: #10b981;
    font-weight: 600;
    text-decoration: none;
}

.register-link:hover {
    text-decoration: underline;
}
</style>
