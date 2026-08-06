import os

paths = [
    "resources/js/Pages/Company/Compta/Dashboard.vue",
    "resources/js/Pages/Company/Compta/Comptes/Index.vue",
    "resources/js/Pages/Company/Compta/Ecritures/Index.vue",
    "resources/js/Pages/Company/Compta/Balance.vue",
    "resources/js/Pages/Company/Compta/Rapports/Bilan.vue",
    "resources/js/Pages/Company/Compta/Rapports/GrandLivre.vue",
    "resources/js/Pages/Company/Compta/Rapports/Resultat.vue",
    "resources/js/Pages/Company/Compta/Rapports/TrialBalance.vue",
    "resources/js/Pages/Company/Compta/Rapports/CashFlow.vue",
    "resources/js/Pages/Company/Compta/Rapports/Aging.vue",
    "resources/js/Pages/Company/Compta/Factures/Index.vue",
    "resources/js/Pages/Company/Compta/Journaux/Index.vue",
    "resources/js/Pages/Company/Compta/Tva/Index.vue",
    "resources/js/Pages/Company/Compta/Banque/Index.vue",
    "resources/js/Pages/Company/Compta/Immobilisations/Index.vue",
    "resources/js/Pages/Company/Compta/Exercices/Index.vue",
]

base_dir = r"c:\xampp\htdocs\Para"
template = """<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Module Compta</h1>
        <p>Ce module est en cours de développement.</p>
    </div>
</template>

<script setup>
</script>
"""

for path in paths:
    full_path = os.path.join(base_dir, path.replace("/", "\\"))
    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    if not os.path.exists(full_path):
        with open(full_path, "w", encoding="utf-8") as f:
            f.write(template)

print("Placeholder components created.")
