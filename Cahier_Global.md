# Cahier des Charges Global

GEL — CAHIER DES CHARGES GLOBAL DU PROJET
Écosystème numérique de gestion externalisée, comptable, fiscale et intelligente des entreprises — Espace OHADA, priorité Bénin
Version consolidée — Août 2026 Rédigé par : Lauclair Hazaël Fiogbe, Consultant en Comptabilité, Fiscalité et Finance d’Entreprise (fusion et structuration comptable/fiscale du projet) Statut : Document de référence pour le développement — fusionne l’ensemble des cahiers des charges, notes de travail et maquettes transmis à ce jour.
Note méthodologique — comment ce document a été construit
Ce cahier des charges résulte de la fusion réelle (et non de la juxtaposition) des documents suivants :
Cartographie des redondances et arbitrages effectués (Étapes 1 à 4 du prompt maître) :
Les deux cahiers des charges décrivaient le même module Secrétariat avec des noms différents (« GEL-SECRÉTARIAT » vs « Secrétariat virtuel / Bureau du secrétaire ») → fusionnés en un seul module, en conservant le detail le plus complet des deux (workflows courrier à 6 étapes + mode Institution/GEC).
Le document « GEL SABINET » utilisait le terme « IRPP » dans une version antérieure des échanges avec le développeur ; il est explicitement corrigé dans le document lui-même en ITS (Impôt sur les Traitements et Salaires), qui est le terme exact du CGI béninois. Ce document reprend cette correction et l’applique partout.
Les deux documents proposaient des modèles économiques différents (« 3 modèles d’usage » dans le premier vs « profils utilisateurs collaboratifs » dans le second) → fusionnés : les 3 modèles d’usage (Section 15) structurent la facturation et le rattachement, les profils utilisateurs (Section 8) structurent les droits d’accès — les deux logiques sont complémentaires, pas contradictoires.
Le premier document proposait un plan comptable SYSCOHADA générique sans détail des classes ; le second listait des entités comptables (PieceComptable, Declaration, EtatFinancier) sans les relier au plan comptable → fusionnés et complétés avec la nomenclature officielle des 9 classes (Partie 10), absente des deux documents source.
Aucun des deux documents ne proposait de moteur fiscal multi-pays ni de moteur d’IA comptable/fiscale détaillé : ce sont les apports structurants de ce cahier des charges (Parties 11 à 13), conformément à la mission du comptable-projet. Ils sont marqués « Proposition complémentaire » lorsqu’ils ne proviennent d’aucun document source.
Le nom commercial du projet varie selon les documents (GEL, GEL SABINET, GEL Accountant, GEL Business) : ce document retient GEL comme nom de la plateforme globale, GEL Secrétariat et GEL Comptabilité (anciennement « GEL Accountant », confirmé par les captures d’écran) comme noms des deux portails métier historiques, en cohérence avec les écrans déjà prototypés.
Distinction des sources dans tout le document (Règle 5 du prompt maître), matérialisée par les pastilles suivantes :
🟢 [Document] — provient explicitement d’un cahier des charges transmis
🔵 [Réglementaire] — provient d’un texte officiel (AUDCIF, CGI, loi de finances)
🟠 [Proposition complémentaire] — ajout nécessaire à la cohérence du système, non présent dans les documents source
1. Résumé exécutif
GEL est un écosystème numérique intégré de pilotage, de conformité et de gestion externalisée des entreprises, conçu prioritairement pour les TPE/PME de l’espace OHADA, avec le Bénin comme marché de déploiement prioritaire. 🟢[Document]
GEL n’est ni un simple site web, ni un simple logiciel de comptabilité : c’est, selon le principe directeur transmis à l’équipe technique, « le système d’exploitation d’une entreprise ». 🟢[Document] La plateforme permet à une entreprise de fonctionner pleinement — secrétariat, comptabilité, fiscalité, trésorerie, stocks, immobilisations, RH — sans disposer nécessairement d’une administration interne physique, en s’appuyant soit sur son propre personnel, soit sur une équipe virtuelle GEL, soit sur des professionnels indépendants référencés.
Le cœur métier de ce cahier des charges, développé et structuré par un professionnel comptable, est le module de comptabilité, fiscalité et finance d’entreprise, construit sur le référentiel SYSCOHADA révisé (AUDCIF 2017) 🔵[Réglementaire] et sur un moteur fiscal paramétrable par pays qui permet d’appliquer, dès le lancement, les règles fiscales béninoises (CGI, loi de finances 2026) tout en préparant l’extension à l’ensemble des 17 États membres de l’OHADA sans réécriture du socle applicatif. 🟠[Proposition complémentaire]
L’intelligence artificielle constitue le second pilier stratégique : elle n’est pas un chatbot ajouté à la marge, mais un ensemble de capacités intégrées à chaque processus métier — lecture et classification automatique des pièces, proposition d’écritures comptables et de traitements fiscaux, détection d’anomalies, assistant de clôture, copilote conversationnel explicable — toujours sous validation humaine pour les actions comptables et fiscales significatives. 🟢[Document]
L’interface déjà prototypée (portail « GEL Accountant », confirmé par les captures d’écran fournies) reprend consciemment les codes de QuickBooks Online, avec une organisation Clients / Fournisseurs / Équipe / Comptabilité déjà opérationnelle en français, sur laquelle ce cahier des charges vient documenter l’ensemble du moteur comptable, fiscal et IA sous-jacent.
2. Vision du projet
GEL se positionne comme la première plateforme africaine de pilotage, de conformité et de gestion externalisée des entreprises, capable de donner aux TPE/PME béninoises et, à terme, à celles de tout l’espace OHADA, l’équivalent numérique d’une direction administrative, financière et comptable qu’elles n’ont pas les moyens de recruter en interne. 🟢[Document]
L’objectif à long terme est de créer le premier écosystème numérique de gestion des entreprises en Afrique francophone, où une entreprise peut créer sa structure, gérer son administration, sa comptabilité, ses stocks, communiquer avec son équipe, suivre ses performances, collaborer avec des professionnels indépendants référencés et développer son activité — le tout depuis une seule plateforme, accessible sur ordinateur comme sur téléphone. 🟢[Document]
2.1 Les sept pôles d’activité (périmètre métier global)
🟢[Document] Conformément au cahier des charges global transmis, le présent document couvre en priorité les pôles 1, 2 et 3, qui constituent le cœur du produit numérique, en intégrant également en détail les pôles 4 (stocks) et 5 (digitalisation via GED/IA) dans la mesure où ils sont indissociables de la comptabilité.
2.2 La Méthode GEL® — les 10 piliers
🟢[Document] Cadre stratégique déjà défini, dont la traduction fonctionnelle est reprise et complétée dans ce document :
3. Contexte et problématique
🟢[Document] Au Bénin comme dans l’ensemble de l’espace UEMOA/OHADA, une grande majorité de PME :
n’ont pas les moyens de recruter un secrétariat ou un service comptable dédié à temps plein ;
ne disposent pas de bureaux adaptés ni des moyens d’en louer ;
perdent leurs documents administratifs et comptables faute d’organisation ;
oublient leurs échéances fiscales et sociales (TVA, ITS, patente, TPS, CNSS) ;
travaillent sans structuration ni traçabilité de leurs activités administratives et comptables ;
n’ont pas accès, faute de moyens, à un accompagnement comptable et fiscal réactif et fiable ;
subissent des risques de non-conformité (retards de déclaration, erreurs de taux, pénalités) faute d’un dispositif de veille.
🟠[Proposition complémentaire] À cela s’ajoute une problématique spécifique à l’espace OHADA que ne traitaient pas les documents source de façon opérationnelle : le référentiel comptable SYSCOHADA est commun à 17 pays, mais la fiscalité, elle, ne l’est pas. Un logiciel construit sur un seul pays (le Bénin) ne pourrait pas être déployé ailleurs sans réécriture complète — ce qui contredirait l’ambition affichée de couverture panafricaine. Le problème à résoudre n’est donc pas seulement « informatiser un cabinet comptable », mais construire une architecture qui sépare structurellement le socle comptable commun (OHADA) du moteur fiscal variable (par pays et par année).
4. Objectifs généraux et spécifiques
4.1 Objectifs généraux
Donner à toute entreprise de l’espace OHADA, quelle que soit sa taille, un accès à une direction administrative, comptable et financière externalisée de qualité professionnelle. 🟢[Document]
Garantir la conformité comptable (SYSCOHADA/AUDCIF) et fiscale (droit interne de chaque pays) de façon continue, et non plus seulement lors des clôtures. 🟠[Proposition complémentaire]
Réduire drastiquement le temps de traitement des tâches comptables et administratives répétitives grâce à une intelligence artificielle intégrée aux processus. 🟢[Document]
Construire une plateforme extensible à l’ensemble des 17 pays membres de l’OHADA sans réécriture du socle applicatif, avec le Bénin comme priorité de déploiement. 🟢[Document]
4.2 Objectifs spécifiques
Fournir un moteur comptable SYSCOHADA complet : plan comptable, journaux, écritures, grand livre, balance, états financiers (Bilan, Compte de résultat, SIG, TAFIRE, flux de trésorerie), lettrage, rapprochement, clôture assistée. 🟢[Document]
Fournir un moteur fiscal paramétrable : pays, année, régime, impôt, taux, seuil, exonération, retenue, échéance — versionné, sans code en dur. 🟢[Document]
Intégrer une IA comptable et fiscale contrôlée : OCR, suggestion d’écritures, détection d’anomalies, copilote explicable, jamais autonome sur les actions sensibles. 🟢[Document]
Couvrir les modules de gestion connexes indispensables à une comptabilité fiable : trésorerie, stocks, immobilisations, achats/ventes, RH/paie, comptabilité analytique, budget. 🟢[Document]
Fournir des outils de pilotage transversaux : Score de Conformité GEL®, Passeport Entreprise GEL®, Tableau de Bord GEL®. 🟢[Document]
Garantir une sécurité et une gouvernance des données de niveau professionnel (isolation multi-tenant stricte, chiffrement, traçabilité fine, conformité APDP). 🟢[Document]
5. Périmètre
5.1 Dans le périmètre (in scope)
Comptabilité générale et analytique SYSCOHADA (classes 1 à 9). 🔵[Réglementaire]
Fiscalité béninoise complète (TVA, IS, ITS, TPS, Patente, VPS, CNSS) avec priorité 2026, et moteur fiscal extensible aux autres pays OHADA. 🟢[Document]
Secrétariat/administration (courriers, contrats, agenda, tâches, relances). 🟢[Document]
Gestion documentaire, signature électronique, coffre-fort numérique. 🟢[Document]
Trésorerie, stocks, immobilisations, achats/ventes, RH de premier niveau et paie. 🟢[Document]
Intelligence artificielle intégrée (documentaire, comptable, fiscale, contrôle, prédictive, copilote). 🟢[Document]
Modèles Entreprise avec personnel propre / Cabinet virtuel GEL / Professionnel indépendant / Marketplace / Mode Cabinet / Mode Institution. 🟢[Document]
Sécurité, audit trail, conformité réglementaire (APDP Bénin). 🟢[Document]
5.2 Hors périmètre applicatif direct (accompagnement humain)
Pôle 6 (communication digitale) et pôle 7 (formation professionnelle), qui relèvent de prestations humaines et non du logiciel lui-même. 🟢[Document]
Piliers 8 (Développer) et 10 (Pérenniser) de la Méthode GEL®, qui relèvent de l’accompagnement stratégique. 🟢[Document]
La mise en relation automatique de la Marketplace (moteur de recherche/matching), positionnée en Phase 2/3 de la feuille de route (Partie 39). 🟢[Document]
Le conseil juridique et fiscal personnalisé : GEL assiste et automatise, mais ne se substitue pas à un professionnel habilité pour les avis engageant la responsabilité de l’entreprise. 🟠[Proposition complémentaire]
6. Parties prenantes
7. Architecture globale et modèles d’usage
7.1 Principe d’architecture
🟢[Document] Chaque entreprise cliente dispose d’un espace sécurisé et isolé comprenant : tableau de bord, coffre-fort documentaire, agenda, tâches, comptabilité, déclarations, secrétariat, rapports, et Score de Conformité GEL®. Plusieurs acteurs collaborent autour de cet espace (dirigeant, secrétaire, comptable, administrateurs GEL, consultants), chacun avec un profil, des droits et une interface dédiés. Toutes les actions sont tracées.
L’isolation des données entre entreprises est absolue à tous les niveaux (base de données, API, interface) : un secrétaire ou un comptable ne voit que les entreprises qui lui sont explicitement rattachées, dans la limite des permissions accordées. 🟢[Document]
7.2 Les trois modèles d’usage (rattachement et facturation du personnel)
🟢[Document] Ces trois modèles coexistent sur la même infrastructure sans jamais mélanger données ni permissions :
Le rattachement Modèle 3 fonctionne dans les deux sens : le professionnel indépendant peut ajouter lui-même un client, ou une entreprise cliente peut l’inviter directement à gérer son dossier. Une passerelle de conversion « compte individuel → compte entreprise » est prévue, avec validation manuelle et migration des données. 🟢[Document]
7.3 Profils utilisateurs (rôle fonctionnel, orthogonal aux modèles d’usage)
🟢[Document] En complément des modèles d’usage (qui structurent la facturation et le rattachement), les profils suivants structurent les droits d’accès :
Règle transversale 🟢[Document] : chaque profil n’accède qu’aux ressources et actions que son rôle autorise (principe du moindre privilège — voir modèle Rôle/Permissions en Partie 26).
7.4 Schéma hiérarchique des comptes
🟢[Document]
SUPER ADMINISTRATEUR (1-2 comptes, propriétaire de la plateforme)
 │  crée et gère les plans tarifaires ; supervise toutes les entreprises (mode support journalisé)
 ▼
