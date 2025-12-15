<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionBatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Determine status based on final evaluation, then time
        $status = 'pending';
        
        $evaluation = $this->finalEvaluation->first();
        
        if ($evaluation) {
            $reason = strtolower($evaluation->razon ?? '');
            if (str_contains($reason, 'falló')) {
                $status = 'failed';
            } else {
                $status = 'completed';
            }
        } elseif ($this->hora_inicio && !$this->hora_fin) {
            $status = 'in_progress';
        } elseif ($this->hora_fin) {
            $status = 'completed';
        }
        
        \Illuminate\Support\Facades\Log::info("Batch {$this->lote_id} status calc: Eval=" . ($evaluation ? 'Yes' : 'No') . ", Start={$this->hora_inicio}, End={$this->hora_fin}, Status={$status}");

        return [
            'batch_id' => $this->lote_id,
            'order_id' => $this->pedido_id,
            'batch_code' => $this->codigo_lote,
            'name' => $this->nombre,
            // Fields expected by BatchesListScreen
            'product_name' => $this->nombre,
            'quantity' => $this->cantidad_objetivo,
            'status' => $status,
            'start_date' => $this->fecha_creacion,
            'operator_name' => $this->processMachineRecords->first()?->operator?->nombre ?? 'Sin asignar',
            // Original fields
            'creation_date' => $this->fecha_creacion,
            'start_time' => $this->hora_inicio,
            'end_time' => $this->hora_fin,
            'target_quantity' => $this->cantidad_objetivo,
            'produced_quantity' => $this->cantidad_producida,
            'observations' => $this->observaciones,
            'order' => new CustomerOrderResource($this->whenLoaded('order')),
            'raw_materials' => BatchRawMaterialResource::collection($this->whenLoaded('rawMaterials')),
            'final_evaluation' => $this->whenLoaded('finalEvaluation', function() {
                return $this->finalEvaluation->first();
            }),
        ];
    }
}
