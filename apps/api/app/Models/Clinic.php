<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'nif',
        'logo',
        'status',
        'plan',
    ];

    protected $casts = [
        'status' => 'string',
        'plan' => 'string',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function specialties(): HasMany
    {
        return $this->hasMany(Specialty::class);
    }
}
