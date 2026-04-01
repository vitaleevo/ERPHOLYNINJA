<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consultation extends Model
{
    protected $fillable = [
        'clinic_id',
        'appointment_id',
        'patient_id',
        'doctor_id',
        'started_at',
        'ended_at',
        'chief_complaint',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'symptoms',
        'diagnosis',
        'icd10_codes',
        'primary_diagnosis_code',
        'template_id',
        'observations',
        'status',
        'is_signed',
        'signed_at',
        'digital_signature_hash',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => 'string',
        'icd10_codes' => 'array',
        'is_signed' => 'boolean',
        'signed_at' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ClinicalTemplate::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Assinar digitalmente a consulta
     */
    public function signDigital(): void
    {
        $hashData = [
            'consultation_id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'timestamp' => now()->toISOString(),
            'content' => $this->subjective . $this->objective . $this->assessment . $this->plan,
        ];
        
        $this->update([
            'is_signed' => true,
            'signed_at' => now(),
            'digital_signature_hash' => hash('sha256', json_encode($hashData)),
        ]);
    }

    /**
     * Adicionar código CID-10
     */
    public function addICD10Code(string $code): void
    {
        $codes = $this->icd10_codes ?? [];
        
        if (!in_array($code, $codes)) {
            $codes[] = $code;
            $this->update(['icd10_codes' => $codes]);
        }
    }

    /**
     * Verificar se está assinada
     */
    public function isSigned(): bool
    {
        return $this->is_signed;
    }
}
