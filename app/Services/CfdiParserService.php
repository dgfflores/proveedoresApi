<?php 

namespace App\Services;

use App\DTOs\CfdiDataDto;
use Illuminate\Http\UploadedFile;
use SimpleXMLElement;
use Exception;

class CfdiParserService
{
    /**
     * Extrae la información esencial de un archivo XML CFDI 4.0.
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

        // Extraer UUID
        $timbre = $xml->xpath('//tfd:TimbreFiscalDigital');
        $uuid = !empty($timbre) ? (string) $timbre[0]->attributes()['UUID'] : null;

        // Extraer Atributos Principales
        $attributes = $xml->attributes();
        $emisor = $xml->xpath('//cfdi:Emisor');
        $receptor = $xml->xpath('//cfdi:Receptor');

        $rfcEmisor = !empty($emisor) ? (string) $emisor[0]->attributes()['Rfc'] : null;
        $rfcReceptor = !empty($receptor) ? (string) $receptor[0]->attributes()['Rfc'] : null;
        $total = isset($attributes['Total']) ? (string) $attributes['Total'] : '0.00';

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
            total: $total,
            clavesProdServ: array_unique($clavesProdServ)
        );
    }
}