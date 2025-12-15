<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialMovementLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'log_id' => $this->log_id,
            'material_id' => $this->material_id,
            'movement_type_id' => $this->movement_type_id,
            'user_id' => $this->user_id,
            'quantity' => $this->quantity,
            'previous_balance' => $this->previous_balance,
            'new_balance' => $this->new_balance,
            'description' => $this->description,
            'movement_date' => $this->movement_date,
            'material' => $this->whenLoaded('material'),
            'movement_type' => $this->whenLoaded('movementType'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}

