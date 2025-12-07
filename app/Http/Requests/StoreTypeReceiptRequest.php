<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTypeReceiptRequest extends FormRequest
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
            'receipt_category' => 'required|in:'. implode(',', \App\Models\TypeReceipt::$categories),
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
            'receipt_category.required' => 'El campo categoría es obligatorio.',
            'receipt_category.in' => 'El campo categoría debe ser uno de los siguientes valores: ' . implode(', ', \App\Models\TypeReceipt::$categories) . '.',
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser una cadena de texto.',
            'name.max' => 'El campo nombre no puede tener más de 255 caracteres.',
            'description.string' => 'El campo descripción debe ser una cadena de texto.',
        ];
    }
}
