<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchRawMaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'batch_material_id' => $this->batch_material_id,
            'batch_id' => $this->batch_id,
            'raw_material_id' => $this->raw_material_id,
            'planned_quantity' => $this->planned_quantity,
            'used_quantity' => $this->used_quantity,
            'raw_material' => $this->whenLoaded('rawMaterial'),
        ];
    }
}
