<?php

namespace App\Http\Requests\Api\ChartAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChartAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_code' => ['required', 'string', 'max:20', Rule::unique('accounting_accounts', 'code')],
            'account_class' => ['required', 'string', 'in:1,2,3,4,5,6,7,8,9'],
            'account_type' => ['required', 'string', 'in:asset,liability,equity,revenue,expense,contra_asset,contra_liability,special,analytical'],
            'account_nature' => ['nullable', 'string', 'in:debitor,creditor,bilateral'],
            'label_fr' => ['required', 'string', 'min:2', 'max:255'],
            'label_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_summary' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'has_vat' => ['nullable', 'boolean'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'allow_journal_entry' => ['nullable', 'boolean'],
            'reconciliable' => ['nullable', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:accounting_accounts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_code.unique' => 'Ce code de compte existe déjà.',
            'account_code.required' => 'Le code du compte est requis.',
            'label_fr.required' => 'Le libellé français est requis.',
            'parent_id.exists' => 'Le compte parent sélectionné n\'existe pas.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_summary' => $this->boolean('is_summary'),
            'is_active' => $this->boolean('is_active', true),
            'has_vat' => $this->boolean('has_vat'),
            'allow_journal_entry' => $this->boolean('allow_journal_entry', true),
            'reconciliable' => $this->boolean('reconciliable'),
        ]);
    }
}
