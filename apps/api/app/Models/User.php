<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'clinic_id',
        'name',
        'email',
        'password',
        'role',
        'specialty_id',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'role' => 'string',
    ];

    /**
     * Obter a clínica do usuário
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Obter especialidade do usuário
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Verificar se é médico
     */
    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    /**
     * Verificar se é admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verificar se é recepcionista
     */
    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    /**
     * Verificar se é staff (admin ou receptionist)
     */
    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'receptionist']);
    }
}
