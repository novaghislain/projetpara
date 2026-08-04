<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Importe un fichier CSV de produits.
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt'
        ]);

        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $path = $request->file('import_file')->getRealPath();
        
        // Lire le fichier CSV (séparateur point-virgule ou virgule)
        $content = file_get_contents($path);
        $lines = explode(PHP_EOL, $content);
        
        $imported = 0;
        $isFirst = true;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Détection du séparateur
            $separator = strpos($line, ';') !== false ? ';' : ',';
            $data = str_getcsv($line, $separator);

            if ($isFirst) {
                $isFirst = false;
                continue; // Ignorer l'entête
            }

            // Mapping simplifié: 0=Nom, 1=SKU, 2=Prix HT, 3=Prix TTC
            $data = array_pad($data, 4, '');
            $name = trim($data[0]);
            $sku = trim($data[1]);
            $price_ht = floatval(trim($data[2]));
            $price_ttc = floatval(trim($data[3]));

            if (!empty($name)) {
                Product::create([
                    'client_id' => $clientId,
                    'name' => $name,
                    'sku' => $sku,
                    'price_ht' => $price_ht,
                    'price_ttc' => $price_ttc,
                    'is_active' => true,
                    'is_bundle' => false,
                ]);
                $imported++;
            }
        }

        return redirect()->back()->with('success', "$imported produits ont été importés avec succès !");
    }
}
