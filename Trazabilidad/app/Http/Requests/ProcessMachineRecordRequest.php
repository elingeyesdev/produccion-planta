<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessMachineRecordRequest extends FormRequest
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
            'batch_id' => 'required|integer|exists:production_batch,batch_id',
            'process_machine_id' => 'required|integer|exists:process_machine,process_machine_id',
            'entered_variables' => 'required|array',
            'observations' => 'nullable|string|max:500',
        ];
    }
}