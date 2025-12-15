<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitOfMeasureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'unit_id' => $this->unidad_id,
            'code' => $this->codigo,
            'name' => $this->nombre,
            'abbreviation' => $this->codigo, // Add abbreviation alias
            'description' => $this->descripcion,
            'active' => $this->activo,
        ];
    }
}

