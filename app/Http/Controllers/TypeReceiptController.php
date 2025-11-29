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
        $typesReceipts->each(function ($typeReceipt) {
            $typeReceipt->category = TypeReceipt::$categories[$typeReceipt->type_receipt_category_id] ?? 'Sin categoría';
        });

        return response()->json([
            'data' => $typesReceipts
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
    public function store(StoreTypeReceiptRequest $request)
    {
        $data = $request->validated();

        $typeReceipt = TypeReceipt::create([
            'type_receipt_category_id' => $data['type_receipt_category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Tipo de comprobante creado exitosamente.',
            'data' => $typeReceipt
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($typeReceipt)
    {
        return response()->json([
            'data' => TypeReceipt::findOrFail($typeReceipt)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TypeReceipt $typeReceipt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTypeReceiptRequest $request, $typeReceipt)
    {
        $data = $request->validated();

        $typeReceiptModel = TypeReceipt::findOrFail($typeReceipt);
        $typeReceiptModel->update([
            'type_receipt_category_id' => $data['type_receipt_category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Tipo de comprobante actualizado exitosamente.',
            'data' => $typeReceiptModel
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($typeReceipt)
    {
        $typeReceipt = TypeReceipt::findOrFail($typeReceipt);
        $typeReceipt->delete();

        return response()->json([
            'message' => 'Tipo de comprobante eliminado exitosamente.'
        ], 200);
    }
}
