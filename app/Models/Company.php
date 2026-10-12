<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'rfc',
        'bussiness_name',
        'alias',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function clasifications(): HasMany
    {
        return $this->hasMany(Clasification::class);
    }
}
