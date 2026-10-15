<?php

use App\Http\Controllers\ClasificationController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('upload-invoice', [InvoiceController::class, 'upload']);
Route::get('clasifications', [ClasificationController::class, 'index']);

Route::get('invoices/lookup', [InvoiceController::class, 'lookup'])
    ->middleware(['throttle:30,1', 'captcha:lookup_invoice']);

Route::post('upload-invoice', [InvoiceController::class, 'upload'])
    ->middleware(['throttle:10,1', 'captcha:upload_invoice']);
