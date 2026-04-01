<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationBatch extends Model
{
    protected $fillable = [
        'medication_id',
        'clinic_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'initial_quantity',
        'current_quantity',
        'cost_price',
        'sale_price',
        'storage_location',
        'status',
        'observations',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'status' => 'string',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(PharmacyStock::class, 'batch_id');
    }

    /**
     * Verificar se está vencido
     */
    public function isExpired(): bool
    {
        return $this->expiry_date < now();
    }

    /**
     * Verificar se vence em breve (dias)
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date <= now()->addDays($days) && !$this->isExpired();
    }

    /**
     * Verificar se está esgotado
     */
    public function isDepleted(): bool
    {
        return $this->current_quantity <= 0;
    }

    /**
     * Atualizar quantidade do lote
     */
    public function updateQuantity(int $quantity): void
    {
        $newQuantity = $this->current_quantity + $quantity;
        
        if ($newQuantity < 0) {
            throw new \Exception('Quantidade insuficiente no lote');
        }

        $this->update([
            'current_quantity' => $newQuantity,
            'status' => $newQuantity <= 0 ? 'depleted' : ($this->isExpired() ? 'expired' : 'active'),
        ]);
    }
}
