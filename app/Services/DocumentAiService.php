<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentAiService
{
    /**
     * Analyse un document via IA (simulation OCR/ICR)
     * et retourne les métadonnées extraites (montant, date, tags, catégorie suggérée).
     */
    public function analyzeDocument(UploadedFile $file): array
    {
        // Dans une vraie implémentation, on appellerait une API d'OCR ou Google Document AI.
        // Ici, on simule l'extraction basée sur des mots-clés dans le nom du fichier
        // ou en générant des valeurs aléatoires plausibles.

        $filename = strtolower($file->getClientOriginalName());
        
        $metadata = [
            'confidence_score' => rand(70, 99),
            'suggested_category' => 'Autre',
            'suggested_folder' => 'Documents divers',
            'extracted_data' => [],
            'suggested_tags' => [],
        ];

        if (Str::contains($filename, ['facture', 'invoice', 'recu', 'receipt'])) {
            $metadata['suggested_category'] = 'Facture';
            $metadata['suggested_folder'] = 'Achats / Fournisseurs';
            $metadata['suggested_tags'] = ['Facture', 'A classer', 'Comptabilité'];
            
            // Simulation d'extraction OCR
            $metadata['extracted_data'] = [
                'date' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                'total_ht' => rand(100, 5000),
                'tva' => 18,
                'total_ttc' => 0, // Calculé après
                'fournisseur' => 'Fournisseur ' . Str::random(5),
            ];
            $metadata['extracted_data']['total_ttc'] = $metadata['extracted_data']['total_ht'] * 1.18;

        } elseif (Str::contains($filename, ['paie', 'salaire', 'bulletin', 'rh'])) {
            $metadata['suggested_category'] = 'Bulletin de Paie';
            $metadata['suggested_folder'] = 'Ressources Humaines';
            $metadata['suggested_tags'] = ['Confidentiel', 'RH', now()->format('Y')];
            
            $metadata['extracted_data'] = [
                'mois' => now()->format('m'),
                'annee' => now()->format('Y'),
                'employe' => 'Employé ' . Str::random(5),
                'net_a_payer' => rand(100000, 500000),
            ];
            
        } elseif (Str::contains($filename, ['kbis', 'rccm', 'statut', 'contrat', 'juridique'])) {
            $metadata['suggested_category'] = 'Document Juridique';
            $metadata['suggested_folder'] = 'Juridique & Fiscal';
            $metadata['suggested_tags'] = ['Juridique', 'Officiel'];
            
            $metadata['extracted_data'] = [
                'date_emission' => now()->subMonths(rand(1, 24))->format('Y-m-d'),
                'type_acte' => Str::contains($filename, 'contrat') ? 'Contrat' : 'Acte statutaire',
            ];
        }

        return $metadata;
    }

    /**
     * Valide et applique les données extraites au modèle Document (pour création).
     */
    public function formatForDocumentModel(array $aiData): array
    {
        $attributes = [
            'category' => $aiData['suggested_category'] ?? null,
            'description' => "Auto-classé via IA (Score: {$aiData['confidence_score']}%)",
            'tags' => $aiData['suggested_tags'] ?? [],
        ];

        // On peut stocker les données brutes extraites dans une colonne JSON si elle existait, 
        // ou utiliser les champs spécifiques du document.
        if (isset($aiData['extracted_data']['date'])) {
            $attributes['document_date'] = $aiData['extracted_data']['date'];
        }

        return $attributes;
    }
}
