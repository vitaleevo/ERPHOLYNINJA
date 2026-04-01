<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalTemplate extends Model
{
    protected $fillable = [
        'clinic_id',
        'created_by',
        'name',
        'specialty',
        'type',
        'structure',
        'common_diagnoses',
        'default_medications',
        'is_global',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'structure' => 'array',
        'common_diagnoses' => 'array',
        'default_medications' => 'array',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Incrementar contador de uso
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
