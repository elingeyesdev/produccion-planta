<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'supplier_id' => $this->proveedor_id,
            'business_name' => $this->razon_social,
            'trading_name' => $this->nombre_comercial,
            'nit' => $this->nit,
            'contact' => $this->contacto,
            'phone' => $this->telefono,
            'email' => $this->email,
            'address' => $this->direccion,
            'active' => $this->activo,
        ];
    }
}