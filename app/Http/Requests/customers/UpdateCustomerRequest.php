<?php

namespace App\Http\Requests\customers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            // Customer
            'business_name'       => ['required','string','max:150'],
            'representative_name' => ['nullable','string','max:100'],
            'rfc'                 => ['required','string','size:13','regex:/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/i'],
            'phone_number'        => ['required','string','max:15','regex:/^[0-9+\-\s()]+$/'],

            // Address
            'postal_code'     => ['required','string','size:5','regex:/^\d{5}$/'],
            'address'         => ['required','string','max:255'],
            'locality_name'   => ['required','string','max:100'],
            'federal_entity'  => ['required','string','max:100'],
            'neighborhood'    => ['required','string','max:150'],
            'municipality'    => ['required','string','max:100'],
            'between_streets' => ['required','string','max:255'],
            'interior_number' => ['nullable','string','max:10'],  // ÚNICO opcional
            'exterior_number' => ['required','string','max:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'business_name.required'       => 'El nombre comercial es obligatorio.',
            'business_name.string'         => 'El nombre comercial debe ser texto.',
            'business_name.max'            => 'El nombre comercial no puede exceder 150 caracteres.',

            'representative_name.required' => 'El nombre del representante es obligatorio.',
            'representative_name.string'   => 'El nombre del representante debe ser texto.',
            'representative_name.max'      => 'El nombre del representante no puede exceder 100 caracteres.',

            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.string'   => 'El RFC debe ser texto.',
            'rfc.size'     => 'El RFC debe tener exactamente 13 caracteres.',
            'rfc.regex'    => 'El RFC no tiene un formato válido (ej.: ABCD900101XYZ).',

            'phone_number.required' => 'El número de teléfono es obligatorio.',
            'phone_number.string'   => 'El teléfono debe ser texto.',
            'phone_number.max'      => 'El teléfono no puede exceder 15 caracteres.',
            'phone_number.regex'    => 'El teléfono solo admite números y símbolos (+ - ( ) espacios).',

            'postal_code.required' => 'El código postal es obligatorio.',
            'postal_code.string'   => 'El código postal debe ser texto.',
            'postal_code.size'     => 'El código postal debe tener 5 dígitos.',
            'postal_code.regex'    => 'El código postal debe tener solo 5 números.',

            'address.required' => 'La calle/dirección es obligatoria.',
            'address.string'   => 'La dirección debe ser texto.',
            'address.max'      => 'La dirección no puede exceder 255 caracteres.',

            'locality_name.required' => 'La localidad es obligatoria.',
            'locality_name.string'   => 'La localidad debe ser texto.',
            'locality_name.max'      => 'La localidad no puede exceder 100 caracteres.',

            'federal_entity.required' => 'La entidad federativa es obligatoria.',
            'federal_entity.string'   => 'La entidad federativa debe ser texto.',
            'federal_entity.max'      => 'La entidad federativa no puede exceder 100 caracteres.',

            'neighborhood.required' => 'La colonia/barrio es obligatoria.',
            'neighborhood.string'   => 'La colonia/barrio debe ser texto.',
            'neighborhood.max'      => 'La colonia/barrio no puede exceder 150 caracteres.',

            'municipality.required' => 'El municipio es obligatorio.',
            'municipality.string'   => 'El municipio debe ser texto.',
            'municipality.max'      => 'El municipio no puede exceder 100 caracteres.',

            'between_streets.required' => 'El campo "entre calles" es obligatorio.',
            'between_streets.string'   => 'El campo "entre calles" debe ser texto.',
            'between_streets.max'      => 'El campo "entre calles" no puede exceder 255 caracteres.',

            'interior_number.string' => 'El número interior debe ser texto.',
            'interior_number.max'    => 'El número interior no puede exceder 10 caracteres.',

            'exterior_number.required' => 'El número exterior es obligatorio.',
            'exterior_number.string'   => 'El número exterior debe ser texto.',
            'exterior_number.max'      => 'El número exterior no puede exceder 10 caracteres.',
        ];
    }

}
