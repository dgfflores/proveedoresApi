<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface DocumentStorageInterface
{
    public function storeXml(UploadedFile $file, string $folderPath, string $nombreArchivo, string $disk): string;
    public function storePdf(UploadedFile $file, string $folderPath, string $nombreArchivo, string $disk): string;
    public function storeMultiplePdfs(array $files, string $folderPath, string $nombreArchivo, string $disk): array;
}
