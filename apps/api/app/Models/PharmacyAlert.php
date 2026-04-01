<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyAlert extends Model
{
    protected $fillable = [
        'clinic_id',
        'medication_id',
        'batch_id',
        'type',
        'severity',
        'message',
        'quantity_current',
        'quantity_threshold',
        'expiry_date',
        'days_until_expiry',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'quantity_current' => 'integer',
        'quantity_threshold' => 'integer',
        'expiry_date' => 'date',
        'days_until_expiry' => 'integer',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
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
        return $this->belongsTo(MedicationBatch::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Verificar se é alerta crítico
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    /**
     * Resolver alerta
     */
    public function resolve(int $userId): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);
    }

    /**
     * Escopo para alertas não resolvidos
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Escopo para alertas de stock baixo
     */
    public function scopeLowStock($query)
    {
        return $query->where('type', 'low_stock');
    }

    /**
     * Escopo para alertas de validade
     */
    public function scopeExpiry($query)
    {
        return $query->where('type', 'expiry');
    }
}
