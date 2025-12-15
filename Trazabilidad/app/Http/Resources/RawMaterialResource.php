<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawMaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'raw_material_id' => $this->materia_prima_id,
            'material_id' => $this->material_id,
            'supplier_id' => $this->proveedor_id,
            'supplier_batch' => $this->lote_proveedor,
            'invoice_number' => $this->numero_factura,
            'receipt_date' => $this->fecha_recepcion,
            'expiration_date' => $this->fecha_vencimiento,
            'quantity' => $this->cantidad,
            'available_quantity' => $this->cantidad_disponible,
            'receipt_conformity' => $this->conformidad_recepcion,
            'observations' => $this->observaciones,
            'material_base' => new RawMaterialBaseResource($this->whenLoaded('materialBase')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
        ];
    }
}

