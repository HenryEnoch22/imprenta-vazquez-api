<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeStatusPrintJobRequest;
use App\Http\Requests\StorePrintJobRequest;
use App\Http\Requests\UpdatePrintJobRequest;
use App\Models\PrintJobRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrintJobRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->is_admin) {
            $printJobs = PrintJobRequest::with(['customer', 'typeReceipt'])->get();
        } else {
            $printJobs = PrintJobRequest::with(['customer', 'typeReceipt'])
                ->where('customer_id', $user->customer->id)
                ->get();
        }

        return response()->json($printJobs);
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
    public function store(StorePrintJobRequest $request)
    {
        if (Auth::user()->is_admin) {
            return response()->json([
                'message' => 'No autorizado para crear solicitudes de impresión.',
            ], 403);
        }
        $validated = $request->validated();

        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $path = Storage::disk('print-files')->putFileAs(
                '',
                $request->file('file_path'),
                time().'_'.$file->getClientOriginalName()
            );
            $validated['file_path'] = $path;
        }

        $printJob = PrintJobRequest::create(
            array_merge($validated, [
            'status' => 1, // solicitada
        ]));

        return response()->json([
            'message' => 'Solicitud de impresión creada exitosamente.',
            'data' => $printJob,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($printJobRequest)
    {
        $printJobRequest = PrintJobRequest::findOrFail($printJobRequest);
        $printJobRequest->load(['customer', 'typeReceipt']);
        $printJobRequest->file_path = Storage::disk('print-files')->url($printJobRequest->file_path);

        return response()->json($printJobRequest, 200);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PrintJobRequest $printJobRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrintJobRequest $request, $printJob)
    {
        $validated = $request->validated();
        $printJob = PrintJobRequest::findOrFail($printJob);

        $user = Auth::user();

        // ADMIN NO EDITA CONTENIDO, SOLO ESTADOS
        if ($user->is_admin) {
            return response()->json([
                'message' => 'Un administrador no puede modificar el contenido de la solicitud.',
            ], 403);
        }

        // Validar solo editar su propia solicitud
        if (!$user->customer || $printJob->customer_id != $user->customer->id) {
            return response()->json([
                'message' => 'No autorizado para modificar esta solicitud de impresión.',
            ], 403);
        }

        // Validar si el cliente puede editarla
        if (!$this->canClientEdit($printJob)) {
            return response()->json([
                'message' => 'No se puede editar una solicitud en este estado.',
            ], 400);
        }

        // Cambios de estado automáticos para clientes
        if ($printJob->status == 5) {
            $printJob->status = 2; // pasa a "esperando aceptación"
        }

        if ($request->hasFile('file_path')) {
            if ($printJob->file_path && Storage::disk('print-files')->exists($printJob->file_path)) {
                Storage::disk('print-files')->delete($printJob->file_path);
            }

            $file = $request->file('file_path');
            $path = Storage::disk('print-files')->putFileAs(
                '',
                $file,
                time().'_'.$file->getClientOriginalName()
            );

            $validated['file_path'] = $path;
        }

        $printJob->update([
            'name' => $validated['name'],
            'type_receipt_id' => $validated['type_receipt_id'],
            'description' => $validated['description'],
            'folio' => $validated['folio'],
            'paper_size' => $validated['paper_size'],
            'copies_number' => $validated['copies_number'],
            'copies_colors' => $validated['copies_colors'],
            'tint_colors' => $validated['tint_colors'],
            'paper_type' => $validated['paper_type'],
            'quantity' => $validated['quantity'],
            'file_path' => $validated['file_path'] ?? $printJob->file_path,
        ]);

        return response()->json([
            'message' => 'Solicitud actualizada exitosamente.',
            'data' => $printJob,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($printJobRequest)
    {
        $printJobRequest = PrintJobRequest::findOrFail($printJobRequest);
        $printJobRequest->delete();

        return response()->json([
            'message' => 'Solicitud de impresión eliminada exitosamente.',
        ], 200);
    }

    /**
     * Change status of a specified print job request.
     */
    public function changeStatus(ChangeStatusPrintJobRequest $request, $printJobRequestId)
    {
        $validated = $request->validated();
        $printJobRequest = PrintJobRequest::findOrFail($printJobRequestId);
        $newStatus = $validated['status'];

        $user = Auth::user();

        // CLIENTES NO PUEDEN CAMBIAR ESTADOS
        if (!$user->is_admin) {
            return response()->json([
                'message' => 'Un cliente no puede cambiar el estado de la solicitud.',
            ], 403);
        }

        // ADMIN NO PUEDE EDITAR TERMINADAS O RECHAZADAS
        if (!$this->canAdminEdit($printJobRequest->status)) {
            return response()->json([
                'message' => 'No se puede editar esta solicitud en su estado actual.',
            ], 400);
        }

        // VALIDAR TRANSICION
        if (! $this->isValidTransition($printJobRequest->status, $newStatus)) {
            return response()->json([
                'message' => 'La transición de estado no es válida.',
            ], 400);
        }

        $printJobRequest->status = $newStatus;

        // SI ES RECHAZADA, GUARDAR RAZÓN
        if ($newStatus == 5) {
            $printJobRequest->reason_rejection = $validated['reason_rejection'] ?? null;
        }

        $printJobRequest->save();

        return response()->json([
            'message' => 'Estado actualizado exitosamente.',
            'data' => $printJobRequest,
        ], 200);
    }

    /**
     * @var int[][] $adminTransitions Mapa de transiciones de estado permitidas para administradores.
     */
    private $adminTransitions = [
        1 => [3, 5], // solicitada → en proceso o rechazada
        2 => [3, 5], // esperando aceptación → en proceso o rechazada
        3 => [4],    // en proceso → terminada
    ];

    /**
     * @param int $job
     * @return bool Indica si un administrador puede editar la solicitud de impresión dada.
     */
    private function canAdminEdit($job): bool
    {
        return isset($this->adminTransitions[$job]);
    }

    /**
     * @param PrintJobRequest $job
     * @return bool Indica si un cliente puede editar la solicitud de impresión dada.
     */

    private function canClientEdit(PrintJobRequest $job): bool
    {
        return in_array($job->status, [1, 2, 5]);
    }

    /**
     * @param int $from Estado actual.
     * @param int $to Estado al que se desea cambiar.
     * @return bool Indica si la transición de estado es válida.
     */
    private function isValidTransition($from, $to): bool
    {
        return isset($this->adminTransitions[$from])
            && in_array($to, $this->adminTransitions[$from]);
    }

    /**
     * Reject a specified print job request.
     */
    public function reject(ChangeStatusPrintJobRequest $request)
    {
        $validated = $request->validated();
        $printJobRequest = PrintJobRequest::findOrFail($validated['print_job_request_id']);
        if ($printJobRequest->status != 1) { // 1 es "solicitada"
            return response()->json([
                'message' => 'No se puede rechazar una solicitud de impresión que no está en estado "Solicitada".',
            ], 400);
        }

        if (!Auth::user()->is_admin) {
            return response()->json([
                'message' => 'Usuario no autorizado para rechazar solicitudes de impresión.',
            ], 403);
        }

        $printJobRequest->update([
            'status' => 5, // rechazado
            'reason_rejection' => $validated['reason_rejection'],
        ]);

        return response()->json([
            'message' => 'Solicitud de impresión rechazada exitosamente.',
            'data' => $printJobRequest,
        ], 200);

    }
}
