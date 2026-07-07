<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EntryLineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'line_number' => $this->line_number,
            'account_id' => $this->account_id,
            'account_code' => $this->account_code,
            'account_label' => $this->account_label,
            'description' => $this->description,
            'debit' => (float) $this->debit,
            'credit' => (float) $this->credit,
            'partner_id' => $this->partner_id,
            'partner_type' => $this->partner_type,
            'vat_code' => $this->vat_code,
            'vat_base' => (float) $this->vat_base,
            'vat_amount' => (float) $this->vat_amount,
        ];
    }
}
