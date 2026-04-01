<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TelemedicineSession extends Model
{
    protected $fillable = [
        'clinic_id',
        'appointment_id',
        'doctor_id',
        'patient_id',
        'session_id',
        'meeting_url',
        'moderator_password',
        'attendee_password',
        'status',
        'started_at',
        'ended_at',
        'duration_minutes',
        'recording_url',
        'settings',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'settings' => 'array',
        'is_telemedicine' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->session_id)) {
                $session->session_id = Str::uuid();
            }
        });
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(TelemedicineChat::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TelemedicineFile::class);
    }

    /**
     * Gerar senha aleatória para a reunião
     */
    public static function generatePassword(): string
    {
        return substr(Str::random(8), 0, 6);
    }

    /**
     * Iniciar sessão
     */
    public function start(): void
    {
        if ($this->status !== 'scheduled') {
            throw new \Exception('Sessão não está agendada');
        }

        $this->update([
            'status' => 'started',
            'started_at' => now(),
        ]);
    }

    /**
     * Encerrar sessão
     */
    public function end(?string $recordingUrl = null): void
    {
        if ($this->status !== 'started') {
            throw new \Exception('Sessão não está em andamento');
        }

        $this->update([
            'status' => 'ended',
            'ended_at' => now(),
            'recording_url' => $recordingUrl,
        ]);
    }

    /**
     * Cancelar sessão
     */
    public function cancel(): void
    {
        if (in_array($this->status, ['ended', 'cancelled'])) {
            throw new \Exception('Sessão não pode ser cancelada');
        }

        $this->update(['status' => 'cancelled']);
    }

    /**
     * Verificar se sessão está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'started';
    }

    /**
     * Obter link da reunião
     */
    public function getMeetingUrl(): string
    {
        return $this->meeting_url ?? '';
    }

    /**
     * Obter configurações da sessão
     */
    public function getSettings(): array
    {
        return $this->settings ?? [];
    }
}
