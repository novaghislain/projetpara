<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Vérifié via middleware
    }

    protected function getClientId(): int
    {
        return (int) ($this->user()->active_client_id ?? $this->user()->client_id);
    }

    public function rules(): array
    {
        $clientId = $this->getClientId();

        return [
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('journals')->where('client_id', $clientId),
            ],
            'label' => 'required|string|max:255',
            'type' => [
                'required', 'string',
                Rule::in(array_keys(\App\Models\Journal::TYPES)),
            ],
            'prefix' => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ];
    }
}
