<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    protected $fillable = [
        'clinic_id',
        'name',
        'generic_name',
        'brand',
        'dosage',
        'form',
        'route',
        'composition',
        'manufacturer',
        'registration_number',
        'requires_prescription',
        'requires_special_control',
        'indications',
        'contraindications',
        'side_effects',
        'reference_price',
        'min_stock_alert',
        'is_active',
    ];

    protected $casts = [
        'requires_prescription' => 'boolean',
        'requires_special_control' => 'boolean',
        'is_active' => 'boolean',
        'reference_price' => 'decimal:2',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicationBatch::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(PharmacyStock::class);
    }

    /**
     * Obter estoque total disponível
     */
    public function getTotalStock(): int
    {
        return $this->batches()
            ->where('status', 'active')
            ->sum('current_quantity');
    }

    /**
     * Verificar se está com estoque baixo
     */
    public function isLowStock(int $threshold = 10): bool
    {
        return $this->getTotalStock() < $threshold;
    }

    /**
     * Obter lote mais próximo do vencimento
     */
    public function getExpiringBatch(): ?MedicationBatch
    {
        return $this->batches()
            ->where('status', 'active')
            ->where('current_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->first();
    }

    /**
     * Verificar se possui algum lote vencido
     */
    public function hasExpiredBatches(): bool
    {
        return $this->batches()
            ->where('expiry_date', '<', now())
            ->exists();
    }

    /**
     * Gerar alertas para este medicamento
     */
    public function generateAlerts(): void
    {
        $totalStock = $this->getTotalStock();
        
        // Alerta de stock baixo
        if ($totalStock < $this->min_stock_alert) {
            $severity = $totalStock === 0 ? 'critical' : 'high';
            
            foreach ($this->batches as $batch) {
                PharmacyAlert::firstOrCreate([
                    'clinic_id' => $this->clinic_id,
                    'medication_id' => $this->id,
                    'batch_id' => $batch->id,
                    'type' => $totalStock === 0 ? 'out_of_stock' : 'low_stock',
                ], [
                    'severity' => $severity,
                    'message' => $totalStock === 0 
                        ? "Medicamento {$this->name} em falta"
                        : "Medicamento {$this->name} com estoque baixo ({$totalStock} unidades)",
                    'quantity_current' => $totalStock,
                    'quantity_threshold' => $this->min_stock_alert,
                ]);
            }
        }
        
        // Alerta de validade próxima
        foreach ($this->batches as $batch) {
            $daysUntilExpiry = now()->diffInDays($batch->expiry_date, false);
            
            if ($daysUntilExpiry <= 90 && $batch->current_quantity > 0) {
                $severity = $daysUntilExpiry <= 30 ? 'critical' : 'high';
                
                PharmacyAlert::firstOrCreate([
                    'clinic_id' => $this->clinic_id,
                    'medication_id' => $this->id,
                    'batch_id' => $batch->id,
                    'type' => 'expiry',
                ], [
                    'severity' => $severity,
                    'message' => "Lote {$batch->batch_number} vence em {$daysUntilExpiry} dias",
                    'expiry_date' => $batch->expiry_date,
                    'days_until_expiry' => $daysUntilExpiry,
                ]);
            }
        }
    }
}
