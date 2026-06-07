<script setup>
defineProps({
    title: String,
    subtitle: String,
    image: String
})
</script>

<template>
    <section class="page-hero position-relative overflow-hidden py-5 d-flex align-items-center" :style="{ minHeight: '300px' }">
        <!-- Background Image with Overlay -->
        <div class="position-absolute top-0 start-0 w-100 h-100 z-0">
            <img :src="image" :alt="title" class="w-100 h-100 object-fit-cover" />
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-30"></div>
            <div class="position-absolute top-0 start-0 w-100 h-100 hero-gradient"></div>
        </div>

        <div class="container position-relative z-1 text-white">
            <div class="row">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/" class="text-white text-opacity-75 text-decoration-none">Accueil</a></li>
                            <li class="breadcrumb-item active text-white fw-bold" aria-current="page">{{ title }}</li>
                        </ol>
                    </nav>
                    <h1 class="display-4 fw-bold mb-3 ls-tight">{{ title }}</h1>
                    <p class="lead text-white text-opacity-90 mb-0 pe-lg-5 col-md-10 fs-5">
                        {{ subtitle }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Bottom Curve -->
        <div class="position-absolute bottom-0 start-0 w-100 overflow-hidden" style="line-height: 0; transform: translateY(1px)">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="position: relative; display: block; width: calc(100% + 1.3px); height: 40px; fill: #ffffff;">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,29.39,172,38.62,39.19,6.16,76.84-4.43,113.88-19.22,31-12.38,61.16-27,90.69-42.34V0Z" opacity=".5"></path>
                <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
            </svg>
        </div>
    </section>
</template>

<style scoped>
.page-hero {
    background-color: #1a1a1a; /* Dark base for better contrast during load */
}
.ls-tight {
    letter-spacing: -0.02em;
}
.opacity-30 { opacity: 0.3; }
.hero-gradient {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, rgba(27, 94, 32, 0.8) 0%, rgba(27, 94, 32, 0.4) 50%, transparent 100%);
}
</style>
