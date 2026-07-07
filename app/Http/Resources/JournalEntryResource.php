<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entry_number' => $this->entry_number,
            'entry_date' => $this->entry_date->format('Y-m-d'),
            'value_date' => $this->value_date?->format('Y-m-d'),
            'reference' => $this->reference,
            'description' => $this->description,
            'total_debit' => (float) $this->total_debit,
            'total_credit' => (float) $this->total_credit,
            'is_balanced' => $this->is_balanced,
            'status' => $this->status,
            'journal' => JournalResource::make($this->whenLoaded('journal')),
            'lines' => EntryLineResource::collection($this->whenLoaded('lines')),
            'created_by' => $this->creator?->name,
            'validated_by' => $this->validator?->name,
            'validated_at' => $this->validated_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
