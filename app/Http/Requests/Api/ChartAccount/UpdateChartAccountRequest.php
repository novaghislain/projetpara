<?php

namespace App\Http\Requests\Api\ChartAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChartAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $accountId = $this->route('chart_account');

        return [
            'account_code' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('accounting_accounts', 'code')->ignore($accountId, 'id')],
            'account_class' => ['sometimes', 'required', 'string', 'in:1,2,3,4,5,6,7,8,9'],
            'account_type' => ['sometimes', 'required', 'string', 'in:asset,liability,equity,revenue,expense,contra_asset,contra_liability,special,analytical'],
            'account_nature' => ['nullable', 'string', 'in:debitor,creditor,bilateral'],
            'label_fr' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
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
}
