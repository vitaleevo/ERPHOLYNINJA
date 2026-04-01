<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'clinic_id',
        'patient_id',
        'invoice_number',
        'series',
        'sequential_number',
        'total_amount',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'withholding_tax',
        'status',
        'issue_date',
        'due_date',
        'observations',
        'created_by',
        'invoice_type',
        'at_code',
        'at_hash',
        'at_datetime',
        'agt_status',
        'agt_response',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'at_datetime' => 'datetime',
        'status' => 'string',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Gerar número da fatura automaticamente
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'FAT';
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastInvoice ? intval(substr($lastInvoice->invoice_number, -6)) + 1 : 1;
        
        return "{$prefix}/{$year}{$month}/" . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular total a partir dos itens
     */
    public function calculateTotal(): void
    {
        $total = $this->items()->sum('total_price');
        $this->update(['total_amount' => $total]);
    }

    /**
     * Marcar fatura como emitida
     */
    public function issue(): void
    {
        $this->update(['status' => 'issued']);
    }

    /**
     * Marcar fatura como paga
     */
    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    /**
     * Cancelar fatura
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Calcular IVA (14% Angola)
     */
    public function calculateVAT(): void
    {
        $subtotal = $this->items()->sum('total_price');
        $vatAmount = $subtotal * ($this->vat_rate / 100);
        $total = $subtotal + $vatAmount - $this->withholding_tax;
        
        $this->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $total,
        ]);
    }

    /**
     * Gerar numeração fiscal conforme AGT
     */
    public static function generateFiscalNumber(): array
    {
        $year = date('Y');
        $month = date('m');
        $series = $year;
        
        $lastInvoice = self::where('series', $series)
            ->orderBy('sequential_number', 'desc')
            ->first();
        
        $sequentialNumber = $lastInvoice ? intval($lastInvoice->sequential_number) + 1 : 1;
        
        return [
            'series' => $series,
            'sequential_number' => str_pad($sequentialNumber, 6, '0', STR_PAD_LEFT),
            'full_number' => "{$series}/{$sequentialNumber}",
        ];
    }

    /**
     * Submeter à AGT (e-Factura)
     */
    public function submitToAGT(): bool
    {
        // Implementação da comunicação com AGT
        // Isso seria feito via API da AGT
        return true;
    }

    /**
     * Verificar se fatura está conforme AGT
     */
    public function isAGTCompliant(): bool
    {
        return $this->agt_status === 'accepted' 
            && $this->at_code !== null 
            && $this->at_hash !== null;
    }
}
