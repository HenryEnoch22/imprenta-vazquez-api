<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrintJobPaymentRequest;
use App\Http\Requests\UpdatePrintJobPaymentRequest;
use App\Models\PrintJobPayment;
use App\Models\PrintJobRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrintJobPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = PrintJobPayment::all();
        return response()->json([
            'data' => $payments
        ], 200);
    }

    /**
     * Display a listing of the resource by PrintJobRequest.
     */
    public function paymentsByPrintJobRequest($printJobRequestId)
    {
        $payments = PrintJobPayment::where('print_job_request_id', $printJobRequestId)->get();
        return response()->json([
            'data' => $payments
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePrintJobPaymentRequest $request)
    {
        $validated = $request->validated();
        $printJobRequest = PrintJobRequest::findOrFail($validated['print_job_request_id']);
        $user = Auth::user();

        // Solo el cliente puede agregar pagos a su propia solicitud
        if ($user->is_admin) {
            return response()->json([
                'message' => 'Los administradores no pueden agregar pagos.',
            ], 403);
        }

        if (!$user->customer || $printJobRequest->customer_id !== $user->customer->id) {
            return response()->json([
                'message' => 'No autorizado para agregar pagos a esta solicitud.',
            ], 403);
        }

        // Validar que la solicitud esté en estados que permitan pagos
        if (!in_array($printJobRequest->status, [
            PrintJobRequest::STATUS_ACCEPTED,
            PrintJobRequest::STATUS_IN_PROGRESS
        ])) {
            return response()->json([
                'message' => 'Solo puedes agregar pagos a solicitudes aceptadas o en proceso.',
            ], 400);
        }

        // Para pago en efectivo, el monto es el restante
        if ($validated['payment_method'] == 3) {
            $totalPaid = $printJobRequest->payments()->sum('amount');
            $validated['payment_amount'] = $printJobRequest->price - $totalPaid;
        }

        // Validar que el monto no exceda el restante
        $totalPaid = $printJobRequest->payments()->sum('amount');
        $remaining = $printJobRequest->price - $totalPaid;

        if ($validated['payment_amount'] > $remaining) {
            return response()->json([
                'message' => 'El monto excede el saldo pendiente.',
            ], 400);
        }

        // Crear el pago
        $payment = PrintJobPayment::create([
            'print_job_request_id' => $printJobRequest->id,
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['payment_amount'],
            'paid_at' => now(),
        ]);

        // Guardar archivo si existe
        if ($request->hasFile('payment_file')) {
            $file = $request->file('payment_file');
            $path = Storage::disk('payment-files')->putFileAs(
                '',
                $file,
                time().'_'.$file->getClientOriginalName()
            );
            $payment->file_path = $path;
        }

        $payment->save();

        // Actualizar is_paid si ya se completó el pago
        $newTotalPaid = $printJobRequest->payments()->sum('amount');
        if ($newTotalPaid >= $printJobRequest->price) {
            $printJobRequest->is_paid = true;
            $printJobRequest->save();
        }

        return response()->json([
            'message' => 'Pago registrado exitosamente.',
            'data' => $payment,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($printJobPayment)
    {
        return response()->json([
            'data' => PrintJobPayment::findOrFail($printJobPayment)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PrintJobPayment $printJobPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrintJobPaymentRequest $request, $printJobPayment)
    {
        $validated = $request->validated();

        $payment = PrintJobPayment::findOrFail($printJobPayment);
        $payment->update($validated);

        return response()->json([
            'message' => 'Pago de solicitud de impresión actualizado exitosamente.',
            'data' => $payment,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($printJobPayment)
    {
        $payment = PrintJobPayment::findOrFail($printJobPayment);
        $payment->delete();

        return response()->json([
            'message' => 'Pago de solicitud de impresión eliminado exitosamente.'
        ], 200);
    }
}
