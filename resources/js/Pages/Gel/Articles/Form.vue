<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const props = defineProps({
    article: { type: Object, default: null },
});

const isEditing = computed(() => !!props.article);

const pageTitle = computed(() => isEditing.value ? "Modifier l'article" : 'Nouvel article');

const submitting = ref(false);
const errors = ref({});
const success = ref('');

// ── Preset categories ──
const categories = [
    { value: 'actualites',   label: 'Actualités' },
    { value: 'blog',         label: 'Blog' },
    { value: 'tutoriel',     label: 'Tutoriel' },
    { value: 'juridique',    label: 'Juridique' },
    { value: 'fiscal',       label: 'Fiscal' },
    { value: 'social',       label: 'Social' },
    { value: 'comptabilite', label: 'Comptabilité' },
    { value: 'technologie',  label: 'Technologie' },
    { value: 'general',      label: 'Général' },
];

// ── Form data ──
const form = ref({
    title: '',
    slug: '',
    category: '',
    content: '',
    excerpt: '',
    author: '',
    reading_minutes: 5,
    is_published: false,
    tags: '',
});

// ── Slug auto-generation ──
let slugManuallyEdited = false;

watch(() => form.value.title, (newTitle) => {
    if (!slugManuallyEdited && !isEditing.value) {
        form.value.slug = newTitle
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});

const markSlugEdited = () => {
    slugManuallyEdited = true;
};

// ── Populate form when editing ──
const loadArticle = () => {
    if (!props.article) return;
    const a = props.article;
    form.value = {
        title: a.title || '',
        slug: a.slug || '',
        category: a.category || '',
        content: a.content || '',
        excerpt: a.excerpt || '',
        author: a.author || '',
        reading_minutes: a.reading_minutes || 5,
        is_published: a.is_published || false,
        tags: Array.isArray(a.tags) ? a.tags.join(', ') : (a.tags || ''),
    };
    if (form.value.slug) slugManuallyEdited = true;
};

onMounted(loadArticle);

// ── Submit ──
const submitForm = async () => {
    submitting.value = true;
    errors.value = {};
    success.value = '';
    try {
        const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
        const url = isEditing.value ? '/api/articles/' + props.article.id : '/api/articles';
        const method = isEditing.value ? 'PUT' : 'POST';

        const payload = { ...form.value };
        // Normalise tags from comma-separated string to array
        payload.tags = payload.tags
            .split(',')
            .map(t => t.trim())
            .filter(t => t.length > 0);
        // Ensure is_published is boolean
        payload.is_published = !!payload.is_published;

        const res = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (!res.ok) {
            const errData = await res.json();
            if (errData.errors) {
                errors.value = errData.errors;
                const msgs = Object.values(errData.errors).flat();
                throw new Error(msgs.join(', '));
            }
            throw new Error(errData.message || "Erreur lors de l'enregistrement");
        }

        success.value = isEditing.value
            ? 'Article mis à jour avec succès.'
            : 'Article créé avec succès.';

        setTimeout(() => {
            window.location.href = '/articles';
        }, 1500);
    } catch (e) {
        alert('Erreur : ' + e.message);
    } finally {
        submitting.value = false;
    }
};

// ── Field error helper ──
const fieldError = (field) => {
    return errors.value[field] ? errors.value[field].join(', ') : '';
};
</script>

<template>
    <GelLayout :page-title="pageTitle">

        <div class="container-fluid py-3">

            <!-- Success banner -->
            <div v-if="success" class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="bi-check-circle-fill"></i>
                {{ success }}
            </div>

            <!-- Main container -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span>
                        <i :class="isEditing ? 'bi-pencil' : 'bi-plus-lg'" class="me-2"></i>
                        {{ isEditing ? "Modifier l'article" : 'Nouvel article' }}
                    </span>
                    <a href="/articles" class="btn btn-sm btn-outline-light">
                        <i class="bi-arrow-left me-1"></i>Retour
                    </a>
                </div>

                <form @submit.prevent="submitForm" novalidate>

                    <div class="row g-3">

                        <!-- ── Title ── -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">
                                Titre <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldError('title') }"
                                placeholder="Titre de l'article"
                                required
                            />
                            <div v-if="fieldError('title')" class="invalid-feedback">
                                {{ fieldError('title') }}
                            </div>
                        </div>

                        <!-- ── Slug ── -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Slug</label>
                            <input
                                v-model="form.slug"
                                type="text"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldError('slug') }"
                                placeholder="titre-de-l-article"
                                @input="markSlugEdited"
                            />
                            <div v-if="fieldError('slug')" class="invalid-feedback">
                                {{ fieldError('slug') }}
                            </div>
                            <small class="text-muted">Laissez vide pour génération automatique</small>
                        </div>

                        <!-- ── Category ── -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">
                                Catégorie <span class="text-danger">*</span>
                            </label>
                            <select
                                v-model="form.category"
                                class="form-select form-select-sm"
                                :class="{ 'is-invalid': fieldError('category') }"
                                required
                            >
                                <option value="" disabled>Sélectionner une catégorie</option>
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                    {{ cat.label }}
                                </option>
                            </select>
                            <div v-if="fieldError('category')" class="invalid-feedback">
                                {{ fieldError('category') }}
                            </div>
                        </div>

                        <!-- ── Author ── -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Auteur</label>
                            <input
                                v-model="form.author"
                                type="text"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldError('author') }"
                                placeholder="Nom de l'auteur"
                            />
                            <div v-if="fieldError('author')" class="invalid-feedback">
                                {{ fieldError('author') }}
                            </div>
                        </div>

                        <!-- ── Reading minutes ── -->
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">
                                Temps de lecture (min)
                            </label>
                            <input
                                v-model.number="form.reading_minutes"
                                type="number"
                                min="1"
                                max="120"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldError('reading_minutes') }"
                            />
                            <div v-if="fieldError('reading_minutes')" class="invalid-feedback">
                                {{ fieldError('reading_minutes') }}
                            </div>
                        </div>

                        <!-- ── Is published ── -->
                        <div class="col-md-2 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input
                                    v-model="form.is_published"
                                    type="checkbox"
                                    class="form-check-input"
                                    :class="{ 'is-invalid': fieldError('is_published') }"
                                    id="is_published"
                                />
                                <label class="form-check-label small fw-semibold" for="is_published">
                                    Publié
                                </label>
                                <div v-if="fieldError('is_published')" class="invalid-feedback">
                                    {{ fieldError('is_published') }}
                                </div>
                            </div>
                        </div>

                        <!-- ── Excerpt ── -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Extrait / Résumé</label>
                            <textarea
                                v-model="form.excerpt"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldError('excerpt') }"
                                rows="3"
                                placeholder="Bref résumé de l'article (apparaît dans les listes et le SEO)"
                                maxlength="500"
                            ></textarea>
                            <div class="d-flex justify-content-between">
                                <small v-if="fieldError('excerpt')" class="text-danger">{{ fieldError('excerpt') }}</small>
                                <small class="text-muted">{{ (form.excerpt || '').length }} / 500</small>
                            </div>
                        </div>

                        <!-- ── Content ── -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Contenu <span class="text-danger">*</span>
                            </label>
                            <textarea
                                v-model="form.content"
                                class="form-control"
                                :class="{ 'is-invalid': fieldError('content') }"
                                rows="14"
                                placeholder="Rédigez le contenu de l'article ici..."
                                required
                            ></textarea>
                            <div v-if="fieldError('content')" class="invalid-feedback">
                                {{ fieldError('content') }}
                            </div>
                        </div>

                        <!-- ── Tags ── -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Tags
                                <span class="text-muted fw-normal">(séparés par des virgules)</span>
                            </label>
                            <input
                                v-model="form.tags"
                                type="text"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': fieldError('tags') }"
                                placeholder="ex: fiscalité, entreprise, 2025"
                            />
                            <div v-if="fieldError('tags')" class="invalid-feedback">
                                {{ fieldError('tags') }}
                            </div>
                            <small class="text-muted">
                                Saisissez les mots-clés séparés par des virgules
                            </small>
                        </div>

                    </div><!-- /row -->

                    <!-- ── Actions ── -->
                    <hr class="my-4" />
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="/articles" class="btn btn-sm btn-outline-secondary">
                            <i class="bi-x-lg me-1"></i>Annuler
                        </a>
                        <button
                            type="submit"
                            class="btn btn-sm btn-primary"
                            :disabled="submitting"
                        >
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else :class="isEditing ? 'bi-check-lg' : 'bi-plus-lg'" class="me-1"></i>
                            {{ isEditing ? 'Mettre à jour' : "Créer l'article" }}
                        </button>
                    </div>

                </form>

            </div><!-- /main -->

        </div><!-- /container-fluid -->

    </GelLayout>
</template>
