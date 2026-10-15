<?php

namespace App\Services;

use App\DTOs\CfdiDataDto;
use App\Models\RepseCatalog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Clasification;
use App\Models\Invoice;


class InvoiceService
{
    // public function __construct(
    //     private readonly CfdiParserService $parserService,
    //     private readonly SatStatusService $satStatusService
    // ) {}

    // /**
    //  * Procesa la subida y validación completa de una factura.
    //  *
    //  * @return array<string, mixed>
    //  */
    // public function processUpload(UploadedFile $xmlFile): array
    // {
    //     // 1. Parsear el archivo XML
    //     $cfdiData = $this->parserService->parse($xmlFile);

    //     if (!in_array($cfdiData->tipoComprobante, ['I', 'P'], true)) {
    //         throw ValidationException::withMessages([
    //             'xml_file' => 'Solo se permiten comprobantes de tipo Ingreso (I) o Pago (P).',
    //         ]);
    //     }

    //     // 2. Validar contra el catálogo REPSE
    //     $repseValidation = $this->validateRepseCatalog($cfdiData->clavesProdServ);

    //     // 3. Consultar estatus del SAT
    //     $satStatus = $this->satStatusService->getStatus($cfdiData);

    //     return [
    //         'repse_validation' => $repseValidation,
    //         'sat_status'       => $satStatus,
    //         'tipo_comprobante' => $cfdiData->tipoComprobante,
    //     ];
    // }


    // /**
    //  * Valida las claves de producto/servicio contra el catálogo REPSE.
    //  *
    //  * @param array<string> $claves
    //  * @return array<string, mixed>
    //  */
    // private function validateRepseCatalog(array $claves): array
    // {
    //     if (empty($claves)) {
    //         return [
    //             'has_matches'      => false,
    //             'matched_claves'   => [],
    //             'unmatched_claves' => [],
    //             'catalog_records'  => [],
    //         ];
    //     }

    //     $encontrados = RepseCatalog::whereIn('clavepProdServ', $claves)->get();
    //     $matchedClaves = $encontrados->pluck('clavepProdServ')->toArray();
    //     $unmatchedClaves = array_values(array_diff($claves, $matchedClaves));

    //     return [
    //         'has_matches'      => count($matchedClaves) > 0,
    //         'matched_claves'   => $matchedClaves,
    //         'unmatched_claves' => $unmatchedClaves,
    //         'catalog_records'  => $encontrados->toArray(),
    //     ];
    // }

    // public function createValidatedInvoice(array $validatedData, int $clasificationId, int $supplierId)
    // {
    //     return DB::transaction(function () use ($validatedData, $clasificationId, $supplierId) {

    //         // 1. Bloqueamos el registro de la clasificación para escritura exclusiva
    //         $clasification = Clasification::where('id', $clasificationId)
    //             ->lockForUpdate()
    //             ->firstOrFail();

    //         // 2. Incrementamos el consecutivo de manera atómica
    //         $clasification->consecutive += 1;
    //         $clasification->save();

    //         // 3. Construimos el Track ID según tu formato exacto (Ej: 0843AA00000000000001)
    //         // air (4 chars) + serie (2 chars) + consecutivo rellenado con ceros a la izquierda (14 chars)
    //         $trackId = sprintf(
    //             '%s%s%s',
    //             $clasification->air,
    //             $clasification->serie,
    //             str_pad($clasification->consecutive, 14, '0', STR_PAD_LEFT)
    //         );

    //         // 4. Creamos la factura asegurando el track_id único
    //         $validatedData['clasification_id'] = $clasification->id;
    //         $validatedData['supplier_id'] = $supplierId;
    //         $validatedData['track_id'] = $trackId;
    //         $validatedData['status'] = 'Validada';

    //         return Invoice::create($validatedData);
    //     });
    // }
    // -----------------
    public function __construct(
        private readonly CfdiParserService $parserService,
        private readonly SatStatusService $satStatusService
    ) {}

