<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessMachineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'process_machine_id' => $this->proceso_maquina_id,
            'process_id' => $this->proceso_id,
            'machine_id' => $this->maquina_id,
            'step_order' => $this->orden_paso,
            'name' => $this->nombre,
            'description' => $this->descripcion,
            'estimated_time' => $this->tiempo_estimado,
            'machine' => $this->whenLoaded('machine', function () {
                return [
                    'machine_id' => $this->machine->maquina_id,
                    'code' => $this->machine->codigo,
                    'name' => $this->machine->nombre,
                    'description' => $this->machine->descripcion,
                    'image_url' => $this->machine->imagen_url,
                    'active' => $this->machine->activo,
                ];
            }),
        ];
    }
}