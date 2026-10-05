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
        public string $total,
        public array $clavesProdServ
    ) {}
}