<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChartAccount\StoreChartAccountRequest;
use App\Http\Requests\Api\ChartAccount\UpdateChartAccountRequest;
use App\Models\AccountingAccount;
use App\Models\ChartAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChartAccountController extends Controller
{
    /**
     * Récupère l'identifiant du tenant ou du client selon le contexte.
     */
    private function getContext(Request $request): array
    {
        $user = $request->user();
        if ($user && $user->tenant_id) {
            return ['type' => 'tenant', 'id' => $user->tenant_id];
        }
        $clientId = $request->input('client_id', Auth::user()->active_client_id ?? Auth::user()->client_id ?? 0);
        return ['type' => 'client', 'id' => $clientId];
    }

    /**
     * Retourne le modèle de requête selon le contexte.
     */
    private function baseQuery(Request $request)
    {
        $ctx = $this->getContext($request);
        if ($ctx['type'] === 'tenant') {
            return ChartAccount::where('tenant_id', $ctx['id']);
        }
        return AccountingAccount::where('client_id', $ctx['id']);
    }

    /**
     * Liste paginée des comptes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->baseQuery($request);

        // Filtre par classe
        $classField = $this->getContext($request)['type'] === 'tenant' ? 'class' : 'syscohada_class';
        if ($request->filled('class')) {
            $query->where($classField, $request->class);
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filtre actif/inactif
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $accounts = $query->orderBy('code')
            ->paginate($request->input('per_page', 50));

        return response()->json($accounts);
    }

    /**
     * Arborescence complète des comptes.
     */
    public function tree(Request $request): JsonResponse
    {
        $query = $this->baseQuery($request);
        $accounts = $query->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->orderBy('code');
            }])
            ->orderBy('code')
            ->get();

        return response()->json($accounts);
    }

    /**
     * Détail d'un compte.
     */
    public function show(int $id): JsonResponse
    {
        $ctx = $this->getContext(request());
        if ($ctx['type'] === 'tenant') {
            $account = ChartAccount::with(['tenant'])->findOrFail($id);
        } else {
            $account = AccountingAccount::with(['parent', 'children'])->findOrFail($id);
        }

        return response()->json([
            'account' => $account,
        ]);
    }

    /**
     * Créer un nouveau compte.
     */
    public function store(StoreChartAccountRequest $request): JsonResponse
    {
        $account = DB::transaction(function () use ($request) {
            $data = $this->mapRequestToFields($request);
            $ctx = $this->getContext($request);
            if ($ctx['type'] === 'tenant') {
                return ChartAccount::create(array_merge($data, ['tenant_id' => $ctx['id']]));
            }
            return AccountingAccount::create($data);
        });

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'account' => $account,
        ], 201);
    }

    /**
     * Modifier un compte.
     */
    public function update(UpdateChartAccountRequest $request, int $id): JsonResponse
    {
        $ctx = $this->getContext($request);
        if ($ctx['type'] === 'tenant') {
            $account = ChartAccount::where('tenant_id', $ctx['id'])->findOrFail($id);
        } else {
            $account = AccountingAccount::where('client_id', $ctx['id'])->findOrFail($id);
        }

        DB::transaction(function () use ($request, $account) {
            $data = $this->mapRequestToFields($request);
            $account->update($data);
        });

        return response()->json([
            'message' => 'Compte mis à jour avec succès.',
            'account' => $account->fresh(),
        ]);
    }

    /**
     * Supprimer un compte.
     */
    public function destroy(int $id): JsonResponse
    {
        $ctx = $this->getContext(request());
        if ($ctx['type'] === 'tenant') {
            $account = ChartAccount::findOrFail($id);
        } else {
            $account = AccountingAccount::findOrFail($id);
            if ($account->children()->count() > 0) {
                return response()->json([
                    'message' => 'Impossible de supprimer ce compte : il possède des sous-comptes.',
                ], 409);
            }
            if ($account->journalLines()->count() > 0) {
                return response()->json([
                    'message' => 'Impossible de supprimer ce compte : il a des écritures comptables.',
                ], 409);
            }
        }

        $account->delete();

        return response()->json([
            'message' => 'Compte supprimé avec succès.',
        ]);
    }

    /**
     * Exporter la liste des comptes (format simple).
     */
    public function export(): JsonResponse
    {
        $request = request();
        $ctx = $this->getContext($request);
        if ($ctx['type'] === 'tenant') {
            $accounts = ChartAccount::where('tenant_id', $ctx['id'])
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type', 'class', 'is_active']);
        } else {
            $accounts = AccountingAccount::where('client_id', $ctx['id'])
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type', 'syscohada_class', 'is_active']);
        }

        return response()->json($accounts);
    }

    /**
     * Mapping des champs du request vers les colonnes du modèle.
     */
    protected function mapRequestToFields($request): array
    {
        $ctx = $this->getContext($request);
        $data = array_filter([
            'code' => $request->account_code ?? $request->code,
            'name' => $request->label_fr ?? $request->name,
            'type' => $request->account_type ?? $request->type,
            'is_active' => $request->is_active ?? true,
            'is_syscohada' => $request->is_syscohada ?? false,
            'has_tva' => $request->has_vat ?? false,
            'tva_rate' => $request->vat_rate,
            'description' => $request->description,
        ], fn ($value) => $value !== null);

        if ($ctx['type'] === 'client') {
            $data['client_id'] = $ctx['id'];
            if (isset($data['class'])) {
                $data['syscohada_class'] = $data['class'];
                unset($data['class']);
            }
        } else {
            $data['tenant_id'] = $ctx['id'];
            if ($request->account_class) {
                $data['class'] = $request->account_class;
            }
        }

        return $data;
    }
}
