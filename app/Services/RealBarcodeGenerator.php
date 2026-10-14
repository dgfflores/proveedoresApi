<?php

namespace App\Services;

use App\Contracts\BarcodeGeneratorInterface;
use Milon\Barcode\DNS1D;
class RealBarcodeGenerator implements BarcodeGeneratorInterface
{

    private DNS1D $barcodeService;

    public function __construct()
    {
        $this->barcodeService = new DNS1D();
    }

    public function generateBarcodeSvg(string $code, string $type, int $width, int $height): string
    {
        return base64_encode($this->barcodeService->getBarcodeSVG($code, $type, $width, $height));
    }
}
