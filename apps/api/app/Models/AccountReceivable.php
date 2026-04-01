<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountReceivable extends Model
{
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'description',
        'amount',
        'due_date',
        'payment_date',
        'status',
        'payment_method',
        'reference',
        'observations',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'status' => 'string',
        'payment_method' => 'string',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Verificar se está em atraso
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date < now();
    }

    /**
     * Marcar como recebido
     */
    public function markAsReceived(string $paymentDate = null, string $paymentMethod = null): void
    {
        $this->update([
            'status' => 'received',
            'payment_date' => $paymentDate ?? now(),
            'payment_method' => $paymentMethod ?? $this->payment_method,
        ]);
    }

    /**
     * Cancelar recebimento
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
