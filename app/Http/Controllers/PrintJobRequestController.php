<?php

namespace App\Http\Controllers;

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
        $printJobs = PrintJobRequest::with(['customer', 'typeReceipt'])->get();
        \Log::info([
            'msg' => 'Listando solicitudes de impresión',
            'user_id' => Auth::id(),
            'datos' => $printJobs,
        ]);
//        $printJobs->each(function ($job) {
//            $job->load(['customer']);
//            $job->category = PrintJobRequest::$categories[$job->category_id] ?? 'Sin categoria';
//            $job->paper_size = PrintJobRequest::$paperSizes[$job->paper_size] ?? 'No definido';
//            $job->paper_type = PrintJobRequest::$paperTypes[$job->paper_type] ?? 'No definido';
//        });

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
    public function update(UpdatePrintJobRequest $request, $printJobRequest)
    {
        \Log::info([
            'msg' => 'Actualizando solicitud de impresión',
            'user_id' => Auth::id(),
            'print_job_request_id' => $printJobRequest,
            'datos' => $request->all(),
        ]);
        $validated = $request->validated();

        $printJobRequest = PrintJobRequest::findOrFail($printJobRequest);
        if ($printJobRequest->status == 1) {

            if ($request->hasFile('file_path')) {
                // Eliminar el archivo anterior si existe
                if ($printJobRequest->file_path && Storage::disk('print-files')->exists($printJobRequest->file_path)) {
                    Storage::disk('print-files')->delete($printJobRequest->file_path);
                }

                $file = $request->file('file_path');
                $path = Storage::disk('print-files')->putFileAs(
                    '',
                    $request->file('file_path'),
                    time().'_'.$file->getClientOriginalName()
                );
                $validated['file_path'] = $path;
            }
            $printJobRequest->update($validated);

            return response()->json([
                'message' => 'Solicitud de impresión actualizada exitosamente.',
                'data' => $printJobRequest,
            ], 200);
        }

        return response()->json([
            'message' => 'No se puede actualizar una solicitud de impresión que no está en estado "Solicitada".',
        ], 400);
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
}
