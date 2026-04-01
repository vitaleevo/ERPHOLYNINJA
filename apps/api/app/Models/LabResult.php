<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabResult extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_item_id',
        'test_id',
        'result_value',
        'numeric_value',
        'text_result',
        'unit',
        'reference_min',
        'reference_max',
        'is_abnormal',
        'is_critical',
        'abnormal_flag',
        'interpretation',
        'comments',
        'attachments',
        'result_datetime',
    ];

    protected $casts = [
        'numeric_value' => 'decimal:4',
        'reference_min' => 'decimal:2',
        'reference_max' => 'decimal:2',
        'is_abnormal' => 'boolean',
        'is_critical' => 'boolean',
        'attachments' => 'array',
        'result_datetime' => 'datetime',
    ];

    public function requestItem(): BelongsTo
    {
        return $this->belongsTo(LabRequestItem::class, 'request_item_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    /**
     * Atualiza as flags de anormalidade e valor crítico
     */
    public function updateFlags(): void
    {
        if ($this->numeric_value === null) {
            return;
        }

        $test = $this->test;
        
        // Verifica se é crítico
        $this->is_critical = $test->isCriticalValue($this->numeric_value);
        
        // Verifica se é anormal
        $this->is_abnormal = $test->isAbnormalValue($this->numeric_value);
        
        // Obtém a flag de anormalidade
        $this->abnormal_flag = $test->getAbnormalFlag($this->numeric_value);
        
        $this->save();
    }

    /**
     * Formata o resultado para exibição
     */
    public function getFormattedResultAttribute(): string
    {
        if ($this->text_result) {
            return $this->text_result;
        }

        if ($this->numeric_value !== null) {
            return number_format($this->numeric_value, 2) . ' ' . ($this->unit ?? '');
        }

        return $this->result_value ?? '-';
    }

    /**
     * Obtém interpretação com base no resultado
     */
    public function getInterpretationAttribute(): ?string
    {
        if ($this->attributes['interpretation']) {
            return $this->attributes['interpretation'];
        }

        // Interpretação automática baseada nas flags
        if ($this->is_critical) {
            return 'VALOR CRÍTICO - Requer atenção imediata';
        }

        if ($this->is_abnormal) {
            switch ($this->abnormal_flag) {
                case 'HH':
                    return 'Muito acima dos valores de referência';
                case 'H':
                    return 'Acima dos valores de referência';
                case 'LL':
                    return 'Muito abaixo dos valores de referência';
                case 'L':
                    return 'Abaixo dos valores de referência';
            }
        }

        return 'Dentro dos valores de referência';
    }
}
