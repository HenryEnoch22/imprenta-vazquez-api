<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrintJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'type_receipt_id' => 'required|exists:type_receipts,id',
            'name' => 'required|string|max:255',
            'file_path' => 'required|file|max:10240|mimes:pdf',
            'description' => 'nullable|string',

            // Condicionales
            'folio' => 'required_if:type_receipt_id,1|nullable|string|max:100',
            'copies_number' => 'required_if:type_receipt_id,1|nullable|integer|min:1',
            'copies_colors' => 'required_if:type_receipt_id,1|nullable|array',
            'copies_colors.*' => 'integer',

            'tint_colors' => 'required|array',
            'tint_colors.*' => 'integer',
            'paper_size' => 'required|integer',
            'paper_type' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'El campo cliente es obligatorio.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'type_receipt_id.exists' => 'El tipo de comprobante seleccionado no existe.',
            'type_receipt_id.required' => 'El campo tipo de comprobante es obligatorio.',
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no debe superar los 255 caracteres.',
            'file_path.required' => 'El archivo es obligatorio.',
            'file_path.file' => 'El archivo debe ser un archivo válido.',
            'file_path.max' => 'El archivo no debe superar los 10 MB.',
            'file_path.mimes' => 'El archivo debe ser un PDF.',
            'folio.required_if' => 'El campo folio es obligatorio cuando el tipo de recibo es Impresión.',
            'paper_size.required' => 'El campo tamaño de papel es obligatorio.',
            'copies_number.required_if' => 'El campo número de copias es obligatorio cuando el tipo de recibo es Impresión.',
            'copies_colors.required_if' => 'El campo colores de copias es obligatorio cuando el tipo de recibo es Impresión.',
            'paper_type.required' => 'El campo tipo de papel es obligatorio.',
            'quantity.required' => 'El campo cantidad es obligatorio.',
            'tint_colors.required' => 'El campo colores de tinta es obligatorio.',
        ];
    }
}
