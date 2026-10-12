<?php

namespace App\DTOs;

readonly class CfdiDataDto
{
    /**
     * @param array<string> $clavesProdServ
     */
    public function __construct(
        public ?string $uuid,
        public ?string $rfcEmisor,
        public ?string $rfcReceptor,
        public ?string $tipoComprobante,
        public string $total,
        public ?string $serie,
        public ?string $folio,
        public ?string $metodoPago,
        public ?string $formaPago,
        public ?string $moneda,
        public ?string $tipoCambio,
        public ?string $fecha,
        public ?string $exportacion,
        public array $clavesProdServ
    ) {}
}
