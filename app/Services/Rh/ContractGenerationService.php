<?php

namespace App\Services\Rh;

use App\Models\Rh\EmploymentContract;
use Carbon\Carbon;

class ContractGenerationService
{
    /**
     * Génère le HTML du contrat basé sur un template simple
     */
    public function generateHtml(EmploymentContract $contract)
    {
        $dateStr = Carbon::parse($contract->start_date)->translatedFormat('d F Y');
        
        $html = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px;'>
            <h1 style='text-align: center;'>CONTRAT DE TRAVAIL ({$contract->contract_type})</h1>
            
            <p>Entre les soussignés :</p>
            <p>L'entreprise, d'une part,</p>
            <p>Et Monsieur/Madame <strong>{$contract->employee_name}</strong>, d'autre part.</p>
            
            <h3>Article 1 : Engagement</h3>
            <p>Le salarié est engagé en qualité de <strong>{$contract->position}</strong> à compter du <strong>{$dateStr}</strong>.</p>
            
            <h3>Article 2 : Rémunération</h3>
            <p>En contrepartie de ses services, le salarié percevra une rémunération brute mensuelle de <strong>" . number_format($contract->gross_salary, 0, ',', ' ') . " FCFA</strong>.</p>
            
            <h3>Article 3 : Droit applicable</h3>
            <p>Le présent contrat est régi par les dispositions du Code du Travail en vigueur en République du Bénin.</p>
            
            <br><br>
            <div style='display: flex; justify-content: space-between;'>
                <div>
                    <p>Signature de l'Employeur</p>
                </div>
                <div>
                    <p>Signature du Salarié (précédée de la mention 'Lu et approuvé')</p>
                    <div id='employee-signature-zone'>
                        " . ($contract->signature_path ? "<img src='{$contract->signature_path}' alt='Signature' style='max-height: 100px;'>" : "<br><br><br><br>") . "
                    </div>
                </div>
            </div>
        </div>
        ";

        $contract->content_html = $html;
        $contract->save();

        return $html;
    }
}
