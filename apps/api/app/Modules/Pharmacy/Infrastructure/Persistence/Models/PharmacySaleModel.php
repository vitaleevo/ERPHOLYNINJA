<?php

namespace App\Modules\Pharmacy\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Exception;

class PharmacySaleModel extends Model
{
    protected $table = 'pharmacy_sales';

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'prescription_id',
        'user_id',
        'invoice_number',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'status',
        'observations',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_method' => 'string',
        'status' => 'string',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Clinic::class, 'clinic_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Patient::class, 'patient_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Prescription::class, 'prescription_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PharmacySaleItemModel::class, 'sale_id');
    }

    /**
     * Gerar número da fatura automaticamente
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'FARM';
        $year = date('Y');
        $month = date('m');
        
        $lastSale = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastSale ? intval(substr($lastSale->invoice_number, -6)) + 1 : 1;
        
        return "{$prefix}/{$year}{$month}/" . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular totais a partir dos itens
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('total');
        $discount = $this->discount ?? 0;
        $total = $subtotal - $discount;
        
        $this->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    /**
     * Cancelar venda e retornar estoque
     */
    public function cancel(): void
    {
        if ($this->status === 'cancelled') {
            throw new Exception('Venda já está cancelada');
        }

        // Retornar itens ao estoque
        foreach ($this->items as $item) {
            if (method_exists($item->batch, 'updateQuantity')) {
                $item->batch->updateQuantity($item->quantity);
            }
        }

        $this->update(['status' => 'cancelled']);
    }
}