    /**
     *
     * @return array<string, mixed>
     */
    public function processAndStoreUpload(UploadedFile $xmlFile, int $clasificationId, int $supplierId, array $requestData = []): array
    {
        $cfdiData = $this->parserService->parse($xmlFile);

        if (!in_array($cfdiData->tipoComprobante, ['I', 'P'], true)) {
            throw ValidationException::withMessages([
                'xml_file' => 'Solo se permiten comprobantes de tipo Ingreso (I) o Pago (P).',
            ]);
        }

        $repseValidation = $this->validateRepseCatalog($cfdiData->clavesProdServ);

        $satStatus = $this->satStatusService->getStatus($cfdiData);

        $isVigente = data_get($satStatus, 'consultaOk') === true && data_get($satStatus, 'estado') === 'Vigente';

        $invoice = null;

        if ($isVigente) {
            $invoice = $this->createValidatedInvoice($cfdiData, $repseValidation, $clasificationId, $supplierId, $requestData);
        }

        return [
            'repse_validation' => $repseValidation,
            'sat_status'       => $satStatus,
            'tipo_comprobante' => $cfdiData->tipoComprobante,
            'invoice'          => $invoice,
        ];
    }
    /**
     *
     * @param array<string> $claves
     * @return array<string, mixed>
     */
    private function validateRepseCatalog(array $claves): array
    {
        if (empty($claves)) {
            return [
                'has_matches'      => false,
                'matched_claves'   => [],
                'unmatched_claves' => [],
                'catalog_records'  => [],
            ];
        }

        $encontrados = RepseCatalog::whereIn('clavepProdServ', $claves)->get();
        $matchedClaves = $encontrados->pluck('clavepProdServ')->toArray();
        $unmatchedClaves = array_values(array_diff($claves, $matchedClaves));

        return [
            'has_matches'      => count($matchedClaves) > 0,
            'matched_claves'   => $matchedClaves,
            'unmatched_claves' => $unmatchedClaves,
            'catalog_records'  => $encontrados->toArray(),
        ];
    }

    public function createValidatedInvoice(CfdiDataDto $cfdiData, array $repseValidation, int $clasificationId, int $supplierId, array $requestData): array
    {
        return DB::transaction(function () use ($cfdiData, $repseValidation, $clasificationId, $supplierId, $requestData) {

            $clasification = Clasification::where('id', $clasificationId)
                ->lockForUpdate()
                ->firstOrFail();

            $clasification->consecutive += 1;
            $clasification->save();

            $trackId = sprintf(
                '%s%s%s',
                $clasification->air,
                $clasification->serie,
                str_pad($clasification->consecutive, 14, '0', STR_PAD_LEFT)
            );
            
            $invoiceData = array_merge($requestData, [
                'rfcEmisor'        => $cfdiData->rfcEmisor,
                'clasification_id' => $clasification->id,
                'supplier_id'      => $supplierId,
                'track_id'         => $trackId,
                'uuid'             => $cfdiData->uuid,
                'total'            => $cfdiData->total,
                'code'             => $cfdiData->exportacion,
                'serie'            => $cfdiData->serie ?? '',
                'folio'            => $cfdiData->folio ?? '',
                'payment_method'   => $cfdiData->metodoPago,
                'payment_form'     => $cfdiData->formaPago,
                'currency'         => $cfdiData->moneda,
                'exchange_rate'    => $cfdiData->tipoCambio,
                'date'             => $cfdiData->fecha,
                'status_sat'       => true,
                'status'           => 'Vigente',
                'is_masive'        => false,
            ]);

            $invoice = Invoice::create($invoiceData)->load(['supplier' => function ($query) {
                $query->select('id', 'rfc');
            }, 'clasification' => function ($query) {
                $query->select('id', DB::raw("CONCAT(location, ' - ', air) as division"));
            }]);

            $data = $invoice->toArray();

            $data['supplier_rfc'] = $invoice->supplier->rfc ?? null;
            $data['division'] = $invoice->clasification->division ?? null;

            // 3. Eliminamos las llaves de los objetos anidados
            unset($data['supplier'], $data['clasification']);
            
            return $data;
        });
    }
}
