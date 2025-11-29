<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrintJobRequest extends FormRequest
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
            'customer_id' => 'nullable|exists:customers,id',
            'type_receipt_id' => 'nullable|exists:type_receipts,id',
            'name' => 'nullable|string|max:255',

            'file_path' => 'nullable|file|max:10240|mimes:pdf',
            'description' => 'nullable|string',

            // Condicionales
            'folio' => 'required_if:type_receipt_id,1|nullable|string|max:100',
            'copies_number' => 'required_if:type_receipt_id,1|nullable|integer|min:1',
            'copies_colors' => 'required_if:type_receipt_id,1|nullable|array',
            'copies_colors.*' => 'integer',

            'tint_colors' => 'nullable|array',
            'tint_colors.*' => 'string',

            'paper_size' => 'nullable|integer',
            'paper_type' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
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
            'customer_id.required' => 'El campo cliente es obligatorio.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'type_receipt_id.exists' => 'El tipo de comprobante seleccionado no existe.',
            'type_receipt_id.required' => 'El campo tipo de comprobante es obligatorio.',
            'name.required' => 'El campo nombre es obligatorio.',
        ];
    }
}
