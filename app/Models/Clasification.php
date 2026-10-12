<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clasification extends Model
{
    protected $table = 'clasifications';
    
    protected $fillable = ['company_id', 'location', 'air', 'is_active', 'consecutive', 'serie'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

}


