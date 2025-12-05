<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusPrintJobRequest extends FormRequest
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
            'status' => ['required', 'integer', 'in:1,2,3,4,5'],
            'reason_rejection' => ['nullable', 'required_if:status,5', 'string', 'max:1000', 'min:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason_rejection.required_if' => 'El motivo de rechazo es obligatorio cuando el estado es rechazado.',
            'reason_rejection.string' => 'El motivo de rechazo debe ser una cadena de texto.',
            'reason_rejection.max' => 'El motivo de rechazo no debe exceder los 1000 caracteres.',
            'reason_rejection.min' => 'El motivo de rechazo debe tener al menos 10 caracteres.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser uno de los siguientes: \'En proceso\', \'Terminada\', \'Rechazada\'.',
        ];
    }
}
