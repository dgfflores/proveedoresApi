<?php
namespace App\Services;

use App\DTOs\CfdiDataDto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SatStatusService
{
    /**
     * Consulta el estado fiscal de un CFDI en el Backend Core.
     *
     * @return array<string, mixed>|null
     */
    public function getStatus(CfdiDataDto $cfdi): ?array
    {
        if (!$cfdi->uuid || !$cfdi->rfcEmisor || !$cfdi->rfcReceptor) {
            return null;
        }

        try {
            $response = Http::baseUrl(config('services.core_api.url'))
                ->withBasicAuth(
                    config('services.core_api.user'),
                    config('services.core_api.password')
                )
                ->withHeaders([
                    'contrato' => config('services.core_api.contrato'),
                    'Accept'   => 'application/json',
                ])
                ->timeout(30)
                ->post('/invoices/status', [
                    'comprobantes' => [[
                        'uuid'        => $cfdi->uuid,
                        'rfcEmisor'   => $cfdi->rfcEmisor,
                        'rfcReceptor' => $cfdi->rfcReceptor,
                        'total'       => $cfdi->total,
                    ]]
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Error consultando el estado del CFDI en Backend B:', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error('Excepción al conectar con servicio SAT:', ['error' => $e->getMessage()]);
        }

        return [
            'success' => false,
            'message' => 'No se pudo obtener el estado fiscal del comprobante.'
        ];
    }
}