<?php

namespace App\Modules\Pharmacy\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacySaleItemModel extends Model
{
    protected $table = 'pharmacy_sale_items';

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
        return $this->belongsTo(PharmacySaleModel::class, 'pharmacy_sale_id');
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Medication::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MedicationBatch::class, 'medication_batch_id');
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
