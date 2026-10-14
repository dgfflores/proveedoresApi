<?php

namespace App\Services;

use App\Contracts\DocumentStorageInterface;
use Illuminate\Http\UploadedFile;

class DocumentStorageService implements DocumentStorageInterface
{
    public function storeXml(UploadedFile $file, string $folderPath, string $nombreArchivo, string $disk): string
    {
        return $file->storeAs($folderPath, $nombreArchivo, $disk);
    }

    public function storePdf(UploadedFile $file, string $folderPath, string $nombreArchivo, string $disk): string
    {
        return $file->storeAs($folderPath, $nombreArchivo, $disk);
    }

    public function storeMultiplePdfs(array $files, string $folderPath, string $nombreArchivo, string $disk): array
    {
        $paths = [];

        foreach ($files as $index => $file) {
            $paths[] = $file->storeAs($folderPath, $nombreArchivo . '_' . $index . '.pdf', $disk);
        }

        return $paths;
    }
}