<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTestCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'name',
        'code',
        'description',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function labTests(): HasMany
    {
        return $this->hasMany(LabTest::class, 'category_id');
    }

    public function activeLabTests(): HasMany
    {
        return $this->hasMany(LabTest::class, 'category_id')->where('is_active', true);
    }
}
