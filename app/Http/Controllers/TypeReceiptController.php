<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTypeReceiptRequest;
use App\Http\Requests\UpdateTypeReceiptRequest;
use App\Models\TypeReceipt;
use Illuminate\Http\Request;

class TypeReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $typesReceipts = TypeReceipt::all();

        if(auth()->user() && !auth()->user()->is_admin){
            return response()->json(['message' => 'Usuario no autorizado'], 403);
        }

        return response()->json([
            'data' => $typesReceipts
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTypeReceiptRequest $request)
    {
        $data = $request->validated();

        if(auth()->user() && !auth()->user()->is_admin){
            return response()->json(['message' => 'Usuario no autorizado'], 403);
        }

        TypeReceipt::create([
            'receipt_category' => $data['receipt_category'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Tipo de comprobante creado exitosamente.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($typeReceiptId)
    {
        try{
            $typeReceipt = TypeReceipt::findOrfail($typeReceiptId);
            if(auth()->user() && !auth()->user()->is_admin){
                return response()->json(['message' => 'Usuario no autorizado'], 403);
            }
            return response()->json([
                'data' => $typeReceipt
            ], 200);

        }catch (\Exception $e){
            return response()->json([
                'message' => 'Tipo de comprobante no encontrado.'
            ], 404);
        }

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTypeReceiptRequest $request, $typeReceipt)
    {
        try{
            $data = $request->validated();

            if(auth()->user() && !auth()->user()->is_admin){
                return response()->json(['message' => 'Usuario no autorizado'], 403);
            }

            $typeReceiptModel = TypeReceipt::findOrFail($typeReceipt);
            $typeReceiptModel->update([
                'receipt_category' => $data['receipt_category'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            return response()->json([
                'message' => 'Tipo de comprobante actualizado exitosamente.',
            ], 200);

        }catch (\Exception $e){
            return response()->json([
                'message' => 'Tipo de comprobante no encontrado.'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($typeReceiptId)
    {
        try{
            if(auth()->user() && !auth()->user()->is_admin){
                return response()->json(['message' => 'Usuario no autorizado'], 403);
            }

            $typeReceipt = TypeReceipt::findOrFail($typeReceiptId);
            $typeReceipt->delete();

            return response()->json([
                'message' => 'Tipo de comprobante eliminado exitosamente.'
            ], 200);

        }catch (\Exception $e){
            return response()->json([
                'message' => 'Tipo de comprobante no encontrado.'
            ], 404);
        }
    }
}
