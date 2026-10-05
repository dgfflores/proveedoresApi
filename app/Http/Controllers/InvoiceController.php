<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadInvoiceRequest;
use App\Http\Responses\ApiResponse;
use App\Models\RepseCatalog;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Illuminate\Support\Facades\Http;

//Verificar si viene el timbre fiscal digital
//Verificar en cdfi_data -> sat_status -> data -> status-> estado
// Antes de hacer la consulta y hacer el match que de alguna manera me consume recursos deberia consultar primero el status del proveedor por si ya está considerado como repse 
// has_matches valor para verificar si alguna clave coincide con repse dentro de cfdi_data - repse_validation



class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function upload(UploadInvoiceRequest $request)
    {
        $result = $this->invoiceService->processUpload($request->file('xml_file'));

        return ApiResponse::success([
            'cfdi_data' => $result
        ], 'Factura procesada y analizada correctamente');
    }
}