ADMINISTRATEUR D'ENTREPRISE (1 par entreprise cliente)
 │  souscrit et paie un plan ; active/désactive les portails ; invite son équipe (Modèle 1)
 │  ou reçoit une affectation du pool GEL (Modèle 2) ; définit les permissions fines de chaque membre
 ▼
SECRÉTAIRE / COMPTABLE affecté ou invité
    accès complet et isolé aux données des entreprises qui lui sont rattachées, dans la limite
    des permissions accordées ; reçoit et traite les demandes soumises par les clients finaux
7.5 Phases futures de la plateforme (aperçu — détail en Partie 39)
🟢[Document]
Phase 2 : ouverture aux professionnels indépendants (secrétaires, comptables, consultants, fiscalistes, experts RH), avec tableau de bord, agenda, GED, messagerie, suivi des tâches et espace client dédiés.
Phase 3/4 : Marketplace de mise en relation directe entre une entreprise et un professionnel référencé (profil, compétences, disponibilités, évaluations).
8. Profils utilisateurs et hiérarchie des rôles — détail des permissions
8.1 Principe du moindre privilège et permissions par ressource/action
🟢[Document] C’est l’Administrateur d’Entreprise, et lui seul, qui définit les droits d’accès de chaque secrétaire et de chaque comptable rattaché à son entreprise, selon le principe du moindre privilège : chaque membre n’a accès qu’à ce qui est strictement nécessaire à sa fonction, ajustable au cas par cas plutôt que figé sur 2-3 rôles standards.
Interface à cocher/décocher : pour chaque membre, l’Administrateur voit la liste des modules (Documents, Contacts, Agenda, Courriers, Comptabilité, Facturation, Banque, Rapports…) avec, pour chacun, des cases correspondant aux actions autorisées : Voir, Créer, Modifier, Supprimer, Exporter, Valider.
Application immédiate des modifications de droits, sans reconnexion nécessaire.
Modèles de permissions préconfigurés (« Secrétaire standard », « Comptable senior », « Lecture seule »), modifiables.
Toute modification de permission est journalisée : qui a changé quel droit, pour qui, quand.
Bonne pratique retenue : des permissions organisées par ressource et par action plutôt que par rôle figé unique — pour éviter l’écueil fréquent de l’accès « tout ou rien » — tout en évitant de multiplier les rôles à l’infini.
8.2 Rôles complémentaires recommandés
🟢[Document] En complément des rôles Secrétaire et Comptable déjà spécifiés, à valider avec l’équipe avant développement, à introduire seulement après consolidation du socle :
Assistant(e) Ressources Humaines — rôle dédié pour les entreprises à volume RH conséquent (dossiers du personnel, contrats de travail, paie, congés, recrutement).
Réviseur / Auditeur externe (lecture seule) — accès strictement en consultation, pour un commissaire aux comptes ou auditeur externe missionné ponctuellement.
Conseiller fiscal — rôle intermédiaire entre Comptable et Administrateur, accès aux déclarations et à la conformité fiscale sans nécessairement la saisie quotidienne — pertinent en phase 3/4 (indépendants, Marketplace).
🟠[Proposition complémentaire] Pour couvrir la dimension comptable et fiscale approfondie objet de ce cahier des charges, les rôles suivants complètent la liste :
Comptable senior / Chef de mission — valide les écritures et les états financiers produits par un comptable junior, autorise la clôture d’une période.
Fiscaliste — paramètre et supervise le moteur fiscal (taux, seuils, échéances) pour un ou plusieurs pays ; distinct du Conseiller fiscal côté entreprise, il agit au niveau plateforme ou cabinet.
Caissier — accès restreint au module Caisse (encaissements/décaissements de faible montant), sans accès à la comptabilité générale.
Auditeur GEL (plateforme) — rôle de supervision transverse pour l’Administrateur GEL, en lecture seule sur l’historique et les journaux de sécurité de toutes les entités, dans le cadre du mode support journalisé.
9. Modules fonctionnels — vue d’ensemble
🟢[Document] Vue consolidée de tous les modules identifiés dans les documents source, organisés par portail. Cette table sert de table des matières fonctionnelle ; chaque ligne renvoie à la partie qui la détaille.
9.1 Portail GEL-SECRÉTARIAT
9.2 Portail GEL-COMPTABILITÉ (anciennement « GEL Accountant »)
9.3 Modules transverses (nouveaux, issus de la fusion et de la mission comptable)
🟠[Proposition complémentaire] Ces modules n’étaient pas isolés en tant que tels dans les documents source, mais sont indispensables à la cohérence d’un système comptable et fiscal complet :
10. Comptabilité générale SYSCOHADA
10.1 Principe directeur
🟢[Document] Le comptable travaille directement depuis la plateforme, sans ressaisie manuelle des pièces déjà transmises par le secrétariat. La comptabilité doit rester conforme au référentiel SYSCOHADA/AUDCIF même lorsque l’IA intervient — l’IA propose, l’humain valide, la norme comptable n’est jamais contournée. 🟠[Proposition complémentaire — reformulation de la Règle 8 du prompt maître]
10.2 Le plan comptable SYSCOHADA — structure des 9 classes
🔵[Réglementaire — nomenclature officielle AUDCIF/SYSCOHADA révisé, en vigueur depuis le 1er janvier 2018, extraite de la référence demandée plan-comptable-ohada.com] Le plan comptable GEL doit être préchargé, complet et personnalisable, structuré autour des 9 classes suivantes. Cette nomenclature constitue le socle comptable commun à tous les pays de l’espace OHADA (voir séparation avec le moteur fiscal, Partie 11).
Précision sur le compte 64 (Impôts et taxes) — spécifiquement demandé par l’utilisateur, point de jonction central entre comptabilité SYSCOHADA (commune) et fiscalité (variable par pays) 🔵[Réglementaire]/🟠[Proposition complémentaire] : le compte 64 enregistre les impôts, taxes et versements assimilés à la charge de l’entité (hors impôt sur les bénéfices, logé en 89, et hors TVA récupérable/collectée, logée en 443/445). C’est le point d’entrée comptable du moteur fiscal (Partie 11) : chaque type d’impôt ou taxe paramétré dans le moteur fiscal d’un pays (Patente, VPS, taxes locales, droits d’enregistrement, etc.) doit être mappé à une subdivision du compte 64 (ou son équivalent nnational), afin que la génération automatique des écritures fiscales (Partie 13.3) reste conforme au plan comptable SYSCOHADA quel que soit le pays.
10.3 Fonctionnalités de comptabilité générale
🟢[Document] Fusion des deux cahiers des charges :
Plan comptable SYSCOHADA préchargé et personnalisable (ajout de sous-comptes par l’entité, dans le respect de la structure officielle).
Saisie d’écritures en partie double, avec vérification d’équilibre automatique (total débit = total crédit), sur le modèle déjà prototypé (écran « Entrée de journal (OD) »).
Journaux comptables normalisés SYSCOHADA : Achats, Ventes, Banque, Caisse, Opérations diverses (OD), À-nouveaux — chacun avec sa numérotation de pièce automatique.
Grand livre et Balance générale, calculés dynamiquement à partir des écritures validées.
États financiers exportables en PDF : Bilan (Actif/Passif), Compte de résultat, Soldes Intermédiaires de Gestion (SIG), TAFIRE (Tableau Financier des Ressources et des Emplois), Tableau des flux de trésorerie.
Workflow de validation des écritures : Saisie → Vérification → Validation → Clôture, avec séparation des tâches (principe de contrôle interne).
Contre-passation et extourne — 🟠[Proposition complémentaire] : toute écriture validée ne peut être supprimée ; elle ne peut être annulée que par une écriture de contre-passation (extourne) explicitement tracée dans l’Historique, jamais par suppression physique — condition indispensable de l’audit trail (Partie 26).
Lettrage des comptes de tiers (clients 41, fournisseurs 40) — 🟠[Proposition complémentaire] : rapprochement des factures et des règlements associés, manuel ou assisté par l’IA (Partie 13.2), avec suivi des écarts et des créances/dettes non lettrées au-delà d’un seuil d’ancienneté paramétrable.
Comptes d’attente (comptes 47 « Débiteurs et créditeurs divers ») — 🟠[Proposition complémentaire] : suivi obligatoire dans l’assistant de clôture (Partie 10.6), avec alerte si un compte d’attente reste non soldé au-delà d’une période paramétrable.
Comptes de tiers avec sous-comptes individualisés par client/fournisseur, soldes en temps réel, balance âgée.
10.4 Immobilisations (rattachement classe 2)
🟢[Document]/🟠[Proposition complémentaire pour les précisions de traitement]
10.5 Stocks (rattachement classe 3)
🟢[Document]
Entrées et sorties de stock, liées aux modules Achats/Ventes (Partie 21).
Valorisation au coût moyen pondéré (CMP), méthode de référence SYSCOHADA pour la majorité des entités (méthode paramétrable si l’entité justifie d’une autre méthode admise).
Inventaire physique et rapprochement avec les quantités théoriques, écarts tracés.
Dépréciation des stocks (compte 39) en cas de mévente ou d’obsolescence.
Activable selon le secteur du client (une entreprise de service pur n’a pas nécessairement besoin du module).
10.6 Trésorerie (rattachement classe 5)
🟢[Document]
Comptes bancaires, caisse, Mobile Money (MTN MoMo, Moov Money) — compte 55 « Instruments de monnaie électronique », spécificité du contexte ouest-africain déjà intégrée dans le plan SYSCOHADA révisé.
Import de relevés bancaires, rapprochement bancaire avec suggestion automatique de correspondance (assistée par IA — Partie 13.2) et validation manuelle obligatoire.
Règles bancaires automatiques (catégorisation récurrente), virements internes.
Trésorerie prévisionnelle : projection des encaissements/décaissements futurs, alerte de déficit anticipé (Partie 13.5, IA prédictive).
10.7 Comptabilité analytique (rattachement classe 9)
🟢[Document] Centres de coûts, projets, activités, produits, agences, départements, clients — analyse des marges et de la rentabilité. Détail en Partie 22.
10.8 Assistant de clôture comptable intelligent
🟠[Proposition complémentaire — développement de la demande explicite « créer un véritable assistant de clôture comptable intelligent »] Point d’orgue du module comptable, l’assistant de clôture guide le comptable à travers une checklist de contrôle automatisée avant validation de la période :
L’assistant produit un rapport de clôture listant les points bloquants (à régulariser avant clôture) et les points d’attention (informatifs), avec proposition d’écritures de régularisation soumises à validation humaine avant comptabilisation, conformément à la Règle 9 du prompt maître (l’IA est un assistant contrôlé, pas une autorité comptable autonome).
10.9 Moteur comptable intelligent — flux type
🟢[Document] Exemple de bout en bout, illustrant l’intégration IA/comptabilité (détail technique en Partie 13.2) :
Facture importée (PDF/photo)
 → OCR + lecture IA → identification du fournisseur, extraction des données
 → identification de la nature de l'opération
 → proposition du compte SYSCOHADA (classe 6 ou 2 selon le type)
 → identification de la TVA (taux, déductibilité)
 → détermination du traitement fiscal
 → proposition de l'écriture comptable complète (débit/crédit équilibrés)
 → rattachement automatique de la pièce justificative (GED)
 → détection d'anomalies (montant inhabituel, doublon, fournisseur inconnu)
 → demande de validation humaine
 → comptabilisation après validation uniquement
