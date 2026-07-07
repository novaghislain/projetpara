<?php

namespace App\Http\Controllers\Api\Invoicing;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartnerController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Liste paginée des partenaires.
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();

        $query = Partner::where('client_id', $clientId);

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $partners = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($partners);
    }

    /**
     * Détail d'un partenaire.
     */
    public function show(string $id): JsonResponse
    {
        $partner = Partner::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        return response()->json([
            'data' => $partner->load('invoices', 'accountReceivable', 'accountPayable'),
        ]);
    }

    /**
     * Crée un partenaire.
     */
    public function store(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'type' => 'required|string|in:customer,supplier,both',
            'company_name' => 'nullable|string|max:200',
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'rccm' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|size:3',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_term_days' => 'nullable|integer|min:0|max:365',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'iban' => 'nullable|string|max:50',
            'swift' => 'nullable|string|max:20',
        ]);

        // Générer un code automatique
        if (empty($validated['code'])) {
            $prefix = match ($validated['type']) {
                'customer' => 'CLI',
                'supplier' => 'FRN',
                default => 'PAR',
            };
            $lastId = Partner::where('client_id', $clientId)
                ->where('type', $validated['type'])
                ->max('id') ?? 0;
            $validated['code'] = $prefix . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
        }

        $validated['client_id'] = $clientId;

        $partner = DB::transaction(function () use ($validated) {
            return Partner::create($validated);
        });

        return response()->json([
            'data' => $partner,
            'message' => 'Partenaire créé avec succès.',
        ], 201);
    }

    /**
     * Modifie un partenaire.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $partner = Partner::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        $validated = $request->validate([
            'type' => 'sometimes|string|in:customer,supplier,both',
            'company_name' => 'nullable|string|max:200',
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'rccm' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|size:3',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_term_days' => 'nullable|integer|min:0|max:365',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'iban' => 'nullable|string|max:50',
            'swift' => 'nullable|string|max:20',
            'status' => 'sometimes|string|in:active,inactive,blocked',
        ]);

        $partner->update($validated);

        return response()->json([
            'data' => $partner->fresh(),
            'message' => 'Partenaire mis à jour avec succès.',
        ]);
    }

    /**
     * Supprime un partenaire (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        $partner = Partner::where('id', $id)
            ->where('client_id', $this->getClientId())
            ->firstOrFail();

        if ($partner->invoices()->exists()) {
            throw ValidationException::withMessages([
                'partner' => 'Impossible de supprimer un partenaire qui a des factures.',
            ]);
        }

        $partner->delete();

        return response()->json([
            'message' => 'Partenaire supprimé avec succès.',
        ]);
    }
}
