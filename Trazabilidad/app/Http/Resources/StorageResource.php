<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'storage_id' => $this->storage_id,
            'batch_id' => $this->batch_id,
            'location' => $this->location,
            'condition' => $this->condition,
            'quantity' => $this->quantity,
            'observations' => $this->observations,
            'storage_date' => $this->storage_date,
            'retrieval_date' => $this->retrieval_date,
            'batch' => $this->whenLoaded('batch'),
        ];
    }
}

