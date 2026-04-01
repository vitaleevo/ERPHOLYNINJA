<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabTestProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'name',
        'code',
        'description',
        'total_price',
        'is_discountable',
        'discount_percentage',
        'is_active',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'is_discountable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function labTests(): BelongsToMany
    {
        return $this->belongsToMany(LabTest::class, 'lab_profile_tests', 'profile_id', 'test_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(LabRequestItem::class, 'profile_id');
    }
}
