<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SkinAnalysisService
{
    private string $apiKey;
    private string $baseUrl;
    private bool $enabled;
    private int $timeout = 30;
    private int $pollInterval = 2; // seconds between polls
    private int $maxPollTime = 40; // max seconds to poll

    public function __construct()
    {
        $this->apiKey = config('services.perfectcorp.api_key', '');
        $this->baseUrl = config('services.perfectcorp.base_url', 'https://yce-api-01.makeupar.com');
        $this->enabled = config('services.perfectcorp.enabled', false) && !empty($this->apiKey);
    }

    /**
     * Point d'entrée principal : analyse une photo via Perfect Corp API
     */
    public function analyzeSkinPhoto(string $photoPath): array
    {
        if (!$this->enabled) {
            return [
                'ai_enabled' => false,
                'ai_used' => false,
                'message' => 'Perfect Corp API non configuree. Definissez PERFECT_CORP_API_KEY et SKIN_ANALYSIS_AI_ENABLED=true',
                'ai_conditions' => [],
                'ai_confidence' => 0,
                'scores' => [],
            ];
        }

        try {
            $imageData = $this->getImageData($photoPath);
            if (!$imageData) {
                throw new \RuntimeException('Impossible de lire l\'image');
            }

            $fileName = basename($photoPath);
            $mimeType = $this->getMimeType($fileName);

            // Step 1: Initier l'upload du fichier
            $fileUpload = $this->initiateFileUpload($imageData, $fileName, $mimeType);
            $fileId = $fileUpload['file_id'];
            $presignedUrl = $fileUpload['presigned_url'];
            $uploadHeaders = $fileUpload['upload_headers'];

            // Step 2: Uploader le fichier vers S3
            $this->uploadToS3($presignedUrl, $uploadHeaders, $imageData);

            // Step 3: Creer la tâche d'analyse
            $taskId = $this->createAnalysisTask($fileId);

            // Step 4: Pôller les résultats
            $results = $this->pollTaskResults($taskId);

            // Mapper les scores vers nos conditions
            $mapped = $this->mapResultsToConditions($results);

            return [
                'ai_enabled' => true,
                'ai_used' => true,
                'provider' => 'perfect_corp',
                'models' => [
                    'api' => 'Perfect Corp Skin Analysis v2.1',
                    'features' => '16 HD categories (acne, moisture, radiance, pores, wrinkles, redness, oiliness, firmness, age_spot, skin_type, ...)',
                ],
                'raw_scores' => $results,
                'scores' => $mapped['scores'],
                'ai_conditions' => $mapped['conditions'],
                'ai_confidence' => $mapped['avg_confidence'],
                'skin_age' => $results['skin_age'] ?? null,
                'medical_flags' => [],
            ];

        } catch (\Throwable $e) {
            Log::warning('SkinAnalysisService: Perfect Corp API failed', [
                'error' => $e->getMessage(),
                'photo' => $photoPath,
            ]);

            return [
                'ai_enabled' => true,
                'ai_used' => false,
                'error' => $e->getMessage(),
                'ai_conditions' => [],
                'ai_confidence' => 0,
                'scores' => [],
                'medical_flags' => [],
            ];
        }
    }

    // ==================== PERFECT CORP API FLOW ====================

    /**
     * Step 1: Initier l'upload du fichier
     */
    private function initiateFileUpload(string $imageData, string $fileName, string $mimeType): array
    {
        $fileSize = strlen($imageData);

        $response = $this->callApi('POST', '/s2s/v2.1/file/skin-analysis', [
            'files' => [
                [
                    'content_type' => $mimeType,
                    'file_name' => $fileName,
                    'file_size' => $fileSize,
                ],
            ],
        ]);

        if (empty($response['data']['files'][0])) {
            throw new \RuntimeException('Reponse upload invalide: fichier non recu');
        }

        $fileInfo = $response['data']['files'][0];
        $requestInfo = $fileInfo['requests'][0] ?? [];

        return [
            'file_id' => $fileInfo['file_id'],
            'presigned_url' => $requestInfo['url'] ?? '',
            'upload_headers' => $requestInfo['headers'] ?? [],
        ];
    }

    /**
     * Step 2: Uploader le fichier vers le presigned URL S3
     */
    private function uploadToS3(string $url, array $headers, string $imageData): void
    {
        if (empty($url)) {
            throw new \RuntimeException('URL presignee vide');
        }

        $ch = curl_init();
        $curlHeaders = [];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = "{$key}: {$value}";
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_PUT => true,
            CURLOPT_INFILE => fopen('data://application/octet-stream;base64,' . base64_encode($imageData), 'r'),
            CURLOPT_INFILESIZE => strlen($imageData),
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode >= 400) {
            throw new \RuntimeException("Upload S3 echoue: HTTP {$httpCode} - {$error}");
        }
    }

    /**
     * Step 3: Creer la tâche d'analyse
     */
    private function createAnalysisTask(string $fileId): string
    {
        $payload = [
            'src_file_id' => $fileId,
            'dst_actions' => [
                'hd_wrinkle',
                'hd_pore',
                'hd_texture',
                'hd_acne',
                'hd_redness',
                'hd_oiliness',
                'hd_radiance',
                'hd_moisture',
                'hd_age_spot',
                'hd_dark_circle',
                'hd_eye_bag',
                'hd_droopy_upper_eyelid',
                'hd_droopy_lower_eyelid',
                'hd_firmness',
                'hd_tear_trough',
                'hd_skin_type',
            ],
            'format' => 'json',
        ];

        $response = $this->callApi('POST', '/s2s/v2.1/task/skin-analysis', $payload);

        if (empty($response['data']['task_id'])) {
            throw new \RuntimeException('Pas de task_id dans la reponse');
        }

        return $response['data']['task_id'];
    }

    /**
     * Step 4: Poller les résultats
     */
    private function pollTaskResults(string $taskId): array
    {
        $startTime = time();
        $lastError = null;

        while (time() - $startTime < $this->maxPollTime) {
            try {
                $response = $this->callApi('GET', "/s2s/v2.1/task/skin-analysis/{$taskId}");

                $status = $response['data']['task_status'] ?? 'running';

                if ($status === 'success') {
                    $results = $response['data']['results'] ?? [];

                    // format=json: results.output contient le tableau de scores
                    if (!empty($results['output'])) {
                        return $this->parseOutputToScores($results['output']);
                    }

                    // Fallback: résultats bruts
                    return $results;
                }

                if ($status === 'error') {
                    throw new \RuntimeException('Tache d\'analyse en erreur');
                }

                // Toujours en cours, attendre
                sleep($this->pollInterval);

            } catch (\Throwable $e) {
                $lastError = $e;
                sleep($this->pollInterval);
            }
        }

        throw $lastError ?? new \RuntimeException('Timeout en attendant les resultats de l\'analyse');
    }

    /**
     * Parse le tableau output[] en un tableau associatif feature → score
     */
    private function parseOutputToScores(array $output): array
    {
        $scores = [];

        foreach ($output as $item) {
            $type = $item['type'] ?? '';
            if ($type) {
                $scores[$type] = [
                    'ui_score' => intval($item['ui_score'] ?? 0),
                    'raw_score' => floatval($item['raw_score'] ?? 0),
                ];
            }
        }

        // Extraire skin_age du haut niveau si présent
        // Note: skin_age est parfois dans un champ "all" ou "skin_age"
        // On le laisse vide par défaut, il sera rempli si présent

        return $scores;
    }

    // ==================== MAPPING ====================

    /**
     * Mappe les scores Perfect Corp vers nos métriques et conditions
     */
    private function mapResultsToConditions(array $scores): array
    {
        $mappedScores = [];
        $conditions = [];
        $totalConfidence = 0;
        $count = 0;

        // Hydratation (de moisture, inverse: plus moisture = mieux)
        $moisture = $scores['moisture']['ui_score'] ?? 50;
        $mappedScores['hydratation'] = max(1, min(10, intval(round($moisture / 10))));

        // Eclat (de radiance)
        $radiance = $scores['radiance']['ui_score'] ?? 50;
        $mappedScores['eclat'] = max(1, min(10, intval(round($radiance / 10))));

        // Sensibilite (de redness, inverse)
        $redness = $scores['redness']['ui_score'] ?? 30;
        $mappedScores['sensibilite'] = max(1, min(10, intval(round((100 - $redness) / 10))));

        // Imperfections (moyenne acne + pores, inverse)
        $acne = $scores['acne']['ui_score'] ?? 20;
        $pores = $scores['pore']['ui_score'] ?? 30;
        $avgImperfections = ($acne + $pores) / 2;
        $mappedScores['imperfections'] = max(1, min(10, intval(round((100 - $avgImperfections) / 10))));

        // Detection des conditions depuis les scores

        // Peau seche / deshydratee
        if ($moisture < 40) {
            $confidence = min(95, intval(round((40 - $moisture) * 2.5)));
            $conditions[] = [
                'condition_id' => 'xerose',
                'confidence' => $confidence,
                'source' => 'perfect_corp_moisture',
                'label' => 'Peau seche detectee par IA',
            ];
            $totalConfidence += $confidence;
            $count++;
        }

        if ($moisture < 60 && $moisture >= 40) {
            $confidence = 50;
            $conditions[] = [
                'condition_id' => 'deshydratation',
                'confidence' => $confidence,
                'source' => 'perfect_corp_moisture',
                'label' => 'Deshydratation cutanee detectee par IA',
            ];
            $totalConfidence += $confidence;
            $count++;
        }

        // Acne
        if ($acne > 20) {
            $confidence = min(95, intval(round($acne * 1.5)));
            $conditions[] = [
                'condition_id' => 'acne_vulgaire',
                'confidence' => $confidence,
                'source' => 'perfect_corp_acne',
                'label' => 'Acne detectee par IA',
            ];
            $totalConfidence += $confidence;
            $count++;
        }

        // Hyperpigmentation
        $ageSpot = $scores['age_spot']['ui_score'] ?? 0;
        if ($ageSpot > 20) {
            $confidence = min(90, intval(round($ageSpot + 10)));
            $conditions[] = [
                'condition_id' => 'hyperpigmentation',
                'confidence' => $confidence,
                'source' => 'perfect_corp_age_spot',
                'label' => 'Taches pigmentaires detectees par IA',
            ];
            $totalConfidence += $confidence;
            $count++;
        }

        // Couperose / rougeurs
        if ($redness > 35) {
            $confidence = min(90, intval(round($redness * 1.2)));
            $conditions[] = [
                'condition_id' => 'couperose',
                'confidence' => $confidence,
                'source' => 'perfect_corp_redness',
                'label' => 'Rougeurs detectees par IA',
            ];
            $totalConfidence += $confidence;
            $count++;
        }

        // Vieillissement cutane
        $wrinkle = $scores['wrinkle']['ui_score'] ?? 0;
        if ($wrinkle > 45) {
            $confidence = min(90, intval(round($wrinkle * 0.9)));
            $conditions[] = [
                'condition_id' => 'vieillissement_cutane',
                'confidence' => $confidence,
                'source' => 'perfect_corp_wrinkle',
                'label' => 'Signes de vieillissement detectes par IA',
            ];
            $totalConfidence += $confidence;
            $count++;
        }

        // Teint terne
        if ($radiance < 40) {
            $confidence = min(85, intval(round((40 - $radiance) * 2)));
            $conditions[] = [
                'condition_id' => 'teint_terne',
                'confidence' => $confidence,
                'source' => 'perfect_corp_radiance',
                'label' => 'Teint terne detecte par IA',
            ];
            $totalConfidence += $confidence;
            $count++;
        }

        // Peau grasse / hyperseborrhee
        $oiliness = $scores['oiliness']['ui_score'] ?? 0;
        if ($oiliness > 60) {
            $confidence = min(90, intval(round($oiliness * 0.8)));
            $conditions[] = [
                'condition_id' => 'hyperseborrhee',
                'confidence' => $confidence,
                'source' => 'perfect_corp_oiliness',
                'label' => 'Peau grasse detectee par IA',
            ];
            $totalConfidence += $confidence;
            $count++;
        }

        // Detection du type de peau depuis skin_type
        $skinTypeResult = $scores['skin_type']['ui_score'] ?? null;
        // skin_type a des valeurs speciales: toute la zone T/U
        // Pour l'instant on utilise oiliness comme proxy

        $avgConfidence = $count > 0 ? intval(round($totalConfidence / $count)) : 0;

        return [
            'scores' => $mappedScores,
            'conditions' => $conditions,
            'avg_confidence' => $avgConfidence,
        ];
    }

    /**
     * Fusionne les conditions detectees par l'IA avec celles du questionnaire
     */
    public function mergeWithQuestionnaire(array $aiResults, array $questionnaireConditions): array
    {
        if (empty($aiResults['ai_used']) || empty($aiResults['ai_conditions'])) {
            return $questionnaireConditions;
        }

        $aiConditions = $aiResults['ai_conditions'];
        $merged = [];

        foreach ($questionnaireConditions as $qc) {
            $conditionId = $qc['id'] ?? '';
            $found = false;

            foreach ($aiConditions as $aiCond) {
                if ($aiCond['condition_id'] === $conditionId) {
                    $qc['score_confiance'] = min(100, ($qc['score_confiance'] ?? 50) + 20);
                    $qc['ai_confirmed'] = true;
                    $qc['ai_confidence'] = $aiCond['confidence'];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $qc['ai_confirmed'] = false;
            }

            $merged[] = $qc;
        }

        // Ajouter les conditions trouvees par l'IA mais pas par le questionnaire
        foreach ($aiConditions as $aiCond) {
            $alreadyExists = false;
            foreach ($merged as $mc) {
                if (($mc['id'] ?? '') === $aiCond['condition_id']) {
                    $alreadyExists = true;
                    break;
                }
            }

            if (!$alreadyExists && $aiCond['condition_id'] !== '__medical__') {
                $conditionData = $this->getConditionData($aiCond['condition_id']);
                if ($conditionData) {
                    $conditionData['id'] = $aiCond['condition_id'];
                    $conditionData['score_confiance'] = $aiCond['confidence'];
                    $conditionData['ai_confirmed'] = true;
                    $conditionData['ai_detected'] = true;
                    $conditionData['ai_confidence'] = $aiCond['confidence'];
                    $merged[] = $conditionData;
                }
            }
        }

        usort($merged, function ($a, $b) {
            return ($b['score_confiance'] ?? 0) <=> ($a['score_confiance'] ?? 0);
        });

        return array_slice($merged, 0, 3);
    }

    // ==================== API CALLS ====================

    /**
     * Appelle l'API Perfect Corp
     */
    private function callApi(string $method, string $path, ?array $body = null): array
    {
        $url = $this->baseUrl . $path;

        $ch = curl_init();
        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ];

        if ($method === 'POST' && $body !== null) {
            $curlOpts[CURLOPT_POST] = true;
            $curlOpts[CURLOPT_POSTFIELDS] = json_encode($body);
        } elseif ($method === 'GET') {
            $curlOpts[CURLOPT_HTTPGET] = true;
        }

        curl_setopt_array($ch, $curlOpts);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("Erreur API Perfect Corp ({$path}): {$error}");
        }

        if ($httpCode === 401) {
            throw new \RuntimeException('Cle API Perfect Corp invalide ou expiree');
        }

        if ($httpCode === 400) {
            $decoded = json_decode($response, true);
            $errorMsg = $decoded['error'] ?? 'Parametres invalides';
            throw new \RuntimeException("Perfect Corp 400: {$errorMsg}");
        }

        if ($httpCode === 403) {
            throw new \RuntimeException('Credit Perfect Corp insuffisant');
        }

        if ($httpCode >= 500) {
            throw new \RuntimeException("Perfect Corp erreur serveur HTTP {$httpCode}");
        }

        if ($httpCode !== 200) {
            throw new \RuntimeException("Perfect Corp HTTP {$httpCode}: " . substr($response, 0, 200));
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Reponse JSON invalide de Perfect Corp');
        }

        return $decoded;
    }

    // ==================== UTILITAIRES ====================

    private function getImageData(string $photoPath): ?string
    {
        $fullPath = storage_path('app/public/' . $photoPath);
        if (file_exists($fullPath)) {
            return file_get_contents($fullPath);
        }

        if (file_exists($photoPath)) {
            return file_get_contents($photoPath);
        }

        if (Storage::disk('public')->exists($photoPath)) {
            return Storage::disk('public')->get($photoPath);
        }

        return null;
    }

    private function getMimeType(string $fileName): string
    {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpg',
        };
    }

    /**
     * Retourne les donnees d'une condition depuis notre base
     */
    private function getConditionData(string $conditionId): ?array
    {
        $database = $this->getSkinConditionsDatabase();
        return $database[$conditionId] ?? null;
    }

    /**
     * Base de donnees complete des conditions cutanees
     */
    private function getSkinConditionsDatabase(): array
    {
        return [
            'acne_vulgaire' => [
                'nom' => 'Acne Vulgaire',
                'type' => 'infection',
                'description' => 'Inflammation des follicules pileux causee par l\'obstruction des pores par le sebum et les cellules mortes.',
                'causes' => [
                    'Production excessive de sebum due aux hormones (androgenes)',
                    'Accumulation de cellules mortes a la surface de la peau',
                    'Proliferation de la bacterie Propionibacterium acnes',
                    'Facteurs genetiques predisposants',
                    'Stress et alimentation riche en sucres raffines',
                    'Utilisation de produits comedogenes obstruant les pores',
                ],
                'remedes' => [
                    'Nettoyer le visage matin et soir avec un gel purifiant doux',
                    'Appliquer un serum anti-imperfections a base d\'acide salicylique',
                    'Utiliser une creme legere non comedogene pour hydrater sans obstruer',
                    'Faire un masque a l\'argile 1 a 2 fois par semaine',
                    'Eviter de toucher ou percer les boutons pour eviter les cicatrices',
                    'Consulter un dermatologue pour les cas severes',
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'xerose' => [
                'nom' => 'Xerose (Peau Seche Excessive)',
                'type' => 'deshydratation',
                'description' => 'Affection caracterisee par une secheresse anormale de la peau due a un manque d\'eau ou de lipides dans la couche cornee.',
                'causes' => [
                    'Barriere cutanee affaiblie manquant de lipides naturels',
                    'Exposition au froid, au vent et au soleil intense',
                    'Bains trop chauds et utilisation de savons agressifs',
                    'Predisposition genetique (ichtyose)',
                    'Certaines maladies (hypothyroidie, diabete)',
                    'Vieillissement naturel reduisant la production de sebum',
                ],
                'remedes' => [
                    'Utiliser un nettoyant doux sans savon (syndet)',
                    'Appliquer une creme riche en ceramides et acides gras essentiels',
                    'Boire au moins 1,5L d\'eau par jour',
                    'Eviter les douches trop chaudes et prolongees',
                    'Utiliser un humidificateur d\'air dans les pieces climatisees',
                    'Appliquer une huile vegetale le soir pour renforcer la barriere cutanee',
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'dermatite_seborrheique' => [
                'nom' => 'Dermite Seborrheique',
                'type' => 'inflammation',
                'description' => 'Affection inflammatoire chronique touchant les zones riches en glandes sebacees (cuir chevelu, visage, torse).',
                'causes' => [
                    'Proliferation d\'un champignon (Malassezia) sur les zones grasses',
                    'Production excessive de sebum creant un environnement favorable',
                    'Facteurs genetiques et hormonaux',
                    'Stress et fatigue affaiblissant le systeme immunitaire',
                    'Climat froid et sec aggravant les poussees',
                ],
                'remedes' => [
                    'Utiliser un nettoyant doux specifique anti-champignon',
                    'Appliquer des soins a base de zinc pyrithione ou de ketoconazole',
                    'Eviter les produits alcoolises qui irritent la peau',
                    'Gerer le stress par des techniques de relaxation',
                    'Hydrater legerement avec une creme non grasse',
                    'Consulter un dermatologue pour un traitement adapte',
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'hyperpigmentation' => [
                'nom' => 'Hyperpigmentation (Taches Brunes)',
                'type' => 'pigmentation',
                'description' => 'Coloration localisee plus foncee de la peau due a une production excessive de melanine.',
                'causes' => [
                    'Exposition solaire sans protection accumulee',
                    'Sequelles d\'acne et cicatrices inflammatoires',
                    'Changements hormonaux (grossesse, menopause, pilule)',
                    'Certains medicaments photosensibilisants',
                    'Frottements repetes et irritations cutanees',
                    'Predisposition genetique (melasma)',
                ],
                'remedes' => [
                    'Appliquer un serum a la Vitamine C chaque matin pour unifier le teint',
                    'Utiliser une protection solaire SPF 50+ quotidiennement (indispensable !)',
                    'Appliquer un soin eclaircissant a base d\'acide kojique ou de niacinamide',
                    'Exfolier doucement la peau 1 fois par semaine avec des AHA',
                    'Eviter l\'exposition solaire aux heures chaudes (10h-16h)',
                    'La patience est cle : le traitement des taches prend 2 a 6 mois',
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'couperose' => [
                'nom' => 'Couperose (Telangiectasies)',
                'type' => 'vasculaire',
                'description' => 'Dilatation permanente des petits vaisseaux sanguins du visage creant des rougeurs visibles, surtout sur les joues et le nez.',
                'causes' => [
                    'Fragilite vasculaire hereditaire',
                    'Exposition aux ecarts de temperature (chaud/froid)',
                    'Consommation d\'alcool et d\'epices fortes',
                    'Exposition solaire intense sans protection',
                    'Grossesse et changements hormonaux',
                    'Hypertension arterielle et efforts physiques intenses',
                ],
                'remedes' => [
                    'Utiliser des soins doux specifiques pour peaux sensibles',
                    'Appliquer des soins anti-rougeurs a base de vitamine C et d\'extraits de marron d\'Inde',
                    'Eviter les gommages agressifs et l\'eau tres chaude',
                    'Proteger la peau du soleil avec un SPF 50+',
                    'Consulter un dermatologue pour les traitements laser',
                    'Eviter les aliments vasodilatateurs (alcool, cafeine, plats epices)',
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'rosacee' => [
                'nom' => 'Rosacee',
                'type' => 'inflammation',
                'description' => 'Affection cutanee chronique du visage causant rougeurs, vaisseaux visibles et parfois boutons ressemblant a l\'acne.',
                'causes' => [
                    'Predisposition genetique (peaux claires surtout)',
                    'Dysfonctionnement du systeme immunitaire cutane',
                    'Presence excessive d\'acariens (Demodex) dans les follicules',
                    'Facteurs declencheurs : soleil, stress, alcool, epices',
                    'Perturbation de la barriere cutanee',
                ],
                'remedes' => [
                    'Nettoyage doux avec des produits sans savon ni alccol',
                    'Appliquer des soins anti-inflammatoires specifiques',
                    'Eviter les facteurs declencheurs identifies',
                    'Protection solaire imperative SPF 50+',
                    'Consulter un dermatologue pour un traitement medical',
                    'Utiliser un maquillage vert pour neutraliser les rougeurs',
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'teint_terne' => [
                'nom' => 'Teint Terne et Fatigue',
                'type' => 'deshydratation',
                'description' => 'Manque d\'eclat et de luminosite de la peau du a une accumulation de cellules mortes et une mauvaise circulation.',
                'causes' => [
                    'Accumulation de cellules mortes a la surface de la peau',
                    'Manque d\'hydratation cutanee',
                    'Fatigue et manque de sommeil',
                    'Alimentation desequilibree pauvre en antioxydants',
                    'Tabac et alcool reduisant l\'oxygenation de la peau',
                    'Pollution et radicaux libres oxydant les cellules',
                ],
                'remedes' => [
                    'Exfolier la peau 1 a 2 fois par semaine avec un gommage doux',
                    'Appliquer un serum a la Vitamine C chaque matin pour booster l\'eclat',
                    'Hydrater la peau matin et soir avec une creme adaptee',
                    'Boire au moins 1,5L d\'eau par jour',
                    'Dormir suffisamment (7-8h) pour permettre la regeneration',
                    'Consommer des aliments riches en antioxydants (fruits, legumes verts)',
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'vieillissement_cutane' => [
                'nom' => 'Vieillissement Cutane (Rides)',
                'type' => 'vieillissement',
                'description' => 'Apparition progressive des rides, ridules et perte de fermete due au vieillissement naturel et aux facteurs environnementaux.',
                'causes' => [
                    'Vieillissement chronologique naturel (perte de collagene des 25 ans)',
                    'Exposition solaire excessive sans protection (photo-vieillissement)',
                    'Tabac : reduit l\'oxygenation et detruit le collagene',
                    'Stress oxydatif des radicaux libres',
                    'Repetition des expressions faciales (rides d\'expression)',
                    'Alimentation desequilibree pauvre en antioxydants',
                ],
                'remedes' => [
                    'Appliquer un serum anti-age a base de retinol le soir',
                    'Utiliser une creme hydratante enrichie en acide hyaluronique',
                    'Protection solaire SPF 50+ quotidienne (anti-age #1)',
                    'Massages du visage pour stimuler la microcirculation',
                    'Consommer des aliments riches en omega-3 et antioxydants',
                    'Dormir suffisamment pour permettre la regeneration cellulaire',
                ],
                'urgence' => false,
                'previent_soleil' => true,
            ],
            'deshydratation' => [
                'nom' => 'Deshydratation Cutanee',
                'type' => 'deshydratation',
                'description' => 'Manque d\'eau dans la couche superficielle de la peau, pouvant toucher tous les types de peau, meme les peaux grasses.',
                'causes' => [
                    'Consommation insuffisante d\'eau',
                    'Climat sec, ventilation ou climatisation excessive',
                    'Utilisation de produits trop agressifs qui decapent la barriere cutanee',
                    'Consommation excessive de cafeine et d\'alcool (diuretiques)',
                    'Certains medicaments (diuretiques, retinoide)',
                ],
                'remedes' => [
                    'Boire au moins 1,5 a 2L d\'eau par jour',
                    'Utiliser un serum a l\'acide hyaluronique pour capter l\'eau',
                    'Appliquer une creme hydratante adaptee matin et soir',
                    'Eviter les nettoyants trop agressifs',
                    'Utiliser un brumisateur d\'eau thermale dans la journee',
                    'Humidifier votre environnement de travail',
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
            'hyperseborrhee' => [
                'nom' => 'Hyperseborrhee (Peau Grasse)',
                'type' => 'sebum',
                'description' => 'Production excessive de sebum par les glandes sebacees, donnant un aspect brillant et gras a la peau, surtout sur la zone T.',
                'causes' => [
                    'Stimulation hormonale par les androgenes',
                    'Predisposition genetique',
                    'Alimentation riche en sucres et graisses saturees',
                    'Stress augmentant la production de cortisol',
                    'Utilisation de produits trop decapants qui stimulent la production de sebum en reaction',
                ],
                'remedes' => [
                    'Nettoyer le visage matin et soir avec un gel purifiant doux',
                    'Utiliser une creme legere matifiante non comedogene',
                    'Appliquer un masque a l\'argile 2 fois par semaine',
                    'Eviter les produits alcoolises qui stimulent la production de sebum',
                    'Utiliser des papiers matifiants dans la journee',
                ],
                'urgence' => false,
                'previent_soleil' => false,
            ],
        ];
    }
}
