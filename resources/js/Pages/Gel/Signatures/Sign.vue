<template>
    <GelLayout>
        <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-3">
                    <h5 class="mb-0"><i class="bi-pen me-2"></i>Signer le document</h5>
                </div>
                <div class="p-3">
                    <div v-if="signature.signed_at" class="alert alert-success">
                        <i class="bi-check-circle me-2"></i>Ce document a déjà été signé le {{ new Date(signature.signed_at).toLocaleString('fr-FR') }}.
                    </div>
                    <template v-else>
                        <p><strong>Document :</strong> {{ signature.document?.title || 'N/A' }}</p>
                        <p><strong>Signataire :</strong> {{ signature.signer_name }} ({{ signature.signer_email }})</p>
                        <div class="mb-3">
                            <label class="form-label fw500">Signature (dessinez ci-dessous)</label>
                            <canvas ref="canvas" width="500" height="200" style="border:2px solid #e2e8f0;border-radius:8px;cursor:crosshair;width:100%;max-width:500px;touch-action:none;" @mousedown="startDraw" @mousemove="draw" @mouseup="stopDraw" @touchstart.prevent="startDrawTouch" @touchmove.prevent="drawTouch" @touchend="stopDraw"></canvas>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw500">Confirmez votre nom complet</label>
                            <input class="form-control form-control-sm" v-model="signerName" required>
                        </div>
                        <button @click="submit" class="btn btn-primary" :disabled="submitting">
                            <i class="bi-check-circle me-1"></i> {{ submitting ? 'Envoi...' : 'Confirmer la signature' }}
                        </button>
                        <button @click="clearCanvas" class="btn btn-light ms-2"><i class="bi-arrow-counterclockwise me-1"></i> Effacer</button>
                    </template>
                </div>
    </GelLayout>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import GelLayout from '../../../Layouts/GelLayout.vue'
const props = defineProps({ signature: { type: Object, required: true } })
const canvas = ref(null)
const signerName = ref(props.signature?.signer_name || '')
const submitting = ref(false)
let drawing = false, ctx = null
onMounted(() => { if (canvas.value) ctx = canvas.value.getContext('2d') })
const getPos = (e) => { const r = canvas.value.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top } }
const startDraw = (e) => { drawing = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y) }
const draw = (e) => { if (!drawing) return; const p = getPos(e); ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#111827'; ctx.lineTo(p.x, p.y); ctx.stroke() }
const stopDraw = () => { drawing = false }
const startDrawTouch = (e) => { drawing = true; const t = e.touches[0]; const r = canvas.value.getBoundingClientRect(); ctx.beginPath(); ctx.moveTo(t.clientX - r.left, t.clientY - r.top) }
const drawTouch = (e) => { if (!drawing) return; const t = e.touches[0]; const r = canvas.value.getBoundingClientRect(); ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#111827'; ctx.lineTo(t.clientX - r.left, t.clientY - r.top); ctx.stroke() }
const clearCanvas = () => { if (ctx) ctx.clearRect(0, 0, 500, 200) }
const submit = async () => {
    if (!signerName.value) return alert('Veuillez confirmer votre nom.')
    if (!canvas.value) return
    const dataUrl = canvas.value.toDataURL('image/png')
    submitting.value = true
    const res = await fetch(`/signature/${props.signature.token}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }, body: JSON.stringify({ signature_data: dataUrl, signer_name: signerName.value }) })
    if (res.ok) { window.location.reload() } else { alert('Erreur lors de la signature.'); submitting.value = false }
}
</script>
