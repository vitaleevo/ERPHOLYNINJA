<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabEquipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'name',
        'model',
        'manufacturer',
        'serial_number',
        'asset_tag',
        'status',
        'purchase_date',
        'warranty_expiry',
        'calibration_date',
        'next_calibration_due',
        'specifications',
        'maintenance_notes',
        'requires_calibration',
        'calibration_interval_days',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'calibration_date' => 'date',
        'next_calibration_due' => 'date',
        'requires_calibration' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function labTests(): HasMany
    {
        return $this->hasMany(LabTest::class, 'equipment_id');
    }

    public function qualityControls(): HasMany
    {
        return $this->hasMany(LabQualityControl::class);
    }

    /**
     * Verifica se o equipamento precisa de calibração
     */
    public function needsCalibration(): bool
    {
        if (!$this->requires_calibration) {
            return false;
        }

        if (!$this->next_calibration_due) {
            return true;
        }

        return $this->next_calibration_due->isPast();
    }

    /**
     * Obtém o status para exibição
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'active' => 'Ativo',
            'maintenance' => 'Em Manutenção',
            'inactive' => 'Inativo',
            'broken' => 'Avariado',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
