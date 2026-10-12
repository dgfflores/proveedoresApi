<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Clasification;
use Illuminate\Http\Request;

class ClasificationController extends Controller
{
    public function index()
    {
        $rows = Clasification::query()
            ->join('companies as c', 'c.id', '=', 'clasifications.company_id')
            ->orderBy('c.alias')
            ->orderBy('clasifications.location')
            ->where('clasifications.is_active', true)
            ->get([
                'clasifications.id',
                'clasifications.company_id',
                'c.alias',
                'c.bussiness_name',
                'c.rfc',
                'clasifications.location',
                'clasifications.air',
                'clasifications.consecutive',
                'clasifications.is_active',
            ]);

        return ApiResponse::success($rows, 'Clasificaciones obtenidas exitosamente');
    }
}
