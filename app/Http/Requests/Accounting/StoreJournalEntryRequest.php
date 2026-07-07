<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Vérifié via middleware
    }

    public function rules(): array
    {
        return [
            'journal_id' => 'required|exists:journals,id',
            'entry_date' => 'required|date',
            'value_date' => 'nullable|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounting_accounts,id',
            'lines.*.description' => 'nullable|string|max:500',
            'lines.*.debit' => 'required_without_all:lines.*.credit|numeric|min:0',
            'lines.*.credit' => 'required_without_all:lines.*.debit|numeric|min:0',
            'lines.*.partner_id' => 'nullable|exists:clients,id',
            'lines.*.partner_type' => 'nullable|string|in:customer,supplier,employee,other',
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'Une écriture doit contenir au moins 2 lignes.',
            'lines.min' => 'Une écriture doit contenir au moins 2 lignes (débit et crédit).',
            'lines.*.debit.required_without_all' => 'Chaque ligne doit avoir un montant au débit OU au crédit.',
            'lines.*.credit.required_without_all' => 'Chaque ligne doit avoir un montant au débit OU au crédit.',
        ];
    }
}
