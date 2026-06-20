<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ItAsset;
use App\Models\ItAssetIntervention;
use App\Models\ItTicket;
use App\Models\User;
use FPDF;
use Illuminate\Support\Facades\Storage;

/**
 * Service de génération de rapports d'intervention IT (PDF signable)
 *
 * Utilise setasign/fpdf pour produire un rapport formaté prêt
 * à être signé par le client, conforme aux exigences du CDC §4.16.5.
 */
class ItRapportService
{
    /**
     * Générer un rapport PDF pour une intervention.
     *
     * @param ItAssetIntervention $intervention
     * @param string $outputMode 'inline' | 'download' | 'path'
     * @return mixed
     */
    public function generer(ItAssetIntervention $intervention, string $outputMode = 'inline'): mixed
    {
        $intervention->loadMissing(['asset', 'ticket', 'technician']);

        $client = $intervention->asset?->client ?? $intervention->ticket?->client;
        $technicien = $intervention->technician;
        $ticket = $intervention->ticket;
        $equipement = $intervention->asset;

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // ── En-tête ──
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(22, 58, 94); // #163A5E
        $pdf->Cell(0, 10, 'GEL Cabinet', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, "Cabinet d'expertise comptable, juridique et fiscal", 0, 1, 'L');
        $pdf->Cell(0, 5, 'Cotonou, Benin', 0, 1, 'L');
        $pdf->Ln(4);

        // Filet de séparation
        $pdf->SetDrawColor(255, 121, 0); // #FF7900
        $pdf->SetLineWidth(0.8);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(6);

        // Titre
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->SetTextColor(255, 121, 0);
        $pdf->Cell(0, 12, "Rapport d'Intervention IT", 0, 1, 'C');
        $pdf->Ln(4);

        // Référence
        $ref = 'IT-RPT-' . $intervention->id . '-' . ($intervention->date?->format('Ymd') ?? now()->format('Ymd'));
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 5, "Ref: $ref", 0, 1, 'R');
        $pdf->Ln(6);

