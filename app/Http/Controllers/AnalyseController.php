<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Product;
use App\Services\SkinAnalysisService;
use Illuminate\Http\Request;

class AnalyseController extends Controller
{
    private SkinAnalysisService $skinAnalysis;

    public function __construct(SkinAnalysisService $skinAnalysis)
    {
        $this->skinAnalysis = $skinAnalysis;
    }

    /**
     * Affiche la page du questionnaire
     */
    public function index()
    {
        return view('consultation');
    }

    /**
     * Traite le questionnaire complet et retourne l'analyse
     * Integre l'IA via Hugging Face pour l'analyse photo
     */
    public function analyser(Request $request)
    {
        $validated = $request->validate([
            'analysis_type' => 'required|in:visage,corps,les_deux',
            'answers' => 'required|array',
            'photo' => 'nullable|image|max:10240',
        ]);

        $answers = $validated['answers'];
        $type = $validated['analysis_type'];
        $photoPath = null;
        $aiAnalysis = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('consultations', 'public');
        }

        // 1. Déterminer le type de peau
        $skinType = $this->determineSkinType($answers, $type);

        // 2. Déterminer l'état de la peau
        $skinState = $this->determineSkinState($answers, $type);

        // 3. Déterminer le problème principal
        $mainProblem = $this->determineMainProblem($answers, $type);

        // 4. Calculer le score de peau
        $skinScore = $this->calculateSkinScore($answers, $type, $skinType);
        $globalScore = intval(round(array_sum($skinScore) / count($skinScore)));

        // 5. Détecter les conditions cutanées via questionnaire
        $conditions = $this->detectSkinConditions($skinType, $skinState, $mainProblem, $answers, $type);

        // 5b. ANALYSE IA PHOTO (si photo disponible)
        if ($photoPath) {
            $aiAnalysis = $this->skinAnalysis->analyzeSkinPhoto($photoPath);

            // Fusionner les résultats IA avec ceux du questionnaire
            if (!empty($aiAnalysis['ai_used'])) {
                $conditions = $this->skinAnalysis->mergeWithQuestionnaire($aiAnalysis, $conditions);
            }
        }

        // 6. Recommander des produits
        $recommendedProducts = $this->recommendProducts($skinType, $mainProblem, $type, $conditions);
        $recommendedProductIds = collect($recommendedProducts)->pluck('id')->toArray();

        // 7. Générer la routine
        $routine = $this->generateRoutine($skinType, $mainProblem, $type);

        // 8. Construire le diagnostic
        $diagnostic = [
            'type_peau' => $skinType,
            'etat' => $skinState,
            'probleme_principal' => $mainProblem,
            'zone' => $type,
        ];

        // Construire le flag IA pour le frontend
        $aiUsed = $aiAnalysis && !empty($aiAnalysis['ai_used']);
        $responseAi = $aiAnalysis ? [
            'ai_used' => $aiUsed,
            'ai_confidence' => $aiAnalysis['ai_confidence'] ?? 0,
            'ai_enabled' => $aiAnalysis['ai_enabled'] ?? false,
            'models' => $aiAnalysis['models'] ?? [],
            'medical_flags' => $aiAnalysis['medical_flags'] ?? [],
            'scores' => $aiAnalysis['scores'] ?? [],
            'skin_age' => $aiAnalysis['skin_age'] ?? null,
        ] : [
            'ai_used' => false,
            'ai_enabled' => config('services.perfectcorp.enabled', false),
            'models' => [
                'provider' => 'Perfect Corp',
                'api' => 'Skin Analysis v2.1',
                'features' => '16 HD categories',
            ],
            'medical_flags' => [],
        ];

        // Sauvegarder la consultation
        $consultation = Consultation::create([
            'user_id' => auth()->id(),
            'area' => $type === 'les_deux' ? 'visage+corps' : $type,
            'analysis_type' => $type,
            'answers' => $answers,
            'skin_score' => $skinScore + ['global' => $globalScore],
            'diagnostic' => $diagnostic,
            'results' => [
                'condition' => $this->getConditionLabel($skinType, $mainProblem),
                'recommendations' => $routine,
                'severity' => $this->getSeverity($skinScore),
            ],
            'recommended_product_ids' => $recommendedProductIds,
            'photo_path' => $photoPath,
            'ai_analysis' => $responseAi,
            'status' => 'completed',
        ]);

