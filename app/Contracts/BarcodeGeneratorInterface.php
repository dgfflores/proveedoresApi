<?php

namespace App\Contracts;

interface BarcodeGeneratorInterface
{
    public function generateBarcodeSvg(string $code, string $type, int $width, int $height): string;
    
}
