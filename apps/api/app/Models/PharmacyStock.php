<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyStock extends Model
{
    protected $fillable = [
        'clinic_id',
        'medication_id',
        'batch_id',
        'user_id',
        'type',
        'quantity',
        'balance_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_after' => 'integer',
        'unit_cost' => 'decimal:2',
        'reference_id' => 'integer',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicationBatch::class, 'batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Escopo para entradas
     */
    public function scopeEntries($query)
    {
        return $query->where('type', 'entry');
    }

    /**
     * Escopo para saídas
     */
    public function scopeExits($query)
    {
        return $query->where('type', 'exit');
    }

    /**
     * Escopo para ajustes
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }
}