        return response()->json([
            'id' => $consultation->id,
            'diagnostic' => $diagnostic,
            'skin_score' => $skinScore + ['global' => $globalScore],
            'condition' => $this->getConditionLabel($skinType, $mainProblem),
            'severity' => $this->getSeverity($skinScore),
            'conditions' => $conditions,
            'routine' => $routine,
            'products' => $recommendedProducts,
            'ai_analysis' => $responseAi,
        ]);
    }

    /**
     * Rechercher des produits dans la boutique
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('problem_tag', 'like', "%{$query}%")
            ->take(20)
            ->get();

        return response()->json($products);
    }

    // ==================== DÉTERMINATION TYPE DE PEAU ====================

    private function determineSkinType(array $answers, string $type): string
    {
        if ($type === 'visage' || $type === 'les_deux') {
            $brille = $answers['visage_brille'] ?? null;
            $tiraille = $answers['visage_tiraillements'] ?? null;

            if ($brille === 'oui' && $tiraille === 'non') return 'grasse';
            if ($brille === 'non' && $tiraille === 'oui') return 'seche';
            if ($brille === 'oui' && $tiraille === 'oui') return 'mixte';
        }

        if ($type === 'corps' || $type === 'les_deux') {
            $corpsType = $answers['corps_type'] ?? null;
            if ($corpsType) return $corpsType;
        }

        return 'normale';
    }

    // ==================== DÉTERMINATION ÉTAT PEAU ====================

    private function determineSkinState(array $answers, string $type): array
    {
        $state = [];

        if ($type === 'visage' || $type === 'les_deux') {
            if (($answers['visage_boutons'] ?? 'non') === 'oui') $state[] = 'acné';
            if (($answers['visage_teint_terne'] ?? 'non') === 'oui') $state[] = 'terne';
            if (($answers['visage_sensible'] ?? 'non') === 'oui') $state[] = 'sensible';
        }

        if ($type === 'corps' || $type === 'les_deux') {
            $teint = $answers['corps_teint'] ?? '';
            if ($teint === 'irregulier') $state[] = 'teint irrégulier';
            if ($teint === 'taches') $state[] = 'taches';
            if (($answers['corps_terne'] ?? 'non') === 'oui') $state[] = 'terne';
            if (($answers['corps_rugueux'] ?? 'non') === 'oui') $state[] = 'rugueux';
        }

        return array_unique($state);
    }

    // ==================== PROBLÈME PRINCIPAL ====================

    private function determineMainProblem(array $answers, string $type): string
    {
        if (!empty($answers['probleme_principal'])) {
            return $answers['probleme_principal'];
        }

        $problems = [];

        if ($type === 'visage' || $type === 'les_deux') {
            if (($answers['visage_boutons'] ?? 'non') === 'oui') $problems[] = 'acne';
            if (($answers['visage_teint_terne'] ?? 'non') === 'oui') $problems[] = 'terne';
            if (!empty($answers['visage_probleme'])) $problems[] = $answers['visage_probleme'];
        }

        if ($type === 'corps' || $type === 'les_deux') {
            if (!empty($answers['corps_problemes'])) {
                $problems[] = $answers['corps_problemes'];
            }
        }

        if (empty($problems)) return 'tous';
        return $problems[0];
    }

    // ==================== SCORE DE PEAU ====================

    private function calculateSkinScore(array $answers, string $type, string $skinType): array
    {
        $hydratation = 7;
        $eclat = 7;
        $sensibilite = 7;
        $imperfections = 7;

        if ($type === 'visage' || $type === 'les_deux') {
            if (($answers['visage_brille'] ?? 'non') === 'oui') $imperfections -= 2;
            if (($answers['visage_tiraillements'] ?? 'non') === 'oui') $hydratation -= 3;
            if (($answers['visage_boutons'] ?? 'non') === 'oui') $imperfections -= 3;
            if (($answers['visage_teint_terne'] ?? 'non') === 'oui') $eclat -= 3;
            if (($answers['visage_sensible'] ?? 'non') === 'oui') $sensibilite -= 3;

            $prob = $answers['visage_probleme'] ?? '';
            if ($prob === 'acne') $imperfections -= 2;
            if ($prob === 'taches') $eclat -= 2;
            if ($prob === 'rides') $hydratation -= 1;
            if ($prob === 'peau_seche') $hydratation -= 2;
            if ($prob === 'teint_irregulier') $eclat -= 2;
        }

        if ($type === 'corps' || $type === 'les_deux') {
            $corpsType = $answers['corps_type'] ?? '';
            if ($corpsType === 'seche') $hydratation -= 3;
            if ($corpsType === 'grasse') $imperfections -= 1;

            $teint = $answers['corps_teint'] ?? '';
            if ($teint === 'irregulier') $eclat -= 2;
            if ($teint === 'taches') $eclat -= 3;

            if (($answers['corps_terne'] ?? 'non') === 'oui') $eclat -= 2;
            if (($answers['corps_rugueux'] ?? 'non') === 'oui') $hydratation -= 2;

            $probs = $answers['corps_problemes'] ?? '';
            if ($probs === 'taches') $eclat -= 2;
            if ($probs === 'vergetures') $hydratation -= 1;
            if ($probs === 'boutons') $imperfections -= 2;
            if ($probs === 'terne') $eclat -= 2;
        }

        if ($skinType === 'grasse') $imperfections -= 1;
        if ($skinType === 'seche') $hydratation -= 1;
        if ($skinType === 'mixte') {
            $hydratation -= 1;
            $imperfections -= 1;
        }

        return [
            'hydratation' => max(1, min(10, $hydratation)),
            'eclat' => max(1, min(10, $eclat)),
            'sensibilite' => max(1, min(10, $sensibilite)),
            'imperfections' => max(1, min(10, $imperfections)),
        ];
    }

    // ==================== DÉTECTION DES CONDITIONS CUTANÉES ====================

    private function detectSkinConditions(string $skinType, array $skinState, string $mainProblem, array $answers, string $type): array
    {
        $conditions = [];
        $allConditions = $this->getSkinConditionsDatabase();

        // Carte de correspondance symptômes → conditions
        $symptomMap = [
            'acne' => ['acne_vulgaire', 'acne_retentionnelle'],
            'acné' => ['acne_vulgaire'],
            'boutons' => ['acne_vulgaire', 'folliculite'],
            'taches' => ['hyperpigmentation', 'melasma', 'taches_brunes'],
            'terne' => ['teint_terne', 'deshydratation', 'hyperkeratose'],
            'sensible' => ['dermatite_seborrheique', 'couperose', 'rosacee'],
            'secheresse' => ['xerose', 'dermatite_atopique', 'deshydratation'],
            'peau_seche' => ['xerose', 'dermatite_atopique'],
            'rides' => ['vieillissement_cutane', 'relachement_cutane'],
            'teint_irregulier' => ['hyperpigmentation', 'teint_terne'],
            'vergetures' => ['vergetures'],
            'rugueux' => ['hyperkeratose', 'xerose'],
            'grasse' => ['hyperseborrhee', 'acne_vulgaire'],
        ];

        // Collecter les symptômes
        $symptoms = [];
        foreach ($skinState as $state) {
            $symptoms[] = $state;
        }
        if ($mainProblem && $mainProblem !== 'tous') {
            $symptoms[] = $mainProblem;
        }
        $symptoms[] = $skinType;

        // Détecter les conditions
        $matchedIds = [];
        foreach ($symptoms as $symptom) {
            if (isset($symptomMap[$symptom])) {
                foreach ($symptomMap[$symptom] as $conditionId) {
                    $matchedIds[$conditionId] = ($matchedIds[$conditionId] ?? 0) + 1;
                }
            }
        }

        // Si aucun match, condition par défaut
        if (empty($matchedIds)) {
            $matchedIds['peau_saine'] = 1;
        }

        // Trier par score de correspondance
        arsort($matchedIds);

        // Prendre les 3 meilleures conditions
        $count = 0;
        foreach ($matchedIds as $id => $score) {
            if ($count >= 3) break;
            if (isset($allConditions[$id])) {
                $condition = $allConditions[$id];
                $condition['id'] = $id;
                $condition['score_confiance'] = min(100, 50 + ($score * 15));
                $conditions[] = $condition;
                $count++;
            }
        }

        return $conditions;
    }

    /**
     * Base de données complète des conditions cutanées avec causes et remèdes
     */
    private function getSkinConditionsDatabase(): array
    {
        return [
            'acne_vulgaire' => [
                'nom' => 'Acné Vulgaire',
                'type' => 'infection',
                'description' => 'Inflammation des follicules pileux causée par l\'obstruction des pores par le sébum et les cellules mortes.',
                'causes' => [
                    'Production excessive de sébum due aux hormones (androgènes)',
                    'Accumulation de cellules mortes à la surface de la peau',
                    'Prolifération de la bactérie Propionibacterium acnes',
                    'Facteurs génétiques prédisposants',
                    'Stress et alimentation riche en sucres raffinés',
                    'Utilisation de produits comédogènes obstruant les pores'
                ],
                'remedes' => [
                    'Nettoyer le visage matin et soir avec un gel purifiant doux',
                    'Appliquer un sérum anti-imperfections à base d\'acide salicylique',
                    'Utiliser une crème légère non comédogène pour hydrater sans obstruer',
                    'Faire un masque à l\'argile 1 à 2 fois par semaine',
                    'Éviter de toucher ou percer les boutons pour éviter les cicatrices',
                    'Consulter un dermatologue pour les cas sévères'
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'xerose' => [
                'nom' => 'Xérose (Peau Sèche Excessive)',
                'type' => 'deshydratation',
                'description' => 'Affection caractérisée par une sécheresse anormale de la peau due à un manque d\'eau ou de lipides dans la couche cornée.',
                'causes' => [
                    'Barrière cutanée affaiblie manquant de lipides naturels',
                    'Exposition au froid, au vent et au soleil intense',
                    'Bains trop chauds et utilisation de savons agressifs',
                    'Prédisposition génétique (ichtyose)',
                    'Certaines maladies (hypothyroïdie, diabète)',
                    'Vieillissement naturel réduisant la production de sébum'
                ],
                'remedes' => [
                    'Utiliser un nettoyant doux sans savon (syndet)',
                    'Appliquer une crème riche en céramides et acides gras essentiels',
                    'Boire au moins 1,5L d\'eau par jour',
                    'Éviter les douches trop chaudes et prolongées',
                    'Utiliser un humidificateur d\'air dans les pièces climatisées',
                    'Appliquer une huile végétale le soir pour renforcer la barrière cutanée'
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'dermatite_seborrheique' => [
                'nom' => 'Dermite Séborrhéique',
                'type' => 'inflammation',
                'description' => 'Affection inflammatoire chronique touchant les zones riches en glandes sébacées (cuir chevelu, visage, torse).',
                'causes' => [
                    'Prolifération d\'un champignon (Malassezia) sur les zones grasses',
                    'Production excessive de sébum créant un environnement favorable',
                    'Facteurs génétiques et hormonaux',
                    'Stress et fatigue affaiblissant le système immunitaire',
                    'Climat froid et sec aggravant les poussées'
                ],
                'remedes' => [
                    'Utiliser un nettoyant doux spécifique anti-champignon',
                    'Appliquer des soins à base de zinc pyrithione ou de kétoconazole',
                    'Éviter les produits alcoolisés qui irritent la peau',
                    'Gérer le stress par des techniques de relaxation',
                    'Hydrater légèrement avec une crème non grasse',
                    'Consulter un dermatologue pour un traitement adapté'
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'hyperpigmentation' => [
                'nom' => 'Hyperpigmentation (Taches Brunes)',
                'type' => 'pigmentation',
                'description' => 'Coloration localisée plus foncée de la peau due à une production excessive de mélanine.',
                'causes' => [
                    'Exposition solaire sans protection accumulée',
                    'Séquelles d\'acné et cicatrices inflammatoires',
                    'Changements hormonaux (grossesse, ménopause, pilule)',
                    'Certains médicaments photosensibilisants',
                    'Frottements répétés et irritations cutanées',
                    'Prédisposition génétique (mélasma)'
                ],
                'remedes' => [
                    'Appliquer un sérum à la Vitamine C chaque matin pour unifier le teint',
                    'Utiliser une protection solaire SPF 50+ quotidiennement (indispensable !)',
                    'Appliquer un soin éclaircissant à base d\'acide kojique ou de niacinamide',
                    'Exfolier doucement la peau 1 fois par semaine avec des AHA',
                    'Éviter l\'exposition solaire aux heures chaudes (10h-16h)',
                    'La patience est clé : le traitement des taches prend 2 à 6 mois'
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'melasma' => [
                'nom' => 'Mélasma (Masque de Grossesse)',
                'type' => 'pigmentation',
                'description' => 'Taches brunes symétriques sur le visage, souvent liées aux variations hormonales.',
                'causes' => [
                    'Changements hormonaux (grossesse, contraception hormonale)',
                    'Exposition solaire intense sans protection',
                    'Prédisposition génétique (fréquent chez les peaux foncées)',
                    'Dysfonctionnement thyroïdien',
                    'Certains cosmétiques photosensibilisants'
                ],
                'remedes' => [
                    'Protection solaire stricte SPF 50+ toutes les 2 heures en extérieur',
                    'Sérums dépigmentants à base d\'acide tranexamique',
                    'Crème éclaircissante à l\'hydroquinone (sur avis médical)',
                    'Éviter tout produit irritant qui aggrave l\'inflammation',
                    'Consultation dermatologique pour les traitements laser',
                    'Patience : le traitement peut prendre 6 à 12 mois'
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'teint_terne' => [
                'nom' => 'Teint Terne et Fatigué',
                'type' => 'deshydratation',
                'description' => 'Manque d\'éclat et de luminosité de la peau dû à une accumulation de cellules mortes et une mauvaise circulation.',
                'causes' => [
                    'Accumulation de cellules mortes à la surface de la peau',
                    'Manque d\'hydratation cutanée',
                    'Fatigue et manque de sommeil',
                    'Alimentation déséquilibrée pauvre en antioxydants',
                    'Tabac et alcool réduisant l\'oxygénation de la peau',
                    'Pollution et radicaux libres oxydant les cellules'
                ],
                'remedes' => [
                    'Exfolier la peau 1 à 2 fois par semaine avec un gommage doux',
                    'Appliquer un sérum à la Vitamine C chaque matin pour booster l\'éclat',
                    'Hydrater la peau matin et soir avec une crème adaptée',
                    'Boire au moins 1,5L d\'eau par jour',
                    'Dormir suffisamment (7-8h) pour permettre la régénération',
                    'Consommer des aliments riches en antioxydants (fruits, légumes verts)'
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'dermatite_atopique' => [
                'nom' => 'Dermatite Atopique (Eczéma)',
                'type' => 'inflammation',
                'description' => 'Maladie inflammatoire chronique de la peau caractérisée par des plaques rouges qui démangent, souvent d\'origine allergique.',
                'causes' => [
                    'Prédisposition génétique (antécédents familiaux d\'allergies)',
                    'Barrière cutanée défaillante qui laisse passer les irritants',
                    'Réaction excessive du système immunitaire',
                    'Allergènes environnementaux (acariens, pollen)',
                    'Stress et facteurs émotionnels déclencheurs',
                    'Climat sec et froid aggravant les symptômes'
                ],
                'remedes' => [
                    'Hydrater intensément avec des crèmes barrières sans parfum',
                    'Utiliser des nettoyants surgras pour ne pas agresser la peau',
                    'Identifier et éviter les allergènes déclencheurs',
                    'Porter des vêtements en coton doux',
                    'Consulter un dermatologue pour les crises sévères',
                    'Appliquer des corticoïdes sur prescription médicale en cas de crise'
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'couperose' => [
                'nom' => 'Couperose (Télangiectasies)',
                'type' => 'vasculaire',
                'description' => 'Dilatation permanente des petits vaisseaux sanguins du visage créant des rougeurs visibles, surtout sur les joues et le nez.',
                'causes' => [
                    'Fragilité vasculaire héréditaire',
                    'Exposition aux écarts de température (chaud/froid)',
                    'Consommation d\'alcool et d\'épices fortes',
                    'Exposition solaire intense sans protection',
                    'Grossesse et changements hormonaux',
                    'Hypertension artérielle et efforts physiques intenses'
                ],
                'remedes' => [
                    'Utiliser des soins doux spécifiques pour peaux sensibles',
                    'Appliquer des soins anti-rougeurs à base de vitamine C et d\'extraits de marron d\'Inde',
                    'Éviter les gommages agressifs et l\'eau très chaude',
                    'Protéger la peau du soleil avec un SPF 50+',
                    'Consulter un dermatologue pour les traitements laser',
                    'Éviter les aliments vasodilatateurs (alcool, caféine, plats épicés)'
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'rosacee' => [
                'nom' => 'Rosacée',
                'type' => 'inflammation',
                'description' => 'Affection cutanée chronique du visage causant rougeurs, vaisseaux visibles et parfois boutons ressemblant à l\'acné.',
                'causes' => [
                    'Prédisposition génétique (peaux claires surtout)',
                    'Dysfonctionnement du système immunitaire cutané',
                    'Présence excessive d\'acariens (Demodex) dans les follicules',
                    'Facteurs déclencheurs : soleil, stress, alcool, épices',
                    'Perturbation de la barrière cutanée'
                ],
                'remedes' => [
                    'Nettoyage doux avec des produits sans savon ni alcool',
                    'Appliquer des soins anti-inflammatoires spécifiques',
                    'Éviter les facteurs déclencheurs identifiés',
                    'Protection solaire impérative SPF 50+',
                    'Consulter un dermatologue pour un traitement médical',
                    'Utiliser un maquillage vert pour neutraliser les rougeurs'
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'vieillissement_cutane' => [
                'nom' => 'Vieillissement Cutané (Rides)',
                'type' => 'vieillissement',
                'description' => 'Apparition progressive des rides, ridules et perte de fermeté due au vieillissement naturel et aux facteurs environnementaux.',
                'causes' => [
                    'Vieillissement chronologique naturel (perte de collagène dès 25 ans)',
                    'Exposition solaire excessive sans protection (photo-vieillissement)',
                    'Tabac : réduit l\'oxygénation et détruit le collagène',
                    'Stress oxydatif des radicaux libres',
                    'Répétition des expressions faciales (rides d\'expression)',
                    'Alimentation déséquilibrée pauvre en antioxydants'
                ],
                'remedes' => [
                    'Appliquer un sérum anti-âge à base de rétinol le soir',
                    'Utiliser une crème hydratante enrichie en acide hyaluronique',
                    'Protection solaire SPF 50+ quotidienne (anti-âge #1)',
                    'Massages du visage pour stimuler la microcirculation',
                    'Consommer des aliments riches en oméga-3 et antioxydants',
                    'Dormir suffisamment pour permettre la régénération cellulaire'
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'vergetures' => [
                'nom' => 'Vergetures',
                'type' => 'cicatrice',
                'description' => 'Lésions cutanées linéaires dues à une rupture des fibres élastiques et de collagène, souvent liées à des variations de poids.',
                'causes' => [
                    'Étirement rapide de la peau (grossesse, croissance rapide)',
                    'Prise ou perte de poids importante',
                    'Facteurs hormonaux (cortisol élevé fragilise les fibres)',
                    'Prédisposition génétique',
                    'Pratique intensive de musculation sans hydratation cutanée'
                ],
                'remedes' => [
                    'Appliquer des huiles riches en acides gras (amande douce, avocat)',
                    'Masser quotidiennement pour stimuler la production de collagène',
                    'Utiliser des crèmes à base d\'acide hyaluronique',
                    'Exfolier la zone 1 à 2 fois par semaine',
                    'Hydrater la peau en profondeur avec un lait corporel riche',
                    'Consulter pour des traitements laser ou micro-aiguilles'
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'folliculite' => [
                'nom' => 'Folliculite',
                'type' => 'infection',
                'description' => 'Infection des follicules pileux causant de petits boutons rouges souvent accompagnés de démangeaisons.',
                'causes' => [
                    'Infection bactérienne (staphylocoque) ou fongique',
                    'Poils incarnés après épilation ou rasage',
                    'Transpiration excessive et frottements',
                    'Vêtements trop serrés qui irritent les follicules',
                    'Bains chauds insuffisamment chlorés'
                ],
                'remedes' => [
                    'Nettoyer la zone avec un antiseptique doux',
                    'Éviter le rasage ou l\'épilation pendant la poussée',
                    'Porter des vêtements amples en coton',
                    'Appliquer une crème antibiotique locale',
                    'Consulter un dermatologue si persistant',
                    'Utiliser des gommages doux pour libérer les poils incarnés'
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'deshydratation' => [
                'nom' => 'Déshydratation Cutanée',
                'type' => 'deshydratation',
                'description' => 'Manque d\'eau dans la couche superficielle de la peau, pouvant toucher tous les types de peau, même les peaux grasses.',
                'causes' => [
                    'Consommation insuffisante d\'eau',
                    'Climat sec, ventilation ou climatisation excessive',
                    'Utilisation de produits trop agressifs qui décapent la barrière cutanée',
                    'Consommation excessive de caféine et d\'alcool (diurétiques)',
                    'Certains médicaments (diurétiques, rétinoïdes)'
                ],
                'remedes' => [
                    'Boire au moins 1,5 à 2L d\'eau par jour',
                    'Utiliser un sérum à l\'acide hyaluronique pour capter l\'eau',
                    'Appliquer une crème hydratante adaptée matin et soir',
                    'Éviter les nettoyants trop agressifs',
                    'Utiliser un brumisateur d\'eau thermale dans la journée',
                    'Humidifier votre environnement de travail'
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'hyperseborrhee' => [
                'nom' => 'Hyperséborrhée (Peau Grasse)',
                'type' => 'sebum',
                'description' => 'Production excessive de sébum par les glandes sébacées, donnant un aspect brillant et gras à la peau, surtout sur la zone T.',
                'causes' => [
                    'Stimulation hormonale par les androgènes',
                    'Prédisposition génétique',
                    'Alimentation riche en sucres et graisses saturées',
                    'Stress augmentant la production de cortisol',
                    'Utilisation de produits trop décapants qui stimulent la production de sébum en réaction'
                ],
                'remedes' => [
                    'Nettoyer le visage matin et soir avec un gel purifiant doux',
                    'Utiliser une crème légère matifiante non comédogène',
                    'Appliquer un masque à l\'argile 2 fois par semaine',
                    'Éviter les produits alcoolisés qui stimulent la production de sébum',
                    'Utiliser des papiers matifiants dans la journée'
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'hyperkeratose' => [
                'nom' => 'Hyperkératose (Peau Rugueuse)',
                'type' => 'deshydratation',
                'description' => 'Accumulation excessive de cellules mortes à la surface de la peau, donnant une texture rugueuse et un aspect terne.',
                'causes' => [
                    'Renouvellement cellulaire ralenti (avec l\'âge)',
                    'Manque d\'hydratation et de nutrition cutanée',
                    'Exposition solaire chronique',
                    'Carences nutritionnelles (vitamines A, B, E)',
                    'Frottements répétés sur certaines zones'
                ],
                'remedes' => [
                    'Exfolier régulièrement avec des acides de fruits (AHA)',
                    'Hydrater intensément après chaque exfoliation',
                    'Appliquer un sérum à base de vitamine A (rétinol)',
                    'Boire suffisamment d\'eau',
                    'Utiliser un gommage doux 1 à 2 fois par semaine'
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'peau_saine' => [
                'nom' => 'Peau Équilibrée',
                'type' => 'saine',
                'description' => 'Votre peau est bien équilibrée, ni trop grasse ni trop sèche, avec un teint uniforme et peu d\'imperfections.',
                'causes' => [
                    'Bonne hygiène de vie et alimentation équilibrée',
                    'Routine de soins adaptée et régulière',
                    'Bonne hydratation interne et externe',
                    'Protection solaire adéquate'
                ],
                'remedes' => [
                    'Continuer votre routine de soins actuelle qui vous convient',
                    'Maintenir une bonne hydratation quotidienne',
                    'Ne pas négliger la protection solaire même par temps nuageux',
                    'Exfolier doucement 1 fois par semaine pour maintenir l\'éclat'
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
        ];
    }

    // ==================== RECOMMANDATIONS PRODUITS ====================

    private function recommendProducts(string $skinType, string $mainProblem, string $type, array $conditions): array
    {
        // Collecter les mots-clés des conditions
        $keywords = [$skinType, $mainProblem];
        foreach ($conditions as $condition) {
            if (isset($condition['id'])) {
                $keywords[] = $condition['id'];
            }
        }

        $query = Product::query();

        // Filtrer par type de peau
        if ($skinType !== 'normale') {
            $query->where(function ($q) use ($skinType) {
                $q->where('skin_type_tag', $skinType)
                  ->orWhere('skin_type_tag', 'tous');
            });
        }

        // Filtrer par problème
        if ($mainProblem !== 'tous') {
            $query->where(function ($q) use ($mainProblem) {
                $q->where('problem_tag', $mainProblem)
                  ->orWhere('problem_tag', 'tous');
            });
        }

        // Prioriser les soins visage ou corps
        if ($type === 'visage') {
            $query->where('routine_step', '!=', 'corps');
        }
        if ($type === 'corps') {
            $query->where(function ($q) {
                $q->where('routine_step', 'corps')
                  ->orWhere('category', 'Huiles Naturelles');
            });
        }

        $products = $query->get();

        // Élargir si pas assez
        if ($products->count() < 3) {
            $products = Product::where(function ($q) use ($skinType) {
                $q->where('skin_type_tag', $skinType)
                  ->orWhere('skin_type_tag', 'tous');
            })->get();
        }

        if ($products->count() < 3) {
            $products = Product::all();
        }

        // Trier : Victoire en premier, puis par note
        $products = $products->sortByDesc(function ($p) {
            return ($p->is_victoire ? 100 : 0) + ($p->rating * 10);
        })->values();

        return $products->take(8)->toArray();
    }

    // ==================== GÉNÉRATION ROUTINE ====================

    private function generateRoutine(string $skinType, string $mainProblem, string $type): array
    {
        $routine = [];

        if ($type === 'visage' || $type === 'les_deux') {
            $routine[] = match ($skinType) {
                'grasse' => 'Nettoyage matin et soir avec un gel moussant purifiant',
                'seche' => 'Nettoyage doux avec un savon surgras matin et soir',
                'mixte' => 'Nettoyage doux + tonique sur la zone T (front, nez, menton)',
                default => 'Nettoyage doux matin et soir',
            };

            $routine[] = match ($mainProblem) {
                'acne' => 'Appliquer un sérum anti-imperfections matin et soir sur les zones à problème',
                'taches' => 'Appliquer un sérum à la Vitamine C le matin pour uniformiser le teint',
                'rides' => 'Utiliser un soin contour des yeux et une crème anti-âge soir',
                'terne' => 'Exfoliation douce 1x/semaine + sérum éclat chaque matin',
                'peau_seche', 'secheresse' => 'Hydratation intense avec une crème riche matin et soir',
                'teint_irregulier' => 'Sérum éclat + protection solaire SPF50 chaque matin',
                default => 'Hydratation adaptée matin et soir',
            };

            $routine[] = match ($skinType) {
                'grasse' => 'Masque à l\'argile 1 fois par semaine pour purifier les pores',
                'seche' => 'Huile nourrissante en complément le soir pour renforcer la barrière cutanée',
                default => 'Masque hydratant 1 fois par semaine',
            };

            $routine[] = 'Protection solaire SPF50+ chaque matin pour prévenir le vieillissement cutané';
        }

        if ($type === 'corps' || $type === 'les_deux') {
            $routine[] = match ($skinType) {
                'seche' => 'Hydrater le corps quotidiennement avec un lait corporel nourrissant',
                'grasse' => 'Hydrater le corps avec un lait léger non comédogène',
                default => 'Hydratation corporelle quotidienne adaptée',
            };

            if ($mainProblem === 'terne' || $mainProblem === 'taches') {
                $routine[] = 'Exfolier le corps 1 à 2 fois par semaine avec un gommage doux';
                $routine[] = 'Boire au moins 1,5L d\'eau par jour pour améliorer l\'éclat de la peau';
            }

            if ($mainProblem === 'vergetures') {
                $routine[] = 'Appliquer une huile anti-vergetures matin et soir en massant les zones concernées';
            }

            if ($mainProblem === 'boutons' || $mainProblem === 'acne') {
                $routine[] = 'Utiliser un gel douche purifiant sans savon';
            }
        }

        return $routine;
    }

    // ==================== LABELS ====================

    private function getConditionLabel(string $skinType, string $mainProblem): string
    {
        $labels = [
            'grasse' => [
                'acne' => 'Peau grasse avec tendance acnéique',
                'taches' => 'Peau grasse avec des taches pigmentaires',
                'tous' => 'Peau grasse à tendance séborrhéique',
            ],
            'seche' => [
                'acne' => 'Peau sèche avec imperfections',
                'taches' => 'Peau sèche avec taches pigmentaires',
                'secheresse' => 'Peau déshydratée manquant de confort',
                'tous' => 'Peau sèche manquant d\'hydratation',
            ],
            'mixte' => [
                'acne' => 'Peau mixte à tendance acnéique (zone T grasse)',
                'taches' => 'Peau mixte avec taches et teint irrégulier',
                'tous' => 'Peau mixte (zone T grasse, zones latérales normales)',
            ],
            'normale' => [
                'terne' => 'Peau normale au teint terne, manque d\'éclat',
                'taches' => 'Peau normale avec taches brunes localisées',
                'tous' => 'Peau normale équilibrée',
            ],
        ];

        return $labels[$skinType][$mainProblem]
            ?? $labels[$skinType]['tous']
            ?? "Peau $skinType — $mainProblem";
    }

    private function getSeverity(array $score): string
    {
        $avg = array_sum($score) / count($score);
        if ($avg >= 8) return 'Excellent';
        if ($avg >= 6) return 'Normal';
        if ($avg >= 4) return 'Léger';
        return 'Nécessite attention';
    }

    // ==================== HISTORIQUE ====================

    public function historique()
    {
        $consultations = Consultation::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'date' => $c->created_at->format('d/m/Y'),
                    'diagnostic' => $c->diagnostic,
                    'skin_score' => $c->skin_score,
                ];
            });

        return response()->json($consultations);
    }
}
