<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JournalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'type' => $this->type,
            'prefix' => $this->prefix,
            'next_number' => $this->next_number,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'fiscal_year' => FiscalYearResource::make($this->whenLoaded('fiscalYear')),
            'entries_count' => $this->whenCounted('entries'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
