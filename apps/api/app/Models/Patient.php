<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'clinic_id',
        'name',
        'email',
        'phone',
        'nif',
        'bi_number',
        'document_type',
        'document_number',
        'birth_date',
        'gender',
        'blood_type',
        'rh_factor',
        'address',
        'province',
        'municipality',
        'district',
        'allergies',
        'medical_history',
        'emergency_contact',
        'emergency_phone',
        'insurance_id',
        'insurance_number',
        'is_insured',
        'patient_card_number',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'status' => 'string',
        'gender' => 'string',
        'blood_type' => 'string',
        'is_insured' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Insurance::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
}
