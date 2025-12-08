<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeStatusPrintJobRequest;
use App\Http\Requests\StorePrintJobRequest;
use App\Http\Requests\UpdatePrintJobRequest;
use App\Models\PrintJobPayment;
use App\Models\PrintJobRequest;
use Illuminate\Http\Request;
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
                'status' => PrintJobRequest::STATUS_PENDING,
            ])
        );

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
        $printJobRequest->load(['customer', 'typeReceipt', 'payments']);
        $printJobRequest->file_path = Storage::disk('print-files')->url($printJobRequest->file_path);

        return response()->json($printJobRequest, 200);
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
        if (!$printJob->canBeEditedByClient()) {
            return response()->json([
                'message' => 'No se puede editar una solicitud en este estado.',
            ], 400);
        }

        // Si estaba rechazada o declinada, al editarla pasa a pending de nuevo
        if (in_array($printJob->status, [PrintJobRequest::STATUS_REJECTED, PrintJobRequest::STATUS_DECLINED])) {
            $printJob->status = PrintJobRequest::STATUS_PENDING;
            $printJob->reason_rejection = null;
        }

        // Manejar archivo si se subió uno nuevo
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

        // Actualizar solo los campos permitidos
        $printJob->update(array_filter([
            'name' => $validated['name'] ?? $printJob->name,
            'type_receipt_id' => $validated['type_receipt_id'] ?? $printJob->type_receipt_id,
            'description' => $validated['description'] ?? $printJob->description,
            'folio' => $validated['folio'] ?? $printJob->folio,
            'paper_size' => $validated['paper_size'] ?? $printJob->paper_size,
            'copies_number' => $validated['copies_number'] ?? $printJob->copies_number,
            'copies_colors' => $validated['copies_colors'] ?? $printJob->copies_colors,
            'tint_colors' => $validated['tint_colors'] ?? $printJob->tint_colors,
            'paper_type' => $validated['paper_type'] ?? $printJob->paper_type,
            'quantity' => $validated['quantity'] ?? $printJob->quantity,
            'file_path' => $validated['file_path'] ?? $printJob->file_path,
        ]));

        return response()->json([
            'message' => 'Solicitud actualizada exitosamente.',
            'data' => $printJob->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($printJobRequest)
    {
        $printJobRequest = PrintJobRequest::findOrFail($printJobRequest);
        $user = Auth::user();

        if (!$user->is_admin) {
            return response()->json([
                'message' => 'No autorizado para eliminar solicitudes.',
            ], 403);
        }

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

        // Determinar si es admin o cliente
        $isAdmin = $user->is_admin;

        // VALIDAR PERMISOS SEGÚN USUARIO
        if ($isAdmin) {
            // Admin no puede editar completadas, rechazadas o declinadas
            if (!$printJobRequest->canBeEditedByAdmin()) {
                return response()->json([
                    'message' => 'No se puede editar esta solicitud en su estado actual.',
                ], 400);
            }

            // VALIDAR TRANSICION PARA ADMIN
            if (!$this->isValidAdminTransition($printJobRequest->status, $newStatus)) {
                return response()->json([
                    'message' => 'La transición de estado no es válida.',
                ], 400);
            }
        } else {
            // Cliente solo puede aceptar o declinar desde waiting_acceptance
            if ($printJobRequest->status !== PrintJobRequest::STATUS_WAITING_ACCEPTANCE) {
                return response()->json([
                    'message' => 'Solo puedes cambiar el estado de solicitudes en espera de aceptación.',
                ], 400);
            }

            if (!in_array($newStatus, [PrintJobRequest::STATUS_ACCEPTED, PrintJobRequest::STATUS_REJECTED])) {
                return response()->json([
                    'message' => 'Solo puedes aceptar o rechazar la cotización y fecha estimada.',
                ], 400);
            }

            // Validar que sea su propia solicitud
            if (!$user->customer || $printJobRequest->customer_id !== $user->customer->id) {
                return response()->json([
                    'message' => 'No autorizado para modificar esta solicitud.',
                ], 403);
            }
        }

        // ACTUALIZAR ESTADO
        $printJobRequest->status = $newStatus;

        // LÓGICA SEGÚN EL NUEVO ESTADO
        if ($newStatus == PrintJobRequest::STATUS_WAITING_ACCEPTANCE && $isAdmin) {
            // Admin envía cotización al cliente
            $printJobRequest->price = $validated['price'];
            $printJobRequest->estimated_date = $validated['estimated_date'];
        }

        if ($newStatus == PrintJobRequest::STATUS_ACCEPTED && !$isAdmin) {
            if ($validated['payment_method'] != 3 && $request->hasFile('payment_file')) {
                $file = $request->file('payment_file');
                $path = Storage::disk('payment-files')->putFileAs(
                    '',
                    $file,
                    time().'_'.$file->getClientOriginalName()
                );
            } else {
                $path = null;
            }
            PrintJobPayment::create([
                'print_job_request_id' => $printJobRequest->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['payment_amount'],
                'paid_at' => now(),
                'file_path' => $path,
            ]);
            $printJobRequest->reason_rejection = null;
        }

        if ($newStatus == PrintJobRequest::STATUS_REJECTED || $newStatus == PrintJobRequest::STATUS_DECLINED) {
            // Admin rechaza la solicitud
            $printJobRequest->reason_rejection = $validated['reason_rejection'];
            $printJobRequest->price = null;
            $printJobRequest->estimated_date = null;
        }

        $printJobRequest->save();

        return response()->json([
            'message' => 'Estado actualizado exitosamente.',
            'data' => $printJobRequest,
        ], 200);
    }

    /**
     * Mapa de transiciones de estado permitidas para administradores.
     */
    private $adminTransitions = [
        PrintJobRequest::STATUS_PENDING => [
            PrintJobRequest::STATUS_WAITING_ACCEPTANCE,
            PrintJobRequest::STATUS_DECLINED,
        ],
        PrintJobRequest::STATUS_WAITING_ACCEPTANCE => [
            PrintJobRequest::STATUS_DECLINED,
        ],
        PrintJobRequest::STATUS_ACCEPTED => [
            PrintJobRequest::STATUS_IN_PROGRESS,
        ],
        PrintJobRequest::STATUS_IN_PROGRESS => [
            PrintJobRequest::STATUS_COMPLETED,
        ],
    ];

    /**
     * Verifica si la transición de estado es válida para admin.
     */
    private function isValidAdminTransition(string $from, string $to): bool
    {
        return isset($this->adminTransitions[$from]) && in_array($to, $this->adminTransitions[$from]);
    }
}
