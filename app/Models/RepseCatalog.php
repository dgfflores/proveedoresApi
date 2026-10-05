<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepseCatalog extends Model
{
    protected $table = 'repse_catalogs';
    
    protected $fillable = [
        'clavepProdServ',
        'descripcion',
        'status',
    ];
    
    protected $casts = [
        'status' => 'boolean',
    ];
}
