<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->pedido_id,
            'customer_id' => $this->cliente_id,
            'order_number' => $this->numero_pedido,
            'creation_date' => $this->fecha_creacion,
            'delivery_date' => $this->fecha_entrega,
            'description' => $this->descripcion,
            'observations' => $this->observaciones,
            'status' => $this->estado,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'batches' => ProductionBatchResource::collection($this->whenLoaded('batches')),
            'order_products' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            'total_price' => $this->orderProducts->sum(function ($product) {
                return $product->cantidad * $product->precio;
            }),
        ];
    }
}

