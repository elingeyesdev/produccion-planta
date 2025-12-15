<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->producto_id,
            'code' => $this->codigo,
            'name' => $this->nombre,
            'type' => $this->tipo,
            'weight' => $this->peso,
            'unit_price' => $this->precio_unitario,
            'description' => $this->descripcion,
            'active' => $this->activo,
            'unit' => new UnitOfMeasureResource($this->whenLoaded('unit')),
        ];
    }
}
