<?php

namespace App\Traits;

use Carbon\Carbon;

trait DateHelpers {
    
    public function getYearMonthPath(): string
    {
        $fechaActual = Carbon::now();
        $anioActual = $fechaActual->year;
        $mesActual = $fechaActual->month;
        
        return "/{$anioActual}/{$mesActual}/";
    }
}
