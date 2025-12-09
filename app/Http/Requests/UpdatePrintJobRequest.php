<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrintJobRequest extends FormRequest
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
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'nullable|string|max:255',
            'file_path' => 'nullable|file|max:10240|mimes:pdf',
            'description' => 'nullable|string',

            // Condicionales
            'folio' => 'nullable|string|max:100',
            'copies_number' => 'nullable|integer|min:1',
            'copies_colors' => 'nullable|array',
            'copies_colors.*' => 'integer',

            'tint_colors' => 'nullable|array',
            'tint_colors.*' => 'integer',
            'paper_size' => 'nullable|integer',
            'paper_type' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no debe superar los 255 caracteres.',
            'file_path.file' => 'El archivo debe ser un archivo válido.',
            'file_path.max' => 'El archivo no debe superar los 10 MB.',
            'file_path.mimes' => 'El archivo debe ser un PDF.',
        ];
    }
}
