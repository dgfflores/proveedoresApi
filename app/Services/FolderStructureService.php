<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;

class FolderStructureService
{
    private Filesystem $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    public function generateMonthlyStructure(): void
    {
        $fechaActual = Carbon::now();
        $anioActual = $fechaActual->year;
        $anioSiguiente = $fechaActual->copy()->addYear()->year;

        $listaAnios = [$anioActual, $anioSiguiente];

        foreach (range(1, 5) as $empresaId) {
            foreach ($listaAnios as $anio) {
                $mesInicio = ($anio === $anioActual) ? $fechaActual->month : 1;
                for ($m = $mesInicio; $m <= 12; $m++) {
                    $mesFormateado = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $rutaCarpeta = "{$empresaId}/{$anio}/{$mesFormateado}";
                    $this->storage->makeDirectory($rutaCarpeta);
                }
            }
        }
    }
}
