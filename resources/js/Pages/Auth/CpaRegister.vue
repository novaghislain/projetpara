<script setup>
import { ref } from 'vue';
import AuthLayout from '../../Layouts/AuthLayout.vue';

const firstName = ref('');
const lastName = ref('');
const email = ref('');
const password = ref('');
const role = ref('collaborator');

const submit = () => {
    // Dans une vraie application, cela appellerait l'API
    window.location.href = "/gel/dashboard";
};
</script>

<template>
    <AuthLayout type="cabinet" title="Inscription Cabinet | GEL Cabinet">
        <div class="auth-form-wrapper">
            <h2 class="auth-title">Créer un compte</h2>
            <p class="auth-subtitle">Rejoignez l'équipe du cabinet d'expertise.</p>

            <form @submit.prevent="submit" class="auth-form">
                
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
                    <label class="form-label">Adresse Email</label>
                    <div class="input-with-icon">
                        <i class="bi bi-envelope"></i>
                        <input v-model="email" type="email" class="form-input" placeholder="prenom.nom@gelcabinet.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <div class="input-with-icon">
                        <i class="bi bi-lock"></i>
                        <input v-model="password" type="password" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Rôle demandé</label>
                    <select v-model="role" class="form-input" required>
                        <option value="collaborator">Collaborateur / Assistant</option>
                        <option value="accountant">Comptable</option>
                        <option value="secretary">Secrétaire</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">
                    Demander l'accès
                </button>
            </form>
            
            <div class="auth-register">
                Vous avez déjà un compte ? <a href="/cpa/login" class="register-link">Se connecter</a>
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
    margin-bottom: 2.5rem;
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
    border-color: #2563eb;
    background: white;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.btn-submit {
    display: block;
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: white;
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
    transition: all 0.2s;
    margin-top: 2rem;
    margin-bottom: 2rem;
}

.btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px -2px rgba(37, 99, 235, 0.3);
}

.auth-register {
    text-align: center;
    font-size: 0.95rem;
    color: #475569;
}

.register-link {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
}

.register-link:hover {
    text-decoration: underline;
}
</style>