        // ── Informations client ──
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(22, 58, 94);
        $pdf->Cell(0, 8, 'Client', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(80, 6, 'Client : ' . ($client?->name ?? $client?->entreprise ?? 'N/A'), 0, 1, 'L');
        $pdf->Cell(80, 6, 'IFU : ' . ($client?->ifu ?? 'N/A'), 0, 1, 'L');
        $pdf->Cell(80, 6, 'Contact : ' . ($client?->telephone ?? 'N/A'), 0, 1, 'L');
        $pdf->Ln(4);

        // ── Informations intervention ──
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(22, 58, 94);
        $pdf->Cell(0, 8, "Details de l'intervention", 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(40, 40, 40);

        $cols = [
            ['Technicien', $technicien?->name ?? 'N/A'],
            ['Date', $intervention->date?->format('d/m/Y') ?? 'N/A'],
            ['Duree', $intervention->duration_minutes ? $intervention->duration_minutes . ' min' : 'N/A'],
            ['Type', $intervention->type ?? 'N/A'],
            ['Cout', $intervention->cost ? number_format($intervention->cost, 0, ',', ' ') . ' FCFA' : 'N/A'],
        ];
        foreach ($cols as [$label, $value]) {
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(40, 6, $label . ' :', 0, 0, 'L');
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->Cell(0, 6, $value, 0, 1, 'L');
        }
        $pdf->Ln(4);

        // ── Équipement(s) ──
        if ($equipement) {
            $pdf->SetFont('Helvetica', 'B', 11);
            $pdf->SetTextColor(22, 58, 94);
            $pdf->Cell(0, 8, 'Equipement concerne', 0, 1, 'L');
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetTextColor(40, 40, 40);
            $pdf->Cell(80, 6, 'Nom : ' . ($equipement->name ?? 'N/A'), 0, 1, 'L');
            $pdf->Cell(80, 6, 'Categorie : ' . ($equipement->category ?? 'N/A'), 0, 1, 'L');
            $pdf->Cell(80, 6, 'Marque/Modèle : ' . ($equipement->brand ?? '') . ' ' . ($equipement->model ?? ''), 0, 1, 'L');
            $pdf->Cell(80, 6, 'N/S : ' . ($equipement->serial_number ?? 'N/A'), 0, 1, 'L');
            $pdf->Ln(4);
        }

        // ── Ticket associé ──
        if ($ticket) {
            $pdf->SetFont('Helvetica', 'B', 11);
            $pdf->SetTextColor(22, 58, 94);
            $pdf->Cell(0, 8, 'Ticket associe', 0, 1, 'L');
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetTextColor(40, 40, 40);
            $pdf->Cell(80, 6, 'N : ' . ($ticket->ticket_number ?? 'N/A'), 0, 1, 'L');
            $pdf->Cell(80, 6, 'Titre : ' . ($ticket->title ?? 'N/A'), 0, 1, 'L');
            $pdf->MultiCell(0, 6, 'Description : ' . ($ticket->description ?? 'N/A'));
            $pdf->Ln(4);
        }

        // ── Description des travaux ──
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(22, 58, 94);
        $pdf->Cell(0, 8, 'Travaux effectues', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->MultiCell(0, 6, $intervention->description ?? 'Aucune description fournie.');
        $pdf->Ln(8);

        // ── Signature ──
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.4);
        $pdf->Line(30, $pdf->GetY(), 100, $pdf->GetY()); // ligne signature client
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Signature du client', 0, 1, 'L');
        $pdf->Ln(8);
        $pdf->Line(130, $pdf->GetY(), 200, $pdf->GetY()); // ligne signature technicien
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell(0, 5, 'Signature du technicien', 0, 1, 'L');
        $pdf->Ln(8);

        // ── Pied de page ──
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(160, 160, 160);
        $pdf->Cell(0, 4, 'Document genere le ' . now()->format('d/m/Y à H:i'), 0, 1, 'C');
        $pdf->Cell(0, 4, 'GEL Cabinet — Rapport d\'intervention IT', 0, 1, 'C');

        // ── Sortie ──
        $filename = str_replace('/', '-', $ref) . '.pdf';
        return match ($outputMode) {
            'download' => $pdf->Output('D', $filename),
            'path'     => $this->saveToStorage($pdf, $filename),
            default    => $pdf->Output('I', $filename),
        };
    }

    /**
     * Générer un rapport à partir d'un ticket IT complet,
     * en agrégeant toutes ses interventions.
     */
    public function genererDepuisTicket(ItTicket $ticket, string $outputMode = 'inline'): mixed
    {
        $ticket->loadMissing(['client', 'assignedTo', 'comments', 'interventions']);

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // En-tête
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(22, 58, 94);
        $pdf->Cell(0, 10, 'GEL Cabinet', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, "Cabinet d'expertise comptable, juridique et fiscal", 0, 1, 'L');
        $pdf->Ln(4);

        $pdf->SetDrawColor(255, 121, 0);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(6);

        // Titre
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->SetTextColor(255, 121, 0);
        $pdf->Cell(0, 12, 'Rapport de Cloture — Ticket IT', 0, 1, 'C');
        $pdf->Ln(4);

        // Réf
        $ref = 'TKT-RPT-' . $ticket->ticket_number;
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->Cell(0, 5, "Ref: $ref", 0, 1, 'R');
        $pdf->Ln(6);

        // Client
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(22, 58, 94);
        $pdf->Cell(0, 8, 'Informations', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(80, 6, 'Ticket : ' . ($ticket->ticket_number ?? 'N/A'), 0, 1, 'L');
        $pdf->Cell(80, 6, 'Client : ' . ($ticket->client?->name ?? 'N/A'), 0, 1, 'L');
        $pdf->Cell(80, 6, 'Technicien : ' . ($ticket->assignedTo?->name ?? 'Non assigne'), 0, 1, 'L');
        $pdf->Cell(80, 6, 'Priorite : ' . ucfirst($ticket->priority ?? 'N/A'), 0, 1, 'L');
        $pdf->Cell(80, 6, 'Statut : ' . ucfirst($ticket->status ?? 'N/A'), 0, 1, 'L');
        $pdf->Ln(4);

        // Description
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(22, 58, 94);
        $pdf->Cell(0, 8, 'Description', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->MultiCell(0, 6, $ticket->description ?? 'Aucune description.');
        $pdf->Ln(4);

        // Résolution
        if ($ticket->resolution) {
            $pdf->SetFont('Helvetica', 'B', 11);
            $pdf->SetTextColor(22, 58, 94);
            $pdf->Cell(0, 8, 'Resolution', 0, 1, 'L');
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetTextColor(40, 40, 40);
            $pdf->MultiCell(0, 6, $ticket->resolution);
            $pdf->Ln(4);
        }

        // Temps passé
        $totalMinutes = $ticket->interventions?->sum('duration_minutes') ?? 0;
        $coutTotal = $ticket->interventions?->sum('cost') ?? 0;

        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetTextColor(22, 58, 94);
        $pdf->Cell(0, 8, 'Temps & Facturation', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(80, 6, 'Temps total : ' . $totalMinutes . ' min', 0, 1, 'L');
        $pdf->Cell(80, 6, 'Cout total : ' . number_format($coutTotal, 0, ',', ' ') . ' FCFA', 0, 1, 'L');
        $pdf->Ln(8);

        // Signature
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.4);
        $pdf->Line(30, $pdf->GetY(), 100, $pdf->GetY());
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'Signature du client', 0, 1, 'L');
        $pdf->Ln(8);
        $pdf->Line(130, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Cell(0, 5, 'Signature du technicien', 0, 1, 'L');

        // Footer
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(160, 160, 160);
        $pdf->Cell(0, 4, 'Document genere le ' . now()->format('d/m/Y à H:i'), 0, 1, 'C');
        $pdf->Cell(0, 4, 'GEL Cabinet — Rapport de cloture IT', 0, 1, 'C');

        $filename = str_replace('/', '-', $ref) . '.pdf';
        return match ($outputMode) {
            'download' => $pdf->Output('D', $filename),
            'path'     => $this->saveToStorage($pdf, $filename),
            default    => $pdf->Output('I', $filename),
        };
    }

    /**
     * Sauvegarder le PDF dans le storage.
     */
    private function saveToStorage(FPDF $pdf, string $filename): string
    {
        $path = 'rapports/it/' . $filename;
        Storage::disk('public')->put($path, $pdf->Output('S'));
        return $path;
    }
}
