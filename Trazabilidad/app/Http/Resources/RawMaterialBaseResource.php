<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawMaterialBaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'material_id' => $this->material_id,
            'category_id' => $this->categoria_id,
            'unit_id' => $this->unidad_id,
            'code' => $this->codigo,
            'name' => $this->nombre,
            'description' => $this->descripcion,
            'available_quantity' => $this->cantidad_disponible,
            'minimum_stock' => $this->stock_minimo,
            'maximum_stock' => $this->stock_maximo,
            'active' => $this->activo,
            'category' => new RawMaterialCategoryResource($this->whenLoaded('category')),
            'unit' => new UnitOfMeasureResource($this->whenLoaded('unit')),
        ];
    }
}

