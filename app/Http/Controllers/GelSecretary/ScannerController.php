<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ScannerController extends Controller
{
    /**
     * Lance le scan via PowerShell (WIA)
     */
    public function scan(Request $request)
    {
        try {
            // S'assurer que le dossier existe
            if (!Storage::disk('public')->exists('scans')) {
                Storage::disk('public')->makeDirectory('scans');
            }

            $filename = 'scan_' . time() . '.jpg';
            $absolutePath = storage_path('app/public/scans/' . $filename);
            
            // Chemin absolu vers le script PowerShell
            $scriptPath = storage_path('app/scripts/scan.ps1');
            
            // Construire la commande pour exÃ©cuter PowerShell
            $command = 'powershell.exe -ExecutionPolicy Bypass -NoProfile -NonInteractive -File "' . $scriptPath . '" -OutputPath "' . $absolutePath . '" 2>&1';
            
            Log::info("Lancement du scanner : " . $command);
            
            // ExÃ©cuter la commande (bloquant)
            $output = shell_exec($command);
            
            Log::info("Sortie du scanner : " . $output);

            if (strpos($output, 'SUCCESS') !== false && file_exists($absolutePath)) {
                $url = asset('storage/scans/' . $filename);
                return response()->json([
                    'success' => true,
                    'message' => 'NumÃ©risation rÃ©ussie !',
                    'file_path' => 'scans/' . $filename,
                    'url' => $url
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "La numÃ©risation a Ã©chouÃ©. VÃ©rifiez que le scanner est allumÃ© et connectÃ©.",
                    'output' => $output
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error("Erreur ScannerController : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
