<script setup>
import { ref, onMounted } from 'vue';
import GelLayout from '../../../Layouts/GelLayout.vue';

const props = defineProps({
    articleId: { type: [Number, String], required: true }
});

const article = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchArticle = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await fetch('/api/articles/' + props.articleId);
        if (!res.ok) throw new Error('Erreur lors du chargement de l\'article');
        article.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'long', year: 'numeric'
    });
};

const statusLabels = { published: 'Publie', draft: 'Brouillon', archived: 'Archive' };
const statusColors = { published: 'bg-success', draft: 'bg-warning text-dark', archived: 'bg-secondary' };

const categoryColors = [
    '#1a237e', '#004d40', '#3e2723', '#4a148c',
    '#b71c1c', '#01579b', '#33691e', '#e65100', '#4e342e'
];

const tagBgColor = (index) => categoryColors[index % categoryColors.length];

onMounted(fetchArticle);
</script>

<template>
    <GelLayout :page-title="article?.title || 'Article'">
        <!-- Loading -->
        <div v-if="loading" class="d-flex justify-content-center py-5">
            <div class="spinner-border text-primary">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <template v-else-if="article">
            <!-- Back button -->
            <div class="mb-3">
                <a href="/articles" class="btn btn-sm btn-outline-secondary">
                    <i class="bi-arrow-left me-1"></i>Retour aux articles
                </a>
            </div>

            <!-- Featured image -->
            <div v-if="article.featured_image" class="mb-4 rounded-3 overflow-hidden" style="max-height:400px;">
                <img :src="article.featured_image" :alt="article.title"
                     class="w-100"
                     style="object-fit:cover; object-position:center;">
            </div>

            <!-- Article header card -->
            <div class="bg-white rounded-lg shadow p-6 mb-4">
                <!-- Category badge & status -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span v-if="article.category_name" class="badge"
                          style="background:#e8eaf6;color:#1a237e;font-size:13px;font-weight:600;">
                        {{ article.category_name }}
                    </span>
                    <span v-if="article.status" class="badge" :class="statusColors[article.status] || 'bg-secondary'">
                        {{ statusLabels[article.status] || article.status }}
                    </span>
                </div>

                <!-- Title -->
                <h2 class="fw-bold mb-3">{{ article.title }}</h2>

                <!-- Meta: author & date -->
                <div class="d-flex flex-wrap align-items-center text-muted gap-3 mb-0">
                    <span v-if="article.author_name">
                        <i class="bi-person me-1"></i>{{ article.author_name }}
                    </span>
                    <span v-if="article.published_at">
                        <i class="bi-calendar3 me-1"></i>{{ formatDate(article.published_at) }}
                    </span>
                    <span v-if="article.reading_minutes">
                        <i class="bi-clock me-1"></i>{{ article.reading_minutes }} min de lecture
                    </span>
                </div>

                <!-- Tags -->
                <div v-if="article.tags && article.tags.length" class="mt-3">
                    <span v-for="(tag, index) in article.tags" :key="index"
                          class="badge me-1 mb-1"
                          :style="{ backgroundColor: tagBgColor(index), color: '#fff', fontWeight: 500, fontSize: '12px' }">
                        {{ tag }}
                    </span>
                </div>
                <div v-else-if="typeof article.tags === 'string' && article.tags" class="mt-3">
                    <span v-for="(tag, index) in article.tags.split(',').map(t => t.trim()).filter(Boolean)"
                          :key="index"
                          class="badge me-1 mb-1"
                          :style="{ backgroundColor: tagBgColor(index), color: '#fff', fontWeight: 500, fontSize: '12px' }">
                        {{ tag }}
                    </span>
                </div>
            </div>

            <div class="row g-4">
                <!-- Main content -->
                <div class="col-lg-8">
                    <div class="bg-white rounded-lg shadow">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0">
                                <i class="bi-file-text me-2 text-primary"></i>Contenu
                            </h6>
                        </div>
                        <div class="article-content" v-html="article.content || '<p class=\'text-muted\'>Aucun contenu.</p>'">
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Excerpt -->
                    <div v-if="article.excerpt" class="bg-white rounded-lg shadow p-6 mb-4">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0">
                                <i class="bi-quote me-2 text-info"></i>Resume
                            </h6>
                        </div>
                        <p class="small mb-0 text-muted fst-italic">{{ article.excerpt }}</p>
                    </div>

                    <!-- Slug -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="card-header bg-white">
                            <h6 class="fw-bold mb-0">
                                <i class="bi-link-45deg me-2 text-secondary"></i>Informations
                            </h6>
                        </div>
                        <div v-if="article.slug" class="mb-2">
                            <span class="small text-muted d-block">Slug</span>
                            <code class="small">{{ article.slug }}</code>
                        </div>
                        <div v-if="article.category_name">
                            <span class="small text-muted d-block">Categorie</span>
                            <span class="small">{{ article.category_name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </GelLayout>
</template>

<style scoped>
.article-content :deep(h1),
.article-content :deep(h2),
.article-content :deep(h3),
.article-content :deep(h4) {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    font-weight: 700;
    color: #1a1a2e;
}

.article-content :deep(p) {
    margin-bottom: 1rem;
    line-height: 1.8;
    color: #333;
}

.article-content :deep(ul),
.article-content :deep(ol) {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.article-content :deep(li) {
    margin-bottom: 0.35rem;
    line-height: 1.7;
}

.article-content :deep(blockquote) {
    border-left: 4px solid #FF7900;
    padding: 0.75rem 1rem;
    margin: 1rem 0;
    background: #fff8f0;
    border-radius: 0 6px 6px 0;
    color: #555;
    font-style: italic;
}

.article-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
}

.article-content :deep(a) {
    color: #FF7900;
    text-decoration: underline;
}

.article-content :deep(a:hover) {
    color: #cc6000;
}

.article-content :deep(hr) {
    margin: 1.5rem 0;
    opacity: 0.25;
}

.article-content :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.article-content :deep(th),
.article-content :deep(td) {
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    text-align: left;
}

.article-content :deep(th) {
    background: #f8f9fa;
    font-weight: 600;
}

.article-content :deep(pre) {
    background: #1e1e2e;
    color: #cdd6f4;
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 13px;
    line-height: 1.5;
    margin: 1rem 0;
}

.article-content :deep(code) {
    font-family: 'Cascadia Code', 'Fira Code', 'Consolas', monospace;
    font-size: 0.875em;
    background: #f0f0f0;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    color: #d63384;
}

.article-content :deep(pre code) {
    background: transparent;
    padding: 0;
    color: inherit;
    border-radius: 0;
}
</style>
