<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $fillable = [
        'rfc',
        'bussiness_name',
        'status_efos',
        'is_active',
        'is_repse',
        'load_invoice',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_repse' => 'boolean',
        'load_invoice' => 'boolean',
    ];
}
