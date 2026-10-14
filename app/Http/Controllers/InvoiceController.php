<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadInvoiceRequest;
use App\Http\Responses\ApiResponse;
use App\Models\RepseCatalog;
use App\Models\Supplier;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Illuminate\Support\Facades\Http;
use App\Traits\DateHelpers;
use App\Services\DocumentStorageService;
use App\Services\BarcodePdfService;
use App\Services\CfdiParserService;

//Verificar si viene el timbre fiscal digital
//Verificar en cdfi_data -> sat_status -> data -> status-> estado
// Antes de hacer la consulta y hacer el match que de alguna manera me consume recursos deberia consultar primero el status del proveedor por si ya está considerado como repse 
// has_matches valor para verificar si alguna clave coincide con repse dentro de cfdi_data - repse_validation
// agregar un log para saber exactamente que me esta devolviendo el sat
// seria bueno igual validar que el rfc no exista en los uuids cargados es decir del registro de invoices
// VALIDAR RFC RECEPTOR MATCH CON RFC COMPAÑIAS Y SI NO EXISTE REVOCARLO
//validar que el rfc receptor sea el mismo que tiene la division


class InvoiceController extends Controller
{
    use DateHelpers;
    
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly CfdiParserService $cfdiParserService,
        private readonly DocumentStorageService $storageService,
        private readonly BarcodePdfService $barcodePdfService
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
        $rfcEmisor = $request->validated('rfc_receptor');
        $clasificationId = $request->input('clasification_id');
        
        $supplier = Supplier::where('rfc', $rfcEmisor)->first();

        //CAMBIAR VALIDACION POR SI NO EXISTE REGISTRARLO 
        // if (!$supplier) {
        //     return response()->json([
        //         'message' => 'El RFC proporcionado no está registrado en nuestro catálogo de proveedores.'
        //     ], 422);
        // }


        // return response()->json([
        //     'message' => 'El proveedor no tiene permitida la carga de facturas.'
        // ], 422);


        if (!$supplier->is_active || !$supplier->load_invoice) {
            return response()->json([
                'message' => 'El proveedor no tiene permitida la carga de facturas.'
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

        $folderPath = "1" . $this->getYearMonthPath();
        $invoice = $result['invoice'];
        $trackId = $invoice['track_id'];

        $cfdiData = $this->cfdiParserService->parse($request->file('xml_file'));
        $rfcEmisor = $cfdiData->rfcEmisor;
        $rfcReceptor = $cfdiData->rfcReceptor;

        $data = [
            'trackId' => $invoice['track_id'],
            'uuid'    => $invoice['uuid'],
            'total'   => $invoice['total'],
            'serie'   => $invoice['serie'],
            'folio'   => $invoice['folio'],
            'rfcReceptor' => $rfcReceptor,
            'rfcEmisor' => $rfcEmisor,
            'division' => $invoice['division']
        ];

        $disk = 'local';
        $pathXml = $this->storageService->storeXml($request->file('xml_file'), $folderPath, $trackId.'.xml', $disk);
        $pathPdf = $this->storageService->storePdf($request->file('pdf_unico'), $folderPath, $trackId.'.pdf', $disk);
        $pdfMultiplePaths = $this->storageService->storeMultiplePdfs($request->file('pdf_files', []), $folderPath, $trackId, $disk);

        $barcodeSvg = $this->barcodePdfService->generatePdf($data, $folderPath, $trackId . 'acuse.pdf', 'local');


        return ApiResponse::success([
            // 'cfdi_data' => $result,
            // 'track_id' => $trackId,
            'has_repse' => $hasRepse,
            'invoice'   => $result['invoice'], // Retornamos la factura creada con su track_id
        ], $message, 201);

    }
}
