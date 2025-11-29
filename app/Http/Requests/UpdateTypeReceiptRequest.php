<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTypeReceiptRequest extends FormRequest
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
            'type_receipt_category_id' => 'required|integer|min:0|max:'.(count(\App\Models\TypeReceipt::$categories) - 1),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type_receipt_category_id.required' => 'El campo categoría es obligatorio.',
            'type_receipt_category_id.integer' => 'El campo categoría debe ser un número entero.',
            'type_receipt_category_id.min' => 'El campo categoría no puede ser menor que 0.',
            'type_receipt_category_id.max' => 'El campo categoría no puede ser mayor que ' . (count(\App\Models\TypeReceipt::$categories) - 1) . '.',
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no puede tener más de 255 caracteres.',
            'description.string' => 'El campo descripción debe ser una cadena de texto.',
        ];
    }
}