L’utilisateur conserve à tout moment la maîtrise : il peut modifier ou refuser la proposition de l’IA. Aucune écriture n’est comptabilisée sans validation humaine explicite.
11. Moteur fiscal multi-pays OHADA
11.1 Principe fondamental — OHADA ≠ fiscalité unique
🟢[Document — reprise directe du prompt maître, point structurant absent des deux cahiers des charges initiaux] Le référentiel comptable OHADA (AUDCIF/SYSCOHADA) constitue le socle commun, mais chaque pays conserve ses propres règles fiscales. GEL doit donc être architecturé avec :
un socle comptable OHADA commun (Partie 10, valable à l’identique dans les 17 États membres) ;
un moteur fiscal paramétrable par pays, permettant d’ajouter progressivement le Bénin (priorité), la Côte d’Ivoire, le Togo, le Sénégal, le Burkina Faso, le Mali, le Niger, la Guinée, le Cameroun, le Gabon, le Congo, la RDC, etc., sans devoir reconstruire le logiciel à chaque fois.
11.2 Objet fiscal paramétrable — modèle de données du moteur de règles fiscales
🟠[Proposition complémentaire — structuration technique de la demande du prompt maître] Chaque règle fiscale est modélisée comme un objet versionné et paramétrable, jamais codé en dur :
RegleFiscale
 - id
 - pays_code (ISO : BJ, CI, TG, SN, BF, ML, NE, GN, CM, GA, CG, CD…)
 - annee_fiscale / date_debut_validite / date_fin_validite
 - regime_fiscal (ex : Réel Normal, Réel Simplifié, TPS/Synthétique, Micro-entreprise)
 - type_impot (TVA, IS, ITS/IRPP selon pays, TPS, Patente, VPS, retenues à la source, CNSS…)
 - taux (valeur ou barème progressif — table TrancheBareme liée)
 - seuil (chiffre d'affaires, effectif, etc. — déclenche un régime ou une obligation)
 - base_taxable (règle de calcul de l'assiette)
 - exoneration (conditions d'exonération totale ou partielle)
 - retenue (règle de retenue à la source applicable, le cas échéant)
 - obligation_declarative (périodicité : mensuelle, trimestrielle, annuelle)
 - echeance (règle de calcul de la date limite)
 - formulaire (référence du formulaire déclaratif officiel)
 - penalite (règle de calcul des pénalités de retard)
 - regle_particuliere (texte libre / renvoi à un article de loi, pour les cas non paramétrables simplement)
 - compte_syscohada_associe (mapping vers la subdivision du compte 64, 44 ou 89 — Partie 10.2)
 - source_reglementaire (référence du texte : loi de finances, CGI, article)
 - version / date_creation_regle / auteur
Principe de versionnement : une modification fiscale (ex. loi de finances 2027) crée une nouvelle version de la règle avec une date de début de validité, sans écraser la version précédente — indispensable pour recalculer correctement des exercices clos ou en cours de vérification fiscale, et pour produire un historique auditable des règles appliquées à une déclaration donnée.
11.3 Séparation des trois couches de règles
🟢[Document]
Une modification fiscale (ex. loi de finances annuelle) ne doit jamais nécessiter de modifier le code du logiciel : elle se traduit uniquement par la création d’une nouvelle version d’objet RegleFiscale dans le moteur (Règle 7 du prompt maître : les taux fiscaux doivent être paramétrables et versionnés).
11.4 Fonctionnement du moteur au moment de l’usage
🟠[Proposition complémentaire]
Chaque entreprise cliente est rattachée à un pays_code (déterminé à la création du compte, modifiable en cas de changement de siège).
Chaque calcul fiscal (facturation, déclaration, paie) interroge le moteur avec (pays_code, date_operation, type_impot, regime_fiscal_entreprise) et reçoit la RegleFiscale en vigueur à cette date précise.
Le résultat du calcul est tracé avec la référence exacte de la règle appliquée (id + version), permettant au copilote IA (Partie 13.6) de justifier chaque calcul fiscal de façon explicable : « TVA calculée à 18 % en application du taux unique CGI Bénin 2026, règle FIS-BJ-TVA-2026-v1 ».
Lorsqu’une entreprise change de régime fiscal (ex. dépassement de seuil de chiffre d’affaires faisant basculer du régime TPS au régime réel), une alerte de veille est déclenchée (voir Partie 33).
11.5 Roadmap d’ajout des pays
🟠[Proposition complémentaire] L’architecture est conçue pour qu’ajouter un pays consiste à :
créer les entrées RegleFiscale du pays concerné (référentiel CGI local) ;
mapper les comptes fiscaux locaux (le cas échéant) vers la structure SYSCOHADA commune (Partie 10.2) ;
paramétrer les formulaires déclaratifs et échéances propres au pays ;
activer le pays dans la configuration globale du Super Administrateur (Partie 26).
Aucune de ces étapes ne touche au moteur comptable ni au moteur IA, qui restent strictement indépendants du pays.
12. Fiscalité béninoise — CGI 2026 et réformes
12.1 Avertissement méthodologique sur les sources (Règle 5 et Étape 6 du prompt maître)
🟠[Proposition complémentaire — nécessaire à la transparence] Les taux ci-dessous combinent : - 🟢[Document] les taux déjà indiqués dans le cahier des charges « GEL SABINET » (référencé « CGI 2026 ») ; - 🔵[Réglementaire, via recherche web complémentaire] des éléments de réforme 2026 identifiés dans la presse spécialisée et les communications de la Direction Générale du Budget/DGI (loi de finances gestion 2026 n°2025-22 du 8 décembre 2025, et loi de finances rectificative 2026).
Ces informations n’ont pas pu être vérifiées ligne par ligne sur le texte intégral officiel du CGI 2026 (le PDF de l’Acte uniforme comptable transmis ne contient pas le CGI béninois, qui est un texte distinct de droit national ; le site budgetbenin.bj héberge le PDF de la loi de finances mais son contenu détaillé n’a pas pu être extrait intégralement dans le cadre de cette session). Une vérification juridique et fiscale formelle auprès de la DGI ou d’un fiscaliste local reste indispensable avant mise en production, conformément à la note de prudence déjà présente dans le cahier des charges source sur la protection des données personnelles (Partie 26.4). Le moteur fiscal (Partie 11) est précisément conçu pour que cette vérification et ses éventuelles corrections n’impactent qu’un paramétrage, jamais le code.
12.2 Référentiel fiscal béninois de référence — table à paramétrer dans le moteur fiscal (pays_code = BJ)
🟢[Document]
12.3 Réformes 2026 identifiées (à intégrer au moteur de veille — Partie 33)
🔵[Réglementaire, recherche web complémentaire — non issu des documents source, à valider] Éléments de la loi de finances rectificative 2026 et de la modernisation déclarative DGI 2026, pertinents pour le paramétrage du moteur fiscal :
Télédéclaration et télépaiement obligatoires pour toutes les entreprises relevant du régime du réel normal d’imposition, quel que soit leur chiffre d’affaires — impact direct sur le module Déclaration Fiscale/Sociale déjà prototypé (nécessité d’une intégration API avec le système de télédéclaration DGI, à cadrer — voir Partie 28).
Retenue à la source sur les plateformes numériques (hébergement, ventes en ligne, transferts d’argent), avec obligation pesant sur les opérateurs de plateformes.
Imposition des bénéfices non réinvestis dans les trois années suivant leur réalisation, avec un taux réduit incitatif de 7,5 % pour les régularisations volontaires effectuées avant le 31 décembre 2026.
Taxation des plus-values sur cession de valeurs mobilières d’entreprises béninoises, quelle que soit la résidence du cédant.
Réduction des délais de vérification fiscale sur place : de trois à deux mois pour les entreprises dont le chiffre d’affaires annuel est inférieur à deux milliards de FCFA.
Recommandation opérationnelle 🟠[Proposition complémentaire] : chacun de ces points doit être transformé en entrée RegleFiscale (Partie 11.2) distincte, avec sa date d’entrée en vigueur exacte, dès validation du texte officiel intégral par un fiscaliste béninois désigné par le projet.
12.4 Conformité obligatoire (rappel du cahier des charges source)
🟢[Document]
Normes SYSCOHADA sur l’ensemble des modules comptables.
TVA 18 % et devise FCFA appliquées systématiquement.
Intégration e-MECeF/Sygmef opérationnelle pour toute facture émise (dispositif de facturation électronique certifiée en vigueur au Bénin).
Adaptation au contexte béninois : calendrier fiscal préchargé, paiement Mobile Money, mode dégradé en cas de connexion instable.
13. Intelligence artificielle métier GEL
13.1 Principe directeur
🟢[Document] L’IA constitue l’un des piliers stratégiques du projet. L’objectif n’est pas d’ajouter un chatbot à GEL : l’IA doit être intégrée nativement à chaque module métier, sur une architecture centralisée (service dédié appelant l’API Anthropic Claude), avec journalisation systématique dans l’Historique sous la mention « IA ACTION ».
Règles non négociables 🟢[Document] :
Aucune action irréversible (envoi, signature, paiement, comptabilisation définitive) exécutée automatiquement sans validation humaine explicite.
Isolation stricte : chaque requête IA n’a accès qu’aux données réelles de l’espace de l’utilisateur connecté (pas de fuite inter-entreprises).
Le Copilote ne doit jamais halluciner : si une donnée n’existe pas, il le dit clairement plutôt que d’inventer une réponse.
L’IA doit être un assistant contrôlé, jamais une autorité comptable autonome (Règle 9 du prompt maître).
13.2 IA documentaire et comptable
🟢[Document]/🟠[Proposition complémentaire pour le détail des contrôles]
13.3 IA fiscale
🟢[Document]
Identification du traitement fiscal applicable à une opération (TVA déductible/non déductible, charge déductible/non déductible fiscalement).
Contrôle des taux appliqués par rapport au moteur fiscal en vigueur (Partie 11).
Détection des opérations potentiellement non conformes (ex. charge susceptible d’être requalifiée en charge somptuaire non déductible).
Assistance à la préparation des déclarations (pré-remplissage à partir des données comptables réelles).
Calculs fiscaux (TVA due, acomptes IS, ITS sur salaires).
Alertes sur les échéances déclaratives, calées sur le calendrier fiscal du pays paramétré.
13.4 IA de contrôle (détection d’anomalies)
🟢[Document] Détection de :
doublons (factures, écritures, paiements) ;
montants inhabituels par rapport à l’historique de l’entité ;
écritures inhabituelles (compte rarement utilisé, contrepartie atypique) ;
incohérences TVA (taux appliqué non conforme au moteur fiscal) ;
erreurs de comptes (compte de charge crédité au lieu d’être débité, par exemple) ;
comptes d’attente non soldés ;
créances ou dettes anormalement anciennes ;
incohérences caisse (solde créditeur, ce qui est impossible pour une caisse physique) ou banque (écart de rapprochement récurrent).
13.5 IA prédictive
🟢[Document] Lorsque les données historiques sont suffisantes :
prévision de trésorerie (encaissements/décaissements à venir) ;
prévision de chiffre d’affaires ;
prévision de charges ;
prévision fiscale (estimation de l’IS ou de la TVA à venir) ;
détection des risques (ex. entreprise proche d’un seuil de changement de régime fiscal) ;
analyse de tendances (évolution de la marge, de la trésorerie).
13.6 Copilote comptable GEL
🟢[Document] Assistant conversationnel spécialisé, capable de répondre à des demandes en langage naturel telles que : « Analyse les comptes fournisseurs », « Pourquoi ma TVA est-elle élevée ? », « Trouve les écritures inhabituelles », « Vérifie les comptes 64 », « Prépare la clôture », « Identifie les charges potentiellement non déductibles », « Explique cette écriture », « Analyse la variation du résultat », « Prépare une synthèse pour le dirigeant ».
Exigence d’explicabilité 🟢[Document] : les réponses de l’IA doivent être explicables et traçables. L’IA ne doit pas simplement donner une réponse : elle doit pouvoir indiquer sur quelles données et quelles règles elle s’est appuyée — en particulier, pour toute réponse fiscale, elle doit citer la règle exacte du moteur fiscal utilisée (id + version, voir Partie 11.4).
13.7 Niveaux de maturité de l’assistant (repris du cahier « GEL SABINET », étendus à la dimension comptable)
🟢[Document]
13.8 Exigence de qualité — une IA alimentée par les données réelles (RAG)
🟢[Document] L’Assistant IA ne doit pas se limiter à des réponses génériques. Il doit être visiblement nourri par les données réelles et à jour de chaque entreprise :
Mécanisme technique : génération augmentée par récupération (RAG) — à chaque requête, l’IA reçoit en contexte les données réelles et récentes pertinentes de l’espace de l’utilisateur (documents, écritures, tâches, historique des échanges), sans mélanger les dossiers entre entreprises.
Boucle de rétroaction : lorsqu’un secrétaire ou un comptable corrige une proposition de l’IA (ex. reclasse un document, corrige une écriture), la correction est enregistrée et réutilisée pour affiner les futures suggestions sur cette même entreprise (mécanisme d’apprentissage par correction, pas un réentraînement du modèle lui-même).
Précision technique honnête : l’IA de GEL s’appuie sur un modèle de langage existant (API Anthropic Claude) auquel on fournit un contexte réel et actualisé — il ne s’agit pas d’entraîner un modèle propriétaire à partir de zéro. La qualité perçue vient de la richesse et de la fraîcheur du contexte fourni.
13.9 Facturation de l’Assistant IA (séparée de l’abonnement de base)
🟢[Document]
L’usage de l’IA est facturé à part, distinct de l’abonnement de base — une entreprise peut utiliser GEL sans jamais activer l’IA, sans surcoût.
Chaque plan tarifaire définit un quota IA inclus (requêtes/tokens par mois), affiché en temps réel.
Au-delà du quota : blocage avec proposition de mise à niveau, ou facturation à l’usage — arbitrage à trancher avec le Super Administrateur avant développement.
Le coût réel de l’IA (appels API Anthropic) doit être suivi précisément par entreprise, pour garantir une marge définie par le Super Administrateur entre coût réel et facturation client.
Historique de consommation IA consultable par l’Administrateur d’Entreprise.
14. Automatisation des processus et moteur de règles
14.1 Principe transversal — IA assistante avec validation humaine
🟢[Document]
[Déclencheur] → [IA propose] (catégorie, brouillon, écriture, résumé, relance)
 → [Journalisation de la proposition] → [Validation humaine obligatoire]
 → [Action appliquée] (jamais automatique pour les actions sensibles)
14.2 Constructeur de règles « Si / Alors »
🟢[Document] Niveau 4 de maturité IA (Partie 13.7) : configurable sans code par le secrétaire ou le comptable, permettant par exemple : - « Si une facture fournisseur de moins de 50 000 FCFA est reçue du fournisseur X, Alors proposer automatiquement le compte 6XX habituel » ; - « Si l’échéance de déclaration TVA approche à J-3, Alors notifier le comptable et le dirigeant via WhatsApp » ; - « Si un compte d’attente n’est pas soldé après 30 jours, Alors créer une tâche de relance ».
14.3 Workflows métiers automatisés (détail complet en Partie 30)
🟢[Document] Aperçu des chaînes déjà spécifiées, à orchestrer automatiquement sous supervision IA : - Workflow Courrier entrant/sortant (6 étapes). - Workflow Comptable (pièce reçue → enregistrement → traitement → intégration aux états financiers → déclaration → mise à jour du tableau de bord). - Workflow Conformité → Plan d’action. - Workflow Marketplace (inscription professionnel indépendant). - Workflow Onboarding client et affectation d’équipe.
15. Modèles économiques et facturation (rappel consolidé)
🟢[Document] Voir détail complet en Partie 7.2. Tableau récapitulatif fusionné des deux documents source :
Le plan souscrit par l’entreprise inclut nativement les accès de son propre personnel invité (Modèle 1) ; le nombre de membres inclus (ou le coût d’un membre additionnel) est défini par plan tarifaire (Partie 26.2). L’usage de l’IA est toujours facturé séparément (Partie 13.9). Paiement par Mobile Money (MTN MoMo, Moov Money). 🟢[Document]
16. Gestion documentaire (GED)
16.1 Principes de gouvernance documentaire
🟢[Document] Appliqués à l’ensemble du module Documents :
Unicité du document — pas de duplication silencieuse ; toute nouvelle version passe par l’historique de versions.
Classement normalisé — catégories standard par type d’entreprise/institution (Documents : Relevés bancaires, Factures, Déclarations fiscales, Courriers, Contrats, Bilans, Administratif, Courant/Annuel).
Indexation automatique — proposée par l’IA, validée par l’humain.
Historique des modifications — couvert par l’historique d’actions sur Document/Courrier.
Archivage sécurisé — statut archivé, lecture seule.
Durée de conservation définie — à paramétrer selon la nature du document (fiscal, social, contractuel, RH…) ; champ durée_conservation sur l’entité Document.
16.2 Rattachement d’un document à une opération comptable
🟠[Proposition complémentaire — condition indispensable à l’audit trail comptable] Une écriture comptable doit pouvoir être reliée à : facture, reçu, contrat, bon de commande, bon de livraison, relevé bancaire, bulletin de paie, déclaration fiscale, pièce diverse — relation Document ↔ PieceComptable ↔ EcritureComptable (modèle de données détaillé en Partie 29).
16.3 Fonctionnalités complémentaires
🟢[Document] Signature électronique, suivi de signature, alertes d’échéance contractuelle ; bibliothèque de modèles de documents réutilisables ; recherche, versioning, accès sécurisé, traçabilité.
17. Trésorerie
Voir détail comptable en Partie 10.6. Complément fonctionnel côté pilotage 🟢[Document] :
Vue dirigeant : chiffre d’affaires, charges, trésorerie, impayés — alimentée en temps réel par les données saisies par le comptable (écran « Tableau de bord dirigeant »).
Échéanciers, prévisions de trésorerie, alerte de déficit anticipé (IA prédictive, Partie 13.5).
18. Stocks
Voir détail comptable en Partie 10.5. Le module Stocks reste activable selon le secteur du client — une entreprise de service pur peut fonctionner sans lui, conformément au principe d’éviter la création artificielle de modules non pertinents (Règle du prompt maître, Section 12).
19. Immobilisations
Voir détail comptable en Partie 10.4.
20. Ressources humaines et paie
20.1 RH de premier niveau (module Secrétariat)
🟢[Document] Suivi congés/absences, documents RH (attestations, certificats), coordination de recrutement, checklist d’intégration — porté par le Secrétariat pour les besoins courants, avec option d’évolution vers un rôle Assistant RH dédié (Partie 8.2) si le volume le justifie.
20.2 Paie & Charges sociales (module Comptabilité)
🟢[Document]
Bulletins de paie conformes : calcul de l’ITS (barème progressif — Partie 12.2) et des cotisations CNSS.
Déclaration sociale, calendrier des échéances CNSS (distinct du calendrier fiscal DGI).
Génération automatique des écritures de paie (charges de personnel classe 66, dettes envers le personnel 42, organismes sociaux 43, État 44 pour l’ITS retenu à la source).
🟠[Proposition complémentaire] Le moteur fiscal (Partie 11) doit porter le barème ITS comme une RegleFiscale de type barème progressif, versionnée par année, afin qu’un changement de barème dans une future loi de finances ne nécessite qu’une mise à jour de paramétrage.
21. Gestion commerciale (achats, ventes, tiers)
🟢[Document — confirmé par le prototype GEL Accountant]
21.1 Ventes / Facturation
Factures avec calcul TVA 18 %, numérotation séquentielle conforme, intégration e-MECeF/Sygmef obligatoire.
Devis convertibles en facture en un clic.
Notes de crédit (avoir), remboursements.
Clients partagés avec le module Contacts.
Produits & services (catalogue réutilisable).
Paiements reçus (encaissement partiel possible, écriture comptable générée automatiquement — lettrage, Partie 10.3).
Factures récurrentes (abonnements) avec génération automatique à échéance programmée.
Relances de factures impayées, niveaux progressifs.
21.2 Achats / Dépenses
Saisie de dépenses (catégorisées, TVA, justificatif joint) — écran déjà prototypé.
Factures fournisseurs (« Bill ») avec échéance de paiement.
Bons de commande avec suivi de réception.
Fournisseurs (base dédiée, historique de transactions, crédit fournisseur).
Notes de frais avec validation et suivi de remboursement.
21.3 Contacts / Tiers partagés
Base de contacts unique, partagée entre modules Secrétariat et Comptabilité.
Historique complet par contact (documents, courriers, factures, paiements) — « Fiche de contexte à 360° » (IA niveau 3, Partie 13.7).
22. Comptabilité analytique
🟢[Document] Rattachée à la classe 9 du plan SYSCOHADA (classe libre, Partie 10.2) :
Centres de coûts, projets, activités, produits, agences, départements, clients.
Analyse des marges et de la rentabilité, ventilation des charges/produits par axe analytique en parallèle de la comptabilité générale (sans double saisie — chaque écriture générale peut être ventilée sur un ou plusieurs axes analytiques).
23. Budgets et prévisionnel
🟠[Proposition complémentaire — module cité dans le prompt maître, absent en détail des deux cahiers source]
Élaboration budgétaire par exercice, par compte ou par centre analytique.
Suivi budgétaire : comparaison réalisé/budget en temps réel, alertes de dépassement.
Révisions budgétaires (budget rectificatif), historisées.
Intégration avec l’IA prédictive (Partie 13.5) pour proposer des projections basées sur l’historique.
24. Reporting et tableaux de bord
🟢[Document] Plusieurs niveaux de reporting :
Rapports standards (compte de résultat, bilan, balance âgée clients/fournisseurs, TVA), centre de performance (marge, trésorerie, évolution du CA), rapports personnalisés et rapports sauvegardés, tous exportables en PDF. 🟢[Document]
25. Audit, conformité et outils propriétaires GEL®
25.1 Score de Conformité GEL®
🟢[Document] Calculé automatiquement à partir de l’état des documents obligatoires, du respect des échéances, et de la clôture des plans d’action. Cycle : Diagnostic → Évaluation (calcul du Score) → Plan d’action → Mise en œuvre → Suivi → Amélioration.
[Calcul automatique du Score de Conformité] → [Détection d'écarts] → [Ligne "À régulariser"]
 → [Plan d'action] (responsable, échéance, document attendu)
 → [Suivi/Relances J-7, J-2, J] → [Document reçu/validé] → [Mise à jour Score, clôture]
Écran type (déjà maquetté) : liste des obligations (RCCM, IFU, CNSS, document salarié, assurance, autorisation sectorielle…) avec statut ✅/⚠️/❌ et bouton « Créer un plan d’action ».
25.2 Passeport Entreprise GEL®
🟢[Document] Document de synthèse généré automatiquement (identité administrative + Score de Conformité + indicateurs financiers clés issus des états financiers), consultable et exportable par le dirigeant — une « carte d’identité stratégique » de l’entreprise à un instant T.
25.3 Tableau de Bord GEL®
🟢[Document] Vue agrégée pour le dirigeant : dossiers, documents, agenda, indicateurs, Score GEL®, notifications.
25.4 Audit trail (traçabilité fine)
🟢[Document] Chaque Administrateur d’Entreprise doit pouvoir consulter, pour chaque secrétaire et chaque comptable rattaché, un historique détaillé de toutes les manipulations :
Connexions et déconnexions (date, heure, appareil).
Consultation d’un document ou d’un dossier (qui a ouvert quoi, quand).
Création, modification et suppression d’une donnée (écriture comptable, document, contact, tâche).
Export ou téléchargement d’un document ou d’un rapport.
Impression d’un document depuis la plateforme.
Copier/coller réalisé dans l’interface web (événements navigateur copy/paste) — limite technique honnête : impossible de tracer une capture d’écran ou une photo d’écran, aucune plateforme ne peut le garantir.
Changement de permission effectué par l’Administrateur.
L’intégralité de l’Historique doit être rédigée et consultable en français, sans terme technique anglicisé non traduit.
🟠[Proposition complémentaire — extension comptable de l’audit trail] Pour toute opération comptable ou fiscale sensible, l’audit trail conserve systématiquement : utilisateur, date, heure, opération, ancienne valeur, nouvelle valeur, justification, statut de validation — conformément à la Partie 14 (« Audit trail ») du prompt maître. Aucune modification importante (écriture validée, taux fiscal appliqué, montant de déclaration) ne peut être effectuée sans laisser de trace.
26. Sécurité et gouvernance
26.1 Règles absolues, valables sur toute la plateforme
🟢[Document]
Aucune donnée fictive, de démonstration ou d’exemple à quelque endroit que ce soit — état vide clair si aucune donnée réelle n’existe.
Isolation stricte des données entre entreprises, à tous les niveaux (base de données, API, interface).
Aucune action irréversible automatisée sans confirmation humaine explicite (envoi, signature, paiement, suppression, comptabilisation définitive).
Toute suppression de compte ou d’entreprise passe par une période de rétention avant suppression définitive.
Chaque action sensible journalisée dans l’Historique correspondant, avec la mention « IA ACTION » pour les actions générées par l’assistant.
26.2 Sécurité des comptes et des données
🟢[Document]
Authentification à deux facteurs (2FA) disponible pour tous les rôles, obligatoire pour les comptes Administrateur d’Entreprise et Super Administrateur.
Gestion des sessions actives et déconnexion à distance ; journal de connexion et journal de sécurité consolidé.
Politique de mot de passe robuste, verrouillage temporaire après tentatives échouées.
Chiffrement des données en transit : HTTPS/TLS, sans exception, sur tous les portails.
Chiffrement des données sensibles au repos (documents légaux, informations bancaires, pièces d’identité) — au niveau du champ ou du fichier pour les données les plus sensibles, pas seulement au niveau du serveur.
Isolation stricte multi-tenant à trois niveaux : base de données (filtrage systématique par entreprise_id sur chaque requête), API (vérification d’autorisation à chaque appel, jamais une confiance aveugle au frontend), interface (aucune donnée d’une autre entreprise ne doit jamais transiter, même masquée, vers un utilisateur non autorisé).
Sauvegardes régulières et automatiques, avec tests de restauration périodiques.
Principe du moindre privilège appliqué à tous les niveaux, humain ou technique.
Journalisation de sécurité : toute tentative d’accès anormale détectée et alertée, pas seulement enregistrée passivement.
Plan de réponse à incident documenté avant mise en production.
26.3 Compte Super Administrateur
🟢[Document] Accès strictement limité (1 à 2 comptes maximum), URL non devinable, 2FA obligatoire sans exception.
Vue d’ensemble de la plateforme : MRR et évolution, nombre d’entreprises par statut, taux de conversion essai → payant, taux de désabonnement, statut technique en temps réel (base de données, mailing, API IA, Mobile Money, espace disque, dernière sauvegarde), flux d’activité récente.
Gestion des entreprises clientes : liste globale filtrable, suspension/réactivation avec motif obligatoire, suppression avec période de rétention.
Mode Support (impersonation contrôlée) : bannière visible en permanence, journalisation stricte, session limitée dans le temps, notification à l’entreprise concernée.
Plans tarifaires et finance : création/modification/désactivation des plans (portails inclus, quota IA, membres autorisés, durée d’essai), vue financière globale, suivi Mobile Money, gestion des litiges/remboursements.
Configuration globale : feature flags, quotas IA globaux, calendrier fiscal partagé et activation des pays OHADA (Partie 11.5), bibliothèque de modèles de documents partagés.
Pool de personnel GEL (Modèle 2) : registre, capacité, affectation manuelle jamais automatique, réaffectation avec transfert d’historique, détection d’un dossier négligé.
26.4 Protection des données personnelles (APDP Bénin)
🟢[Document] Le Bénin dispose d’un cadre légal de protection des données à caractère personnel, sous l’autorité de l’Autorité de Protection des Données Personnelles (APDP). GEL, en tant que plateforme traitant des données personnelles et professionnelles sensibles pour le compte de tiers, doit s’y conformer :
Recueil du consentement explicite pour toute donnée personnelle collectée, avec finalité clairement indiquée.
Droit d’accès, de rectification et de suppression des données personnelles.
Durée de conservation limitée et justifiée.
Documentation des traitements de données personnelles, disponible en cas de contrôle.
🔵[Réglementaire — note de prudence conservée du document source] Une vérification juridique précise du cadre réglementaire béninois en vigueur au moment du développement reste recommandée, idéalement avec un conseil juridique local, avant la mise en production réelle. Cette même prudence s’applique à l’ensemble des taux fiscaux de la Partie 12.
26.5 Profils de sécurité (rappel, complète Partie 8)
🟢[Document]/🟠[Proposition complémentaire] Administrateur · Dirigeant · Comptable · Assistant comptable · Fiscaliste · Auditeur · RH · Caissier · Utilisateur métier — avec séparation des fonctions (principe de contrôle interne comptable : la personne qui saisit une écriture ne devrait pas être la seule à la valider pour les montants significatifs).
27. Architecture technique
27.1 Stack retenue
🟢[Document]
Note d’arbitrage 🟠[Proposition complémentaire] : le cahier « GEL SABINET » propose une architecture temps réel Laravel Broadcasting/Reverb (écosystème PHP), tandis que le cahier « GEL » global et la stack recommandée en Partie 27.1 reposent sur TypeScript/Node.js de bout en bout. Retenir Socket.io sur NestJS est cohérent avec le choix « un seul langage pour toute la stack » (Partie 27.2) et évite d’introduire un second écosystème technique (PHP) uniquement pour les notifications. Ce point doit être validé explicitement par l’équipe technique avant le lancement du développement.
27.2 Justification du choix
🟢[Document]
Un seul langage (TypeScript) pour l’ensemble de la stack — backend, frontend web, application mobile — réduisant la complexité de maintenance et facilitant la reprise du projet par un futur développeur.
Vivier de développeurs TypeScript large, y compris en Afrique francophone, garantissant la pérennité du recrutement.
Écosystème mature pour les besoins de la plateforme : authentification, temps réel, génération de documents, intégrations de paiement.
PostgreSQL : robustesse et richesse fonctionnelle pour un système de gestion multi-tenant appelé à grandir.
27.3 Principes d’architecture transverses
🟢[Document]
Architecture multi-tenant stricte avec isolation des données par entreprise à tous les niveaux.
API-first : le backend expose une API cohérente consommée par le frontend web et la future application mobile.
Mode dégradé : file d’attente locale pour les actions lancées en connexion instable, traitées automatiquement au retour du réseau — contrainte réaliste du contexte ouest-africain.
Sous-domaine dédié pour le portail Super Admin, avec guard d’authentification séparé.
Documentation d’architecture exigée du développeur : choix techniques, schéma de base de données, guide d’installation, commentaires en français dans le code.
27.4 Exigences non fonctionnelles
🟠[Proposition complémentaire — exigences implicites du prompt maître (« performance, scalabilité, disponibilité ») non détaillées dans les documents source]
28. APIs et intégrations
🟢[Document]/🟠[Proposition complémentaire pour la structuration]
29. Modèle de données conceptuel
29.1 Entités du périmètre « Secrétariat » (déjà spécifiées, reprises intégralement)
🟢[Document]
Entreprise ──┬── Utilisateur ── Rôle
 ├── Document
 ├── Courrier
 ├── Contact
 ├── Réunion
 ├── Contrat
 ├── Tâche
 ├── Échéance
 ├── Relance
 ├── Rapport
 ├── Conformité (ScoreConformité, PlanAction)
 └── (mode Institution) Cabinet → Service → Agent
Entités : Entreprise, Utilisateur, Rôle, Affectation, Document, Courrier, Tâche, Échéance, Contact, Réunion, Contrat, Validation, Signature, Relance, Rapport, Conformité.
29.2 Entités du périmètre comptable et fiscal (fusion des deux documents et compléments)
🟢[Document]/🟠[Proposition complémentaire, marquée ci-dessous]
PlanComptable (🟠) — id, pays_code, classe, compte, sous_compte, libelle, nature (bilan/gestion)
 — référentiel SYSCOHADA préchargé (Partie 10.2), personnalisable par entité

CompteComptable (🟠) — id, entreprise_id, plan_compte_id, libelle_personnalise, solde_courant

Journal (🟠) — id, entreprise_id, code (AC, VE, BQ, CA, OD, AN), libelle

EcritureComptable (🟠) — id, entreprise_id, journal_id, date, numero_piece, statut
 (brouillon/validée/clôturée), lignes[] (compte_id, libelle, debit, credit),
 valide_par, date_validation
 — lien optionnel vers PieceComptable et vers Document (pièce justificative source)

PieceComptable (🟢) — id, entreprise_id, type (facture, dépense, justificatif, pièce bancaire),
 date, montant, devise, statut (reçue, traitée, validée)
 — lien optionnel vers Document (fichier source) et vers Courrier (si reçue par courrier)
 — traité_par (comptable_id)

Declaration (🟢) — id, entreprise_id, type (fiscale, sociale/CNSS), pays_code, periode,
 date_echeance, statut (préparée, déposée, validée), montant
 — lien vers Échéance (déclenche une échéance automatique)
 — lien vers RegleFiscale appliquée (Partie 11.2, traçabilité du calcul)

RegleFiscale (🟠) — voir modèle complet Partie 11.2

Tresorerie (🟢, vue calculée) — entreprise_id, période, chiffre_affaires, charges, solde, impayés

EtatFinancier (🟢) — id, entreprise_id, type (bilan, compte de résultat, SIG, TAFIRE…),
 période, document_généré_id, statut

Immobilisation (🟠) — id, entreprise_id, compte_classe2_id, date_acquisition,
 date_mise_service, cout_entree, methode_amortissement, duree, valeur_nette_comptable

PlanAmortissement (🟠) — id, immobilisation_id, exercice, dotation, cumul_amortissement

MouvementStock (🟠) — id, entreprise_id, article_id, type (entrée/sortie), quantite,
 cout_unitaire, methode_valorisation (CMP)

AxeAnalytique (🟠) — id, entreprise_id, type (centre_cout, projet, agence, client…), libelle

VentilationAnalytique (🟠) — id, ecriture_ligne_id, axe_analytique_id, montant, pourcentage

Budget (🟠) — id, entreprise_id, exercice, compte_id ou axe_analytique_id, montant_prevu

ProfilProfessionnel (Marketplace, 🟢) — id, utilisateur_id, type, domaine_expertise,
 disponibilité, documents_professionnels, statut_vérification
 — relations N-N vers Entreprise via Affectation

Abonnement / Pack (🟢) — id, entreprise_id, type de pack, services_inclus, date_début,
 date_fin, statut, facturation_liée

PasseportEntreprise (🟢) — id, entreprise_id, date_génération, contenu (synthèse identité +
 conformité + performance)

ScoreConformité (🟢) — alimenté par : documents à jour, échéances respectées,
 plan d'action soldés

IAAction (🟠) — id, entreprise_id, utilisateur_id, module, type_proposition, donnees_entree,
 proposition, statut (proposée/validée/rejetée/modifiée), valide_par, date
 — journal exhaustif de toute intervention IA, socle de l'explicabilité (Partie 13.6)
29.3 Règle d’isolation des données (rappel, inchangée et étendue)
🟢[Document] Toute requête applicative reste scopée par entreprise_id, croisée avec Affectation — cette règle s’applique à toutes les entités ci-dessus, y compris les nouvelles entités comptables, fiscales et la Marketplace : un professionnel indépendant ou un comptable du pool GEL ne voit que les entreprises pour lesquelles il a une Affectation active.
30. Workflows globaux
30.1 Traitement standard d’une demande (workflow générique transversal)
🟢[Document]
Entreprise (dépose une demande/document)
 → Notification → Secrétaire → Traitement → Comptable (si nécessaire) → Validation
 → Rapport → Archivage → Notification client
Ce workflow générique se spécialise en workflow Courrier, workflow Comptable ou workflow Conformité selon la nature de la demande. Toutes les étapes sont historisées.
30.2 Gestion des demandes clients (espace interne, sans compte autonome)
🟢[Document] Point de cadrage important : GEL n’a pas pour objectif de créer un portail public où les clients finaux de chaque entreprise créent un compte et interagissent de façon autonome. Le mécanisme retenu est un simple point d’entrée :
Formulaire léger (nom, contact, objet, pièce jointe), sans création de compte ni mot de passe.
Types de demande personnalisables par l’entreprise (« Demander un devis », « Signaler un problème », « Question sur une facture »).
Confirmation de réception par email, sans accès à un espace de suivi personnel.
La demande arrive directement dans le compte GEL de l’entreprise concernée (module Tâches ou file « Demandes clients »), traitée par le personnel affecté comme un courrier ou une tâche interne.
Aucune donnée du client final n’est stockée dans un compte lui appartenant en propre.
30.3 Workflow Courrier entrant (détaillé)
🟢[Document]
[Réception] → [Enregistrement] (numéro auto, date, expéditeur, objet, catégorie, priorité)
 → [Affectation] → [Traitement] ── besoin comptable ? ──► [Transmission comptable]
      │ "pièce manquante"
      ▼
 [Recherche/complément secrétaire]
 → [Action réalisée] → [Clôture / Archivage]
30.4 Workflow Comptable
🟢[Document]
[Pièce comptable reçue] (via Courrier, dépôt direct, ou transmission secrétaire)
 → [Enregistrement PieceComptable] — type, montant, date
 → [Traitement comptable] ── pièce manquante ? ──► [Demande à la secrétaire] (workflow 30.3)
      │ non
      ▼
 [Intégration aux états financiers / trésorerie]
 → [Préparation Déclaration si échéance liée] → suivi via Échéance (rappels, Relance)
 → [Mise à jour du Tableau de Bord GEL® dirigeant]
30.5 Workflow Conformité → Plan d’action
Voir Partie 25.1.
30.6 Workflow Marketplace — parcours professionnel indépendant
🟢[Document]
[Création de compte ProfilProfessionnel] → [Vérification/validation du profil par Admin GEL]
 → [Configuration profil] (expertise, disponibilité) → [Ajout de clients] (Affectation)
 → [Utilisation des modules standard] (documents, agenda, messagerie, rapports)
La mise en relation automatique (recherche par une entreprise d’un professionnel disponible) est positionnée en Phase 2/3 de la feuille de route (Partie 39) et nécessite un workflow de recherche/filtrage supplémentaire à spécifier à ce moment-là.
30.7 Onboarding client et affectation d’équipe
🟢[Document]
[Création de compte Entreprise]
 → [Génération automatique] — espace sécurisé, coffre-fort, agenda, tableau de bord, messagerie
 → [Choix du pack / Abonnement] — services souhaités (secrétariat, comptabilité, conseil…)
 → [Affectation automatique de l'équipe] — secrétaire, comptable, conseiller selon le pack
 → [Le client est informé] — noms et coordonnées des professionnels affectés
31. Fiches fonctionnelles détaillées (échantillon représentatif)
🟠[Proposition complémentaire — format imposé par le prompt maître, Section 19] Format appliqué aux fonctionnalités les plus structurantes du module comptable/fiscal/IA. L’ensemble des fonctionnalités listées dans ce document (~150 au total) devra faire l’objet d’une fiche équivalente lors du cadrage détaillé de chaque sprint ; cet échantillon illustre le niveau de détail attendu.
COM-001 — Saisie et validation d’une écriture comptable
COM-002 — Clôture assistée d’une période comptable
FIS-001 — Calcul et déclaration de la TVA
IA-001 — Lecture automatique d’une facture (OCR + proposition d’écriture)
IMM-001 — Génération automatique du plan d’amortissement
TRS-001 — Rapprochement bancaire assisté
32. Règles métier, comptables et fiscales — synthèse
🟠[Proposition complémentaire — synthèse consolidée exigée par le prompt maître, Section 21]
33. Gestion des versions réglementaires (Regulatory Intelligence)
🟢[Document] Fonctionnalité de veille réglementaire permettant d’identifier les changements concernant : OHADA (évolutions AUDCIF), lois de finances, CGI, obligations déclaratives, taux, seuils, échéances.
Lorsqu’une réforme intervient, le système doit pouvoir indiquer : - quelles règles changent ; - à partir de quelle date ; - quelles entreprises sont concernées (par pays, par régime) ; - quelles fonctionnalités sont affectées ; - quelles déclarations sont concernées ; - quelles données doivent être adaptées.
🟠[Proposition complémentaire — mécanisme technique] Concrètement, la veille réglementaire alimente le moteur fiscal (Partie 11) par la création de nouvelles versions de RegleFiscale, avec une phase de validation humaine par un fiscaliste avant que la nouvelle règle ne devienne active à sa date d’entrée en vigueur. Un rapport d’impact est généré automatiquement listant les entreprises concernées par pays/régime, à des fins de communication proactive (exemple d’application directe : les réformes 2026 identifiées en Partie 12.3).
34. Gouvernance de l’IA
🟢[Document]/🟠[Proposition complémentaire pour la structuration en section dédiée]
Contrôle humain systématique sur toute action comptable, fiscale ou irréversible (Partie 13.1, 26.1).
Explicabilité obligatoire : toute réponse ou proposition de l’IA doit pouvoir être justifiée par les données et règles sur lesquelles elle s’est appuyée (Partie 13.6).
Isolation stricte des données entre entreprises dans le contexte fourni à l’IA (RAG, Partie 13.8).
Interdiction d’hallucination assumée : absence de donnée signalée explicitement plutôt qu’inventée.
Journalisation exhaustive de toute action IA sous la mention « IA ACTION » (entité IAAction, Partie 29.2).
Boucle d’amélioration continue par correction humaine, jamais par réentraînement du modèle sous-jacent (Partie 13.8).
Facturation séparée et transparente de l’usage IA, avec suivi de coût réel vs facturation (Partie 13.9).
🟠 Comité de supervision IA (proposition complémentaire) : revue périodique (trimestrielle) par l’équipe produit et un comptable référent des cas où l’IA a été systématiquement corrigée par les utilisateurs, afin d’identifier les axes d’amélioration du contexte fourni (RAG) ou du prompt système du moteur comptable/fiscal.
35. Mode Cabinet / Mode Institution
35.1 Mode Cabinet (multi-entreprises pour cabinets comptables)
🟠[Proposition complémentaire — section demandée explicitement par le prompt maître (« Mode Cabinet »), présente en germe dans les deux documents source via le Modèle 3/Marketplace mais jamais formalisée comme mode dédié] Pour les cabinets comptables gérant un portefeuille de clients (au-delà du cas de l’indépendant seul), GEL propose :
Portefeuille clients : vue consolidée de toutes les entreprises gérées par le cabinet, avec indicateurs de risque et d’échéance par client.
Collaborateurs : gestion d’une équipe de comptables/assistants au sein du cabinet, avec affectation par client (réutilise le modèle Affectation).
Tâches et échéances consolidées tous clients confondus, avec vue calendaire globale du cabinet.
Suivi des clôtures : état d’avancement de la clôture de chaque client, en un coup d’œil.
Contrôles et alertes transverses (ex. tous les clients dont la TVA n’est pas encore préparée à J-5).
Reporting cabinet : chiffre d’affaires du cabinet, rentabilité par mission, suivi du temps passé.
Facturation des missions : suivi des honoraires par client/mission, distincte de la facturation des clients eux-mêmes dans leur propre comptabilité.
Suivi des missions : cahier des charges de mission, périmètre, échéances contractuelles.
Ce mode s’appuie exactement sur le même modèle de données que le Modèle 3/Marketplace (ProfilProfessionnel, Affectation) — il n’introduit pas de nouvelle entité, seulement une vue consolidée multi-clients supplémentaire pour les comptables gérant plusieurs entreprises.
35.2 Mode Institution (structure hiérarchique)
🟢[Document]
Institution → Cabinet/Direction → Service → Responsable → Secrétaire → Agents
Chaque niveau hérite des mêmes objets (Document, Courrier, Tâche…) et ajoute : - NiveauConfidentialité (public, interne, restreint, confidentiel) ; - Délégation (délégant, délégataire, périmètre, durée) ; - VisaHiérarchique (extension de Validation avec ordre Service → Responsable → Cabinet → Institution).
Spécificités : parapheur numérique, registre des courriers renforcé (numérotation officielle), protection des données personnelles conforme au Code du numérique béninois et aux exigences APDP.
35.3 Cas d’application : GEC — Gestion Électronique des Courriers pour l’UAC
🟢[Document] Cas d’usage institutionnel déjà cadré, conservé intégralement comme référence de configuration du Mode Institution : Institution = UAC, Cabinet/Direction = Rectorat, Service = Vice-Rectorats/Décanats, Responsable = Doyens/Directeurs, Agent = personnel administratif. Compléments identifiés : numéro d’arrivée automatique séquentiel par entité/année (format [Entité]-[Année]-[Séquence], ex. UAC-2026-000452), verrouillage en lecture seule dès statut validé/archivé, MFA recommandée pour Recteur/Doyens, prérequis de déploiement (scanner à chaque secrétariat, connexion internet stable, prototype pilote sur un seul Vice-Rectorat avant généralisation).
36. Critères d’acceptation (échantillon transverse)
🟠[Proposition complémentaire]
Aucune écriture comptable ne peut être déséquilibrée (débit ≠ crédit) en base de données.
Aucune donnée d’une entreprise A n’est jamais visible, même partiellement, par un utilisateur non affecté à l’entreprise A (test d’intrusion croisée obligatoire avant toute mise en production).
Aucune action IA sensible (écriture comptable, déclaration, paiement) n’est appliquée sans validation humaine tracée.
Un changement de taux fiscal (nouvelle version de RegleFiscale) n’affecte jamais le calcul d’une période antérieure déjà clôturée.
Le Score de Conformité GEL® se met à jour automatiquement dans l’heure suivant un changement d’état d’un document ou d’une échéance.
Toute suppression demandée (compte, entreprise) passe par une période de rétention avant suppression physique définitive.
Le mode dégradé permet la saisie hors connexion et la synchronisation automatique au retour du réseau, sans perte de données.
L’ensemble de l’interface et de l’historique est rédigé en français, sans jargon technique non traduit, pour rester lisible par un dirigeant non-comptable.
37. Priorisation (P0–P3)
🟢[Document — méthode imposée par le prompt maître]
38. MVP et versions
🟠[Proposition complémentaire — synthèse fusionnant les deux feuilles de route source, cohérente avec Partie 37]
39. Roadmap de développement
🟢[Document] Fusion des deux feuilles de route (« GEL SABINET » en 4 phases et « GEL » global en 4 phases), harmonisée en un plan unique cohérent avec la priorisation (Partie 37) :
Phase 1 — Socle fonctionnel (MVP)
Gestion des utilisateurs, rôles, permissions (Partie 8) ; authentification et sécurité de base (Partie 26).
Portails Secrétariat et Comptabilité avec les modules déjà listés en existants (Partie 9).
Comptes Administrateur d’Entreprise et Super Administrateur.
Flux d’invitation Modèle 1 (entreprise avec personnel propre).
Comptabilité de base : plan SYSCOHADA Bénin préchargé, écritures, journaux, grand livre, balance, états financiers de base.
Moteur fiscal Bénin (paramétré, mais architecture déjà multi-pays — Partie 11).
Gestion documentaire de base (sans indexation IA à ce stade).
Système de notifications temps réel et PWA installable.
Phase 2 — Service géré, conformité et déclenchement de l’IA
Modèle 2 : pool de personnel GEL, affectation, coordination secrétaire/comptable.
Module de gestion des demandes clients (sans compte autonome).
Assistant IA niveaux 1 à 3 (réactif, proactif, orchestration inter-modules), y compris IA documentaire et suggestion d’écritures.
Score de Conformité GEL®, Passeport Entreprise GEL®.
Assistant de clôture comptable intelligent (checklist complète).
Immobilisations, Paie & Charges sociales de base.
Mode Institution (si un client institutionnel comme l’UAC est priorisé à ce stade).
Phase 3 — Extension des usages et indépendants
Modèle 3 : inscription autonome des professionnels indépendants, essai gratuit puis abonnement.
Marketplace des professionnels (inscription, sans mise en relation automatique).
Mode Cabinet (portefeuille multi-clients).
Comptabilité analytique, Budgets, Stocks avancés, Trésorerie prévisionnelle.
Application mobile (fonctions clés : tableau de bord, notifications, validation/signature).
Signature électronique, notifications intelligentes avancées.
Assistant IA niveaux 4 et 5 (automatisations personnalisables, WhatsApp, voix, mode dégradé).
Phase 4 — Intelligence artificielle avancée et marketplace complète
IA de contrôle avancée (détection d’anomalies systématique), IA prédictive (trésorerie, chiffre d’affaires, charges, risques fiscaux).
Copilote comptable conversationnel complet, explicable.
Recommandations automatiques de mise en relation Marketplace.
Extension du moteur fiscal à un second, puis à d’autres pays de l’espace OHADA.
40. Tests et recette
🟠[Proposition complémentaire — non détaillé dans les documents source, exigé par le prompt maître]
41. Déploiement
🟠[Proposition complémentaire]
Déploiement pilote : une première entreprise cliente en conditions réelles (Phase 1), puis extension progressive.
Prérequis de déploiement pour le cas Institution/GEC (rappel Partie 35.3) : scanner à chaque secrétariat, connexion internet stable, postes d’administration dédiés, prototype pilote sur un seul service avant généralisation.
Environnements : développement, recette (avec données de test jamais en production, conformément à la Règle « aucune donnée fictive » Partie 26.1), production.
Bascule progressive par module (Secrétariat puis Comptabilité, ou l’inverse selon le profil du client pilote).
Plan de reprise après sinistre documenté et testé avant la mise en production réelle (Partie 26.2).
42. Formation
🟠[Proposition complémentaire]
Formation des équipes GEL (Secrétariat, Comptabilité) à l’utilisation de la plateforme et à la validation des propositions IA.
Documentation utilisateur en français, alignée avec l’exigence de lisibilité pour un dirigeant non-comptable (Partie 26 et Partie 24).
Formation spécifique des comptables du pool GEL et des professionnels indépendants sur le moteur fiscal multi-pays (à mesure de l’extension géographique).
Rattachement possible au pôle 7 « Formation professionnelle » de la vision stratégique (Partie 2.1), qui reste hors périmètre applicatif mais peut s’appuyer sur la documentation produite pour ce cahier des charges.
43. Maintenance et support
🟠[Proposition complémentaire]
Centre de tickets de support et annonces plateforme, piloté par le Super Administrateur (Partie 26.3).
Veille réglementaire continue (Partie 33) comme processus de maintenance récurrent, distinct de la maintenance applicative classique.
Sauvegardes régulières et tests de restauration périodiques (Partie 26.2).
Suivi du coût réel de l’IA par rapport à la facturation, avec ajustement de marge (Partie 13.9).
44. Évolutions futures
🟢[Document]
Ouverture de la Marketplace de mise en relation automatique (recherche par une entreprise d’un comptable, d’une secrétaire, d’un fiscaliste ou d’un consultant selon compétences, expérience, domaine d’expertise, disponibilité).
Extension du moteur fiscal aux autres pays OHADA (Côte d’Ivoire, Togo, Sénégal, Burkina Faso, Mali, Niger, Guinée, Cameroun, Gabon, Congo, RDC…), au rythme fixé par la roadmap commerciale, sans réécriture du socle applicatif.
Analyse prédictive avancée (anticipation des échéances à risque, recommandations sur le Score de Conformité GEL®).
Repère indicatif du plan quinquennal stratégique (non applicatif, contexte) :
45. Glossaire
🟢[Document]/🔵[Réglementaire]/🟠[Proposition complémentaire]
Annexe — Sources et limites de ce document
Ce cahier des charges a été produit à partir des documents fournis par l’utilisateur (deux cahiers des charges GEL, 24 captures d’écran du prototype, l’AUDCIF au format PDF) et de recherches web complémentaires explicitement demandées (référentiel plan-comptable-ohada.com, actualité fiscale Bénin 2026). Deux limites doivent être communiquées clairement à l’équipe projet :
Le contenu détaillé du CGI béninois 2026 et de la loi de finances 2026 n’a pas pu être vérifié article par article sur le texte officiel intégral dans le cadre de cette session (le PDF disponible sur budgetbenin.bj n’a pas pu être extrait en texte exploitable). Les taux et réformes cités en Partie 12 combinent les données déjà présentes dans les cahiers des charges transmis et des recherches web complémentaires ; ils doivent être formellement validés par un fiscaliste béninois avant tout paramétrage définitif du moteur fiscal en production.
Le texte intégral de l’AUDCIF transmis (PDF de 94 Mo) est un scan du Journal Officiel OHADA dont l’extraction automatique de texte s’est révélée en grande partie illisible (encodage/OCR défaillant), à l’exception de sa table des matières. La nomenclature des 9 classes et des comptes du plan SYSCOHADA utilisée dans ce document (Partie 10.2) provient donc de la consultation directe du site de référence demandé par l’utilisateur (plan-comptable-ohada.com), qui reprend la même nomenclature officielle. Pour toute question d’interprétation fine d’un compte (fonctionnement détaillé, exclusions, éléments de contrôle), il est recommandé de consulter directement l’AUDCIF ou plan-comptable-ohada.com au moment du développement du module concerné.
Fin du cahier des charges global du projet GEL.