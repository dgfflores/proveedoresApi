<?php

namespace App\Contracts;

interface PdfGeneratorInterface
{
    public function stream(string $view, array $data, string $filename);

    public function save(string $view, array $data, string $outputPath): string;
}
