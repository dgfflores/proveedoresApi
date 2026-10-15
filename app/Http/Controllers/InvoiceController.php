<?php

namespace App\Http\Controllers;

use App\Http\Requests\LookupInvoiceRequest;
use App\Http\Requests\UploadInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Responses\ApiResponse;
use App\Models\RepseCatalog;
use App\Models\Supplier;
use App\Services\InvoiceLookupService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Illuminate\Support\Facades\Http;

//Verificar si viene el timbre fiscal digital
//Verificar en cdfi_data -> sat_status -> data -> status-> estado
// Antes de hacer la consulta y hacer el match que de alguna manera me consume recursos deberia consultar primero el status del proveedor por si ya está considerado como repse 
// has_matches valor para verificar si alguna clave coincide con repse dentro de cfdi_data - repse_validation
// agregar un log para saber exactamente que me esta devolviendo el sat
// seria bueno igual validar que el rfc no exista en los uuids cargados es decir del registro de invoices
// VALIDAR RFC RECEPTOR MATCH CON RFC COMPAÑIAS Y SI NO EXISTE REVOCARLO 


class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    // public function upload(UploadInvoiceRequest $request)
    // {

    //     $result = $this->invoiceService->processUpload($request->file('xml_file'));

    //     $satStatus = data_get($result, 'sat_status');

    //     if (!$satStatus || data_get($satStatus, 'success') === false) {
    //         return ApiResponse::error(
    //             'No se pudo validar el estado del CFDI ante el SAT.',
    //             502
    //         );
    //     }

    //     $estado = data_get($result, 'sat_status.estado');

    //     if ($estado !== 'Vigente') {
    //         $mensajes = [
    //             'Cancelado'     => 'El CFDI se encuentra cancelado ante el SAT y no puede ser procesado.',
    //             'No Encontrado' => 'El CFDI no fue encontrado en el SAT. Verifica que el comprobante esté timbrado.',
    //             'En proceso'    => 'El CFDI se encuentra en proceso de cancelación ante el SAT.',
    //         ];

    //         if (!isset($mensajes[$estado])) {
    //             Log::warning('Estado SAT desconocido', ['sat_status' => $satStatus]);
    //         }


    //         return ApiResponse::error(
    //             $mensajes[$estado] ?? 'El CFDI no está vigente ante el SAT.',
    //             422
    //         );
    //     }

    //     $hasRepse = (bool) data_get($result, 'repse_validation.has_matches', false);

    //     $message = $hasRepse
    //         ? 'El CFDI contiene claves catalogadas como REPSE. Es probable que recibas un correo para cargar información adicional.'
    //         : 'Factura procesada y analizada correctamente';

    //     return ApiResponse::success([
    //         'cfdi_data' => $result,
    //         'has_repse' => $hasRepse,
    //     ], $message);
    // }

    public function upload(UploadInvoiceRequest $request)
    {
        // El servicio se encarga de parsear, validar el SAT, REPSE y, si todo es correcto, guardar la factura
        // NOTA: Asegúrate de pasar el clasification_id y supplier_id desde la petición o el contexto del usuario autenticado
        $rfcReceptor = $request->validated('rfc_receptor');
        $clasificationId = $request->input('clasification_id');

        $supplier = Supplier::where('rfc', $rfcReceptor)->first();

        //CAMBIAR VALIDACION POR SI NO EXISTE REGISTRARLO 
        // if (!$supplier) {
        //     return response()->json([
        //         'message' => 'El RFC proporcionado no está registrado en nuestro catálogo de proveedores.'
        //     ], 422);
        // }

        if (!$supplier->is_active || !$supplier->load_invoice) {
            return response()->json([
                'message' => 'El proveedor no no tiene permitida la carga de facturas.'
            ], 422);
        }

        $result = $this->invoiceService->processAndStoreUpload(
            $request->file('xml_file'),
            $clasificationId,
            $supplier->id,
            $request->validated() // Datos adicionales validados si los hay
        );

        $satStatus = data_get($result, 'sat_status');

        if (!$satStatus || data_get($satStatus, 'success') === false) {
            return ApiResponse::error(
                'No se pudo validar el estado del CFDI ante el SAT.',
                502
            );
        }

        $estado = data_get($result, 'sat_status.estado');

        if ($estado !== 'Vigente') {
            $mensajes = [
                'Cancelado'    => 'El CFDI se encuentra cancelado ante el SAT y no puede ser procesado.',
                'No Encontrado' => 'El CFDI no fue encontrado en el SAT. Verifica que el comprobante esté timbrado.',
                'En proceso'    => 'El CFDI se encuentra en proceso de cancelación ante el SAT.',
            ];

            if (!isset($mensajes[$estado])) {
                Log::warning('Estado SAT desconocido', ['sat_status' => $satStatus]);
            }

            return ApiResponse::error(
                $mensajes[$estado] ?? 'El CFDI no está vigente ante el SAT.',
                422
            );
        }

        $hasRepse = (bool) data_get($result, 'repse_validation.has_matches', false);

        $message = $hasRepse
            ? 'El CFDI contiene claves catalogadas como REPSE. Es probable que recibas un correo para cargar información adicional.'
            : 'Factura procesada y analizada correctamente';

        return ApiResponse::success([
            'cfdi_data' => $result,
            'has_repse' => $hasRepse,
            'invoice'   => $result['invoice'], // Retornamos la factura creada con su track_id
        ], $message, 201);
    }

    public function lookup(LookupInvoiceRequest $request, InvoiceLookupService $lookup)
    {
        $data = $request->validated();

        $invoice = $lookup->find($data['rfc_receptor'], $data['uuid'] ?? null, $data['track_id'] ?? null);

        if (!$invoice) {
            return ApiResponse::error('No se encontró un CFDI con los datos proporcionados.', 404);
        }

        return ApiResponse::success(new InvoiceResource($invoice), 'CFDI localizado correctamente.');
    }
}
