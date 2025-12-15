<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_product_id' => $this->producto_pedido_id,
            'product_id' => $this->producto_id,
            'quantity' => $this->cantidad,
            'price' => $this->precio,
            'status' => $this->estado,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
