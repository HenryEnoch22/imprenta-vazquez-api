<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrintJobPaymentRequest;
use App\Http\Requests\UpdatePrintJobPaymentRequest;
use App\Models\PrintJobPayment;

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

        $payment = PrintJobPayment::create($validated);

        return response()->json([
            'message' => 'Pago de solicitud de impresión creado exitosamente.',
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
