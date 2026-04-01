<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacySaleItem extends Model
{
    protected $fillable = [
        'pharmacy_sale_id',
        'medication_id',
        'medication_batch_id',
        'medication_name',
        'quantity',
        'unit_price',
        'discount',
        'total',
        'dosage',
        'instructions',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PharmacySale::class, 'pharmacy_sale_id');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicationBatch::class, 'medication_batch_id');
    }

    /**
     * Calcular total do item
     */
    public function calculateTotal(): void
    {
        $total = ($this->quantity * $this->unit_price) - $this->discount;
        $this->update(['total' => $total]);
    }
}
