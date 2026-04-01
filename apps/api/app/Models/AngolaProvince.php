<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AngolaProvince extends Model
{
    protected $fillable = [
        'name',
        'code',
        'capital',
        'latitude',
        'longitude',
    ];

    public function municipalities(): HasMany
    {
        return $this->hasMany(AngolaMunicipality::class);
    }
}
