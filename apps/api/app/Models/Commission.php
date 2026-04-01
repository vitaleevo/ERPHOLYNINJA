<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'consultation_id',
        'amount',
        'percentage',
        'reference_month',
        'status',
        'payment_date',
        'observations',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'reference_month' => 'date',
        'payment_date' => 'date',
        'status' => 'string',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Calcular comissão baseado na consulta
     */
    public static function calculateFromConsultation(Consultation $consultation, float $percentage): float
    {
        $consultationValue = $consultation->value ?? 0;
        return ($consultationValue * $percentage) / 100;
    }

    /**
     * Marcar comissão como paga
     */
    public function markAsPaid(string $paymentDate = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => $paymentDate ?? now(),
        ]);
    }

    /**
     * Cancelar comissão
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
