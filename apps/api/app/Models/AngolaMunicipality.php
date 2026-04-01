<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AngolaMunicipality extends Model
{
    protected $fillable = [
        'province_id',
        'name',
        'code',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(AngolaProvince::class);
    }
}
