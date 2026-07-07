<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FiscalYearResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'year' => $this->year,
            'label' => $this->label,
            'date_start' => $this->date_start->format('Y-m-d'),
            'date_end' => $this->date_end->format('Y-m-d'),
            'status' => $this->status,
            'is_open' => $this->isOpen(),
            'is_closed' => $this->isClosed(),
        ];
    }
}
