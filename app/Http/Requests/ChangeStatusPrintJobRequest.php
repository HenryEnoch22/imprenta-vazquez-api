<?php

namespace App\Http\Requests;

use App\Models\PrintJobRequest;
use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusPrintJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = Auth()->user();
        $isAdmin = $user->is_admin;

        return [
            'status' => [
                'required',
                'string',
                'in:' . implode(',', [
                    PrintJobRequest::STATUS_PENDING,
                    PrintJobRequest::STATUS_WAITING_ACCEPTANCE,
                    PrintJobRequest::STATUS_ACCEPTED,
                    PrintJobRequest::STATUS_IN_PROGRESS,
                    PrintJobRequest::STATUS_COMPLETED,
                    PrintJobRequest::STATUS_DECLINED,
                    PrintJobRequest::STATUS_REJECTED,
                ])
            ],

            // Precio y fecha: requeridos cuando ADMIN envía cotización (pending → waiting_acceptance)
            'price' => [
                'nullable',
                $isAdmin ? 'required_if:status,' . PrintJobRequest::STATUS_WAITING_ACCEPTANCE : '',
                'numeric',
                'min:0'
            ],
            'estimated_date' => [
                'nullable',
                $isAdmin ? 'required_if:status,' . PrintJobRequest::STATUS_WAITING_ACCEPTANCE : '',
                'date',
                'after_or_equal:today'
            ],

            // Datos de pago: requeridos cuando CLIENTE acepta (waiting_acceptance → accepted)
            'payment_method' => [
                'nullable',
                !$isAdmin ? 'required_if:status,' . PrintJobRequest::STATUS_ACCEPTED : '',
                'integer',
                'in:1,2,3'
            ],
            'payment_amount' => [
                'nullable',
                !$isAdmin ? 'required_if:status,' . PrintJobRequest::STATUS_ACCEPTED : '',
                'numeric',
                'min:0'
            ],

            'payment_file' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,pdf',
                // Requerido si: usuario NO es admin Y status es ACCEPTED Y payment_method es 3
                function ($attribute, $value, $fail) use ($isAdmin) {
                    if (!$isAdmin &&
                        request('status') == PrintJobRequest::STATUS_ACCEPTED &&
                        request('payment_method') == 3 &&
                        empty(request()->file('payment_file'))) {
                        $fail('Debes adjuntar el comprobante de pago cuando seleccionas el método de pago 3.');
                    }
                }
            ],

            'reason_rejection' => [
                'nullable',
                !$isAdmin ? 'required_if:status,' . PrintJobRequest::STATUS_REJECTED : '',
                $isAdmin ? 'required_if:status,' . PrintJobRequest::STATUS_DECLINED : '',
                'string',
                'max:1000',
                'min:10'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',

            'price.required_if' => 'El precio es obligatorio al enviar la cotización.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio debe ser mayor o igual a 0.',

            'estimated_date.required_if' => 'La fecha estimada es obligatoria al enviar la cotización.',
            'estimated_date.date' => 'La fecha estimada debe ser una fecha válida.',
            'estimated_date.after_or_equal' => 'La fecha estimada debe ser igual o posterior a hoy.',

            'payment_method.required_if' => 'Debes seleccionar un método de pago al aceptar.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',

            'payment_amount.required_if' => 'El monto a pagar es obligatorio al aceptar.',
            'payment_amount.numeric' => 'El monto debe ser un número.',
            'payment_amount.min' => 'El monto debe ser mayor o igual a 0.',

            'payment_file.required_if' => 'Debes adjuntar el comprobante de pago.',
            'payment_file.file' => 'El comprobante debe ser un archivo.',
            'payment_file.max' => 'El comprobante no debe superar los 10 MB.',
            'payment_file.mimes' => 'El comprobante debe ser JPG, PNG o PDF.',

            'reason_rejection.required_if' => 'El motivo de rechazo es obligatorio al rechazar la cotización.',
            'reason_rejection.string' => 'El motivo de rechazo debe ser texto.',
            'reason_rejection.max' => 'El motivo de rechazo no debe exceder los 1000 caracteres.',
            'reason_rejection.min' => 'El motivo de rechazo debe tener al menos 10 caracteres.',
        ];
    }
}
