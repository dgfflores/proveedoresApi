<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/saludo', function () {
    return response()->json(['mensaje' => '¡Hola desde la API!']);
});

Route::post('upload-invoice', [InvoiceController::class, 'upload']);
