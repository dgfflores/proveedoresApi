<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Contracts\BarcodeGeneratorInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;


class BarcodePdfService
{
    public function __construct(
        private BarcodeGeneratorInterface $barcodeGenerator
    ) {
    }

    public function generatePdf(array $data, string $path, string $filename, string $disk): string
    {
        
        $barcodeSvg = $this->barcodeGenerator->generateBarcodeSvg(
            code: $data['trackId'],
            type: 'C39',
            width: 200,
            height: 50
        );

        $data = Arr::add($data, 'barcode', $barcodeSvg);
    
        $pdf = Pdf::loadView('track.trackId', $data);
        $pdfContent = $pdf->output();
        
        // Guardar el PDF en el almacenamiento
        Storage::disk($disk)->put($path . '/' . $filename, $pdfContent);
        
        return $filename;
    }
}
