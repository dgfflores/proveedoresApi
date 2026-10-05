<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $fillable = [
        'supplier_id',
        'track_id',
        'code',
        'serie',
        'folio',
        'payment_method',
        'payment_form',
        'currency',
        'exchange_rate',
        'total',
        'uuid',
        'is_masive',
        'status_sat',
        'status',
        'date',
    ];
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    protected $casts = [
        'is_masive' => 'boolean',
        'status_sat' => 'boolean',
        'date' => 'date',
    ];
}
