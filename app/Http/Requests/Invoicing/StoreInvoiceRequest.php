<?php

namespace App\Http\Requests\Invoicing;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:customer_invoice,supplier_invoice,credit_note,debit_note',
            'partner_id' => 'required|integer|exists:partners,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'delivery_date' => 'nullable|date',
            'payment_term' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
            'terms_conditions' => 'nullable|string|max:5000',
            'related_invoice_id' => 'nullable|integer|exists:invoices,id',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:500',
            'lines.*.product_code' => 'nullable|string|max:50',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit' => 'nullable|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.vat_code' => 'nullable|string|max:10',
            'lines.*.vat_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'La facture doit contenir au moins une ligne.',
            'lines.min' => 'La facture doit contenir au moins une ligne.',
            'lines.*.description.required' => 'Chaque ligne doit avoir une description.',
            'lines.*.unit_price.required' => 'Chaque ligne doit avoir un prix unitaire.',
        ];
    }
}
