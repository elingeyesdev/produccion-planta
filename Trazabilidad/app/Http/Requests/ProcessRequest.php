<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'active' => 'sometimes|boolean',
            'process_machines' => 'sometimes|array',
            'process_machines.*.machine_id' => 'required|integer|exists:maquina,maquina_id',
            'process_machines.*.step_order' => 'sometimes|integer|min:1',
            'process_machines.*.name' => 'required|string|max:100',
            'process_machines.*.description' => 'nullable|string|max:255',
            'process_machines.*.estimated_time' => 'nullable|integer|min:0',
            'process_machines.*.variables' => 'sometimes|array',
            'process_machines.*.variables.*.standard_variable_id' => 'required|integer|exists:variable_estandar,variable_id',
            'process_machines.*.variables.*.min_value' => 'nullable|numeric',
            'process_machines.*.variables.*.max_value' => 'nullable|numeric',
            'process_machines.*.variables.*.target_value' => 'nullable|numeric',
            'process_machines.*.variables.*.mandatory' => 'sometimes|boolean',
        ];
    }
}