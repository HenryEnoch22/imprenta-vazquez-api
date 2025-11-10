<?php

namespace App\Http\Requests\customers;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            // Campos del usuario
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // mandar password_confirmation en el formulario, así la validación 'confirmed' la compara

            // Campos del cliente
            'business_name' => ['required', 'string', 'max:150'],
            'representative_name' => ['nullable', 'string', 'max:100'],
            'rfc' => ['required', 'string', 'size:13', 'regex:/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/i'],
            'phone_number' => ['required', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],

            // --- Dirección del cliente ---
            'postal_code' => ['required', 'string', 'size:5', 'regex:/^\d{5}$/'], // C.P. mexicano
            'address' => ['required', 'string', 'max:255'],
            'locality_name' => ['required', 'string', 'max:100'],
            'federal_entity' => ['required', 'string', 'max:100'],
            'neighborhood' => ['required', 'string', 'max:150'],
            'municipality' => ['required', 'string', 'max:100'],
            'between_streets' => ['required', 'string', 'max:255'],
            'interior_number' => ['nullable', 'string', 'max:10'],
            'exterior_number' => ['required', 'string', 'max:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // --- Usuario ---
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'Este nombre de usuario ya existe.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',

            // --- Cliente ---
            'business_name.required' => 'El nombre comercial es obligatorio.',
            'representative_name.required' => 'El nombre del representante es obligatorio.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'phone_number.regex' => 'El número de teléfono solo puede contener números y símbolos válidos.',

            // --- Dirección ---
            'address.postal_code.required' => 'El código postal es obligatorio.',
            'address.postal_code.regex' => 'El código postal debe tener 5 dígitos.',
            'address.address.required' => 'La calle o dirección es obligatoria.',
            'address.locality_name.required' => 'La localidad es obligatoria.',
            'address.federal_entity.required' => 'La entidad federativa es obligatoria.',
            'address.neighborhood.required' => 'La colonia o barrio es obligatoria.',
            'address.municipality.required' => 'El municipio es obligatorio.',
        ];
    }
}
