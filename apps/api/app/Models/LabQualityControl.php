<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabQualityControl extends Model
{
    protected $fillable = [
        'clinic_id',
        'test_id',
        'equipment_id',
        'control_name',
        'control_level',
        'lot_number',
        'expiry_date',
        'target_value',
        'sd',
        'measured_value',
        'is_acceptable',
        'comments',
        'run_datetime',
        'technician_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'target_value' => 'decimal:2',
        'sd' => 'decimal:4',
        'measured_value' => 'decimal:2',
        'is_acceptable' => 'boolean',
        'run_datetime' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(LabEquipment::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Verifica se o resultado está dentro do intervalo aceitável
     */
    public function isWithinRange(): bool
    {
        $lowerLimit = $this->target_value - (2 * $this->sd);
        $upperLimit = $this->target_value + (2 * $this->sd);

        return $this->measured_value >= $lowerLimit && $this->measured_value <= $upperLimit;
    }

    /**
     * Calcula o valor Z-score
     */
    public function getZScoreAttribute(): float
    {
        if ($this->sd == 0) {
            return 0;
        }

        return ($this->measured_value - $this->target_value) / $this->sd;
    }

    /**
     * Verifica se o controle expirou
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
