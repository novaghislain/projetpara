<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ConsultationController extends Controller
{
    public function index()
    {
        return view('consultation');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blood_group' => 'required|string',
            'health_info' => 'nullable|array',
            'area' => 'required|string',
            'photo' => 'required|image|max:10240', // Max 10MB
        ]);

        $path = $request->file('photo')->store('consultations', 'public');

        // Simulate AI Analysis
        $results = $this->simulateAIAnalysis($validated['area']);

        $consultation = Consultation::create([
            'user_id' => auth()->id(),
            'blood_group' => $validated['blood_group'],
            'health_info' => $validated['health_info'],
            'area' => $validated['area'],
            'photo_path' => $path,
            'results' => $results,
            'status' => 'completed'
        ]);

        return response()->json($consultation);
    }

    private function simulateAIAnalysis($area)
    {
        // Mocking some professional-looking results
        $analysis = [
            'condition' => 'Analyse en cours...',
            'recommendations' => [],
            'severity' => 'Normal'
        ];

        switch (strtolower($area)) {
            case 'visage':
                $analysis = [
                    'condition' => 'Hyperpigmentation légère et pores dilatés',
                    'recommendations' => [
                        'Utiliser un sérum à la Vitamine C le matin',
                        'Appliquer une protection solaire SPF 50+',
                        'Nettoyage doux bi-quotidien'
                    ],
                    'severity' => 'Léger'
                ];
                break;
            case 'dos':
                $analysis = [
                    'condition' => 'Acné rétentionnelle localisée',
                    'recommendations' => [
                        'Utiliser un gel lavant purifiant',
                        'Éviter les frottements excessifs',
                        'Hydratation non-comédogène'
                    ],
                    'severity' => 'Modéré'
                ];
                break;
            default:
                $analysis = [
                    'condition' => 'Grain de peau irrégulier',
                    'recommendations' => [
                        'Hydratation intense nocturne',
                        'Exfoliation enzymatique hebdomadaire'
                    ],
                    'severity' => 'Normal'
                ];
        }

        return $analysis;
    }
}
