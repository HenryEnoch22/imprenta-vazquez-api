<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrintJobPaymentRequest extends FormRequest
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
            'print_job_request_id' => 'required|exists:print_job_requests,id',
            'payment_method' => 'required|integer|in:1,2,3',
            'payment_amount' => 'required_if:payment_method,1,2|nullable|numeric|min:0',
            'payment_file' => 'required_if:payment_method,1,2|nullable|file|max:10240|mimes:jpg,jpeg,png,pdf',
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.integer' => 'El método de pago debe ser un número entero.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
            'payment_amount.required_if' => 'El monto del pago es obligatorio para el método de pago seleccionado.',
            'payment_amount.numeric' => 'El monto del pago debe ser un número.',
            'payment_amount.min' => 'El monto del pago no puede ser negativo.',
            'payment_file.required_if' => 'El comprobante de pago es obligatorio para el método de pago seleccionado.',
            'payment_file.file' => 'El comprobante de pago debe ser un archivo válido.',
            'payment_file.max' => 'El comprobante de pago no debe superar los 10 MB.',
            'payment_file.mimes' => 'El comprobante de pago debe ser un archivo de tipo: jpg, jpeg, png, pdf.',
        ];
    }
}
