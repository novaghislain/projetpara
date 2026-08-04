<!--
  Composant : Legal/Terms.vue
  Description : Page statique "Conditions d'utilisation" détaillant les droits,
                obligations et responsabilités des utilisateurs du service.
-->
<template>
  <div class="min-h-screen bg-gray-100 py-12 px-4">
    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8 animate-slide-up">
      <h1 class="text-3xl font-bold text-gray-900 mb-6 animate-fade-in">Conditions d'utilisation</h1>
      <p class="text-sm text-gray-500 mb-8">Dernière mise à jour : janvier 2025</p>

      <div class="prose max-w-none">
        <!-- Section : Acceptation -->
        <h2>1. Acceptation des conditions</h2>
        <p>En créant un compte sur ComptaSaaS, vous acceptez les présentes conditions d'utilisation.</p>

        <!-- Section : Description du service -->
        <h2>2. Description du service</h2>
        <p>ComptaSaaS est une solution de comptabilité en ligne conforme au plan comptable SYSCOHADA. Elle permet aux entreprises de gérer leur comptabilité, factures, banque et générer leurs états financiers.</p>

        <!-- Section : Création de compte -->
        <h2>3. Création de compte</h2>
        <p>Lors de l'inscription, vous devez fournir des informations exactes et complètes. Vous êtes responsable de la confidentialité de vos identifiants.</p>

        <!-- Section : Obligations du client -->
        <h2>4. Obligations du client</h2>
        <ul>
          <li>Fournir des informations comptables exactes</li>
          <li>Ne pas utiliser le service à des fins frauduleuses</li>
          <li>Respecter la législation comptable en vigueur dans votre pays</li>
          <li>Conserver vos propres archives conformément à la réglementation</li>
        </ul>

        <!-- Section : Propriété intellectuelle -->
        <h2>5. Propriété intellectuelle</h2>
        <p>Le logiciel ComptaSaaS reste la propriété de l'éditeur. Les données comptables que vous y saisissez vous appartiennent.</p>

        <!-- Section : Limitation de responsabilité -->
        <h2>6. Limitation de responsabilité</h2>
        <p>ComptaSaaS fournit un outil de saisie et de calcul. L'exactitude des écritures comptables et la conformité fiscale relèvent de la responsabilité du comptable ou de l'entreprise utilisatrice.</p>

        <!-- Section : Confidentialité -->
        <h2>7. Confidentialité des données</h2>
        <p>Vos données sont traitées conformément à notre politique de confidentialité. Nous ne les partageons pas avec des tiers sans votre consentement.</p>

        <!-- Section : Résiliation -->
        <h2>8. Résiliation</h2>
        <p>Vous pouvez supprimer votre compte à tout moment. Vos données vous seront fournies sur demande avant suppression définitive.</p>

        <!-- Section : Contact -->
        <h2>9. Contact</h2>
        <p>Pour toute question : <a href="mailto:support@comptasaas.ci" class="text-primary">support@comptasaas.ci</a></p>
      </div>
    </div>
  </div>
</template>
