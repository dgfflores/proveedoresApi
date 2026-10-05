<?php
namespace App\Services;

use App\DTOs\CfdiDataDto;
use App\Models\RepseCatalog;
use Illuminate\Http\UploadedFile;

class InvoiceService
{
    public function __construct(
        private readonly CfdiParserService $parserService,
        private readonly SatStatusService $satStatusService
    ) {}

    /**
     * Procesa la subida y validación completa de una factura.
     *
     * @return array<string, mixed>
     */
    public function processUpload(UploadedFile $xmlFile): array
    {
        // 1. Parsear el archivo XML
        $cfdiData = $this->parserService->parse($xmlFile);

        // 2. Validar contra el catálogo REPSE
        $repseValidation = $this->validateRepseCatalog($cfdiData->clavesProdServ);

        // 3. Consultar estatus del SAT
        $satStatus = $this->satStatusService->getStatus($cfdiData);

        return [
            'repse_validation' => $repseValidation,
            'sat_status'       => $satStatus,
        ];
    }

    /**
     * Valida las claves de producto/servicio contra el catálogo REPSE.
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
}