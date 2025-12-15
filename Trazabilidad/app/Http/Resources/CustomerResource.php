<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'customer_id' => $this->cliente_id,
            'business_name' => $this->razon_social,
            'trading_name' => $this->nombre_comercial,
            'nit' => $this->nit,
            'address' => $this->direccion,
            'phone' => $this->telefono,
            'email' => $this->email,
            'contact' => $this->contacto,
            'active' => $this->activo,
        ];
    }
}