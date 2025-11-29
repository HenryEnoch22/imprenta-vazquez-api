<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    if (!$user->is_admin) {
        return response()->json($user->load('customer.customerAddress'), 200);
    }
    return response()->json($user, 200);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::ApiResource('customers', \App\Http\Controllers\CustomerController::class);
    Route::ApiResource('type-receipts', \App\Http\Controllers\TypeReceiptController::class);
    Route::ApiResource('print-jobs-payments', \App\Http\Controllers\PrintJobPaymentController::class);
    Route::ApiResource('print-jobs', \App\Http\Controllers\PrintJobRequestController::class);
    Route::get('print-jobs/{print-job-request}/payments', [\App\Http\Controllers\PrintJobPaymentController::class, 'paymentsByPrintJobRequest']);
});



require __DIR__.'/auth.php';
