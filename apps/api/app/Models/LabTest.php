<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'category_id',
        'name',
        'code',
        'description',
        'unit_of_measurement',
        'min_reference_value',
        'max_reference_value',
        'panic_min_value',
        'panic_max_value',
        'sample_type',
        'turnaround_time_hours',
        'price',
        'requires_equipment',
        'equipment_id',
        'preparation_instructions',
        'notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_reference_value' => 'decimal:2',
        'max_reference_value' => 'decimal:2',
        'panic_min_value' => 'decimal:2',
        'panic_max_value' => 'decimal:2',
        'price' => 'decimal:2',
        'requires_equipment' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LabTestCategory::class, 'category_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(LabEquipment::class, 'equipment_id');
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(LabRequestItem::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class);
    }

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(LabTestProfile::class, 'lab_profile_tests', 'test_id', 'profile_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function qualityControls(): HasMany
    {
        return $this->hasMany(LabQualityControl::class);
    }

    /**
     * Verifica se o valor é crítico (panic value)
     */
    public function isCriticalValue(float $value): bool
    {
        if ($this->panic_min_value && $value < $this->panic_min_value) {
            return true;
        }
        if ($this->panic_max_value && $value > $this->panic_max_value) {
            return true;
        }
        return false;
    }

    /**
     * Verifica se o valor está fora do intervalo de referência
     */
    public function isAbnormalValue(float $value): bool
    {
        if ($this->min_reference_value && $value < $this->min_reference_value) {
            return true;
        }
        if ($this->max_reference_value && $value > $this->max_reference_value) {
            return true;
        }
        return false;
    }

    /**
     * Obtém a flag de anormalidade
     */
    public function getAbnormalFlag(float $value): ?string
    {
        if (!$this->min_reference_value && !$this->max_reference_value) {
            return null;
        }

        if ($this->panic_min_value && $value < $this->panic_min_value) {
            return 'LL'; // Very low
        }
        if ($this->panic_max_value && $value > $this->panic_max_value) {
            return 'HH'; // Very high
        }
        if ($this->min_reference_value && $value < $this->min_reference_value) {
            return 'L'; // Low
        }
        if ($this->max_reference_value && $value > $this->max_reference_value) {
            return 'H'; // High
        }

        return null;
    }
}
