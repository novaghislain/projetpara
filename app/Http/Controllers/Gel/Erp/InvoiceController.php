<?php

namespace App\Http\Controllers\Gel\Erp;

use App\Http\Controllers\Controller;
use App\Models\ErpInvoice;
use App\Models\ErpInvoiceItem;
use App\Models\ErpItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Contrôleur de gestion des factures dans le module ERP.
     * Permet de créer des factures, avoirs, notes de débit et proformas
     * avec leurs lignes de détail associées.
     */
    public function store(Request $request)
    {
        /**
         * Crée une nouvelle facture avec ses lignes de détail.
         *
         * POST /erp/invoices
         *
         * @param Request $request La requête HTTP contenant les données de la facture et ses lignes
         * @return \Illuminate\Http\JsonResponse La réponse JSON avec la facture créée
         */
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string|max:255|unique:erp_invoices,invoice_number',
            'type'           => 'required|string|in:invoice,credit_note,debit_note,proforma',
            'client_id'      => 'nullable|integer|exists:clients,id',
            'client_name'    => 'required_without:client_id|string|max:255',
            'invoice_date'   => 'required|date',
            'due_date'       => 'nullable|date|after_or_equal:invoice_date',
            'total_ht'       => 'required|numeric|min:0',
            'tax_amount'     => 'required|numeric|min:0',
            'total_ttc'      => 'required|numeric|min:0',
            'status'         => 'nullable|string|in:brouillon,finalisee,envoyee,payee,annulee',
            'notes'          => 'nullable|string|max:2000',
            'items'          => 'required|array|min:1',
            'items.*.erp_item_id' => 'nullable|integer|exists:erp_items,id',
            'items.*.designation' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        // Validation des données d'entrée
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            // Opération en transaction pour garantir l'intégrité : facture + lignes
            $result = DB::transaction(function () use ($data, $request) {
                // Attribution de l'utilisateur créateur et statut par défaut
                $data['created_by'] = $data['created_by'] ?? $request->user()?->id;
                $data['status']     = $data['status'] ?? 'brouillon';

                // Création de l'en-tête de la facture
                $invoice = ErpInvoice::create($data);

                // Construction et sauvegarde des lignes de la facture
                $items = [];
                foreach ($data['items'] as $line) {
                    $items[] = new ErpInvoiceItem([
                        'erp_invoice_id' => $invoice->id,
                        'erp_item_id'    => $line['erp_item_id'] ?? null,
                        'designation'    => $line['designation'],
                        'quantity'       => $line['quantity'],
                        'unit_price'     => $line['unit_price'],
                        'total_price'    => $line['total_price'],
                    ]);
                }

                // Sauvegarde groupée des lignes de détail
                $invoice->lineItems()->saveMany($items);

                // Rechargement de la facture avec ses relations
                return $invoice->fresh(['lineItems', 'client']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'data'    => $result,
            ], 201);

        } catch (\Exception $e) {
            // Gestion des erreurs : échec de création de la facture
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
