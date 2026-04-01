<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'specialty_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
        'room',
        'is_telemedicine',
        'video_call_link',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'status' => 'string',
        'duration_minutes' => 'integer',
        'is_telemedicine' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AppointmentLog::class);
    }

    public function telemedicineSession(): HasOne
    {
        return $this->hasOne(TelemedicineSession::class);
    }

    /**
     * Verificar se é telemedicina
     */
    public function isTelemedicine(): bool
    {
        return $this->is_telemedicine === true;
    }

    /**
     * Converter para telemedicina
     */
    public function convertToTelemedicine(string $videoCallLink = null): void
    {
        $this->update([
            'is_telemedicine' => true,
            'video_call_link' => $videoCallLink,
            'room' => null, // Remove sala física
        ]);
    }

    /**
     * Converter para presencial
     */
    public function convertToInPerson(string $room = null): void
    {
        $this->update([
            'is_telemedicine' => false,
            'video_call_link' => null,
            'room' => $room,
        ]);
    }
}
