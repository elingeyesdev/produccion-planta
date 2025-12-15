<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionBatchRequest extends FormRequest
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
        $rules = [
            'order_id' => 'required|integer|exists:pedido_cliente,pedido_id',
            // batch_code se genera automáticamente en el controlador al crear lote,
            // por eso lo dejamos opcional en POST. En update se valida la unicidad.
            'batch_code' => 'nullable|string|max:50|unique:lote_produccion,codigo_lote',
            'name' => 'nullable|string|max:100',
            'target_quantity' => 'nullable|numeric|min:0',
            'produced_quantity' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:500',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // En actualizaciones permitimos que batch_code sea opcional, pero si se
            // proporciona debe ser único (excluyendo el registro actual).
            $rules['batch_code'] = 'nullable|string|max:50|unique:lote_produccion,codigo_lote,' . $this->route('production_batch');
            $rules['order_id'] = 'nullable|integer|exists:pedido_cliente,pedido_id';
        }

        return $rules;
    }
}

