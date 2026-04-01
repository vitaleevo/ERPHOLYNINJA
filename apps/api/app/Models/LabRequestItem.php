<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabRequestItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_id',
        'test_id',
        'profile_id',
        'status',
        'technician_notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class, 'request_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(LabTestProfile::class, 'profile_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class, 'request_item_id');
    }
}
