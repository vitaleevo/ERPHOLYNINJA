<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'consultation_id',
        'patient_id',
        'doctor_id',
        'technician_id',
        'validator_id',
        'accession_number',
        'barcode',
        'priority',
        'status',
        'rejection_reason',
        'collection_datetime',
        'received_at',
        'started_at',
        'completed_at',
        'validated_at',
        'expected_delivery_at',
        'clinical_notes',
        'observations',
        'print_label',
    ];

    protected $casts = [
        'collection_datetime' => 'datetime',
        'received_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'validated_at' => 'datetime',
        'expected_delivery_at' => 'datetime',
        'print_label' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabRequestItem::class, 'request_id');
    }

    /**
     * Gera número de acesso único
     */
    public static function generateAccessionNumber(): string
    {
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return "LAB-{$date}-{$random}";
    }

    /**
     * Gera código de barras
     */
    public static function generateBarcode(): string
    {
        return sprintf('%012d', mt_rand(1, 999999999999));
    }

    /**
     * Verifica se o pedido pode ser validado
     */
    public function canBeValidated(): bool
    {
        if ($this->status !== 'pending_validation') {
            return false;
        }

        // Todos os itens devem estar completos
        return $this->items()->where('status', '!=', 'completed')->count() === 0;
    }

    /**
     * Obtém todos os resultados do pedido
     */
    public function results()
    {
        return $this->hasManyThrough(LabResult::class, LabRequestItem::class, 'request_id', 'request_item_id');
    }
}
