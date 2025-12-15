<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawMaterialCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this->categoria_id,
            'code' => $this->codigo,
            'name' => $this->nombre,
            'description' => $this->descripcion,
            'active' => $this->activo,
        ];
    }
}