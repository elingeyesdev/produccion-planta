<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProcessMachineResource;

class ProcessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        
        // Include process machines if loaded
        if ($this->relationLoaded('processMachines')) {
            $data['process_machines'] = ProcessMachineResource::collection($this->processMachines);
        }
        
        return $data;
    }
}