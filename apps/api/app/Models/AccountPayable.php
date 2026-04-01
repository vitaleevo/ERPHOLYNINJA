<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountPayable extends Model
{
    protected $fillable = [
        'clinic_id',
        'description',
        'amount',
        'due_date',
        'payment_date',
        'status',
        'category',
        'supplier',
        'observations',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'status' => 'string',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
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
     * Marcar como pago
     */
    public function markAsPaid(string $paymentDate = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => $paymentDate ?? now(),
        ]);
    }

    /**
     * Cancelar conta
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
