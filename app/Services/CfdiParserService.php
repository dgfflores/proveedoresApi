<?php

namespace App\Services;

use App\DTOs\CfdiDataDto;
use Illuminate\Http\UploadedFile;
use SimpleXMLElement;
use Exception;

class CfdiParserService
{
    /**
     *
     * @throws Exception
     */
    public function parse(UploadedFile $file): CfdiDataDto
    {
        $xmlContent = file_get_contents($file->getRealPath());
        $xml = new SimpleXMLElement($xmlContent);

        $namespaces = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('cfdi', $namespaces['cfdi'] ?? 'http://www.sat.gob.mx/cfd/4');
        $xml->registerXPathNamespace('tfd', $namespaces['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital');

        $timbre = $xml->xpath('//tfd:TimbreFiscalDigital');
        $uuid = !empty($timbre) ? (string) $timbre[0]->attributes()['UUID'] : null;

        $attributes = $xml->attributes();
        $emisor = $xml->xpath('//cfdi:Emisor');
        $receptor = $xml->xpath('//cfdi:Receptor');

        $rfcEmisor = !empty($emisor) ? (string) $emisor[0]->attributes()['Rfc'] : null;
        $rfcReceptor = !empty($receptor) ? (string) $receptor[0]->attributes()['Rfc'] : null;

        $total = isset($attributes['Total']) ? (string) $attributes['Total'] : '0.00';
        $tipoComprobante = isset($attributes['TipoDeComprobante']) ? (string) $attributes['TipoDeComprobante'] : null;

        $serie = isset($attributes['Serie']) ? (string) $attributes['Serie'] : '';
        $folio = isset($attributes['Folio']) ? (string) $attributes['Folio'] : '';
        $metodoPago = isset($attributes['MetodoPago']) ? (string) $attributes['MetodoPago'] : 'PUE';
        $formaPago = isset($attributes['FormaPago']) ? (string) $attributes['FormaPago'] : '99';
        $moneda = isset($attributes['Moneda']) ? (string) $attributes['Moneda'] : 'MXN';
        $tipoCambio = isset($attributes['TipoCambio']) ? (string) $attributes['TipoCambio'] : '1';
        $fecha = isset($attributes['Fecha']) ? (string) $attributes['Fecha'] : now();
        $exportacion = isset($attributes['Exportacion']) ? (string) $attributes['Exportacion'] : '01';

        // Extraer Claves de Producto/Servicio
        $clavesProdServ = [];
        $conceptos = $xml->xpath('//cfdi:Concepto');

        foreach ($conceptos as $concepto) {
            $atributos = $concepto->attributes();
            if (isset($atributos['ClaveProdServ'])) {
                $clavesProdServ[] = (string) $atributos['ClaveProdServ'];
            }
        }

        return new CfdiDataDto(
            uuid: $uuid,
            rfcEmisor: $rfcEmisor,
            rfcReceptor: $rfcReceptor,
            tipoComprobante: $tipoComprobante,
            total: $total,
            serie: $serie,
            folio: $folio,
            metodoPago: $metodoPago,
            formaPago: $formaPago,
            moneda: $moneda,
            tipoCambio: $tipoCambio,
            fecha: $fecha,
            exportacion: $exportacion,
            clavesProdServ: array_unique($clavesProdServ)
        );
    }
}
