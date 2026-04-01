<?php

namespace App\Modules\Pharmacy\Application\DTOs;

final class CreatePharmacySaleItemDTO
{
    public function __construct(
        public readonly int $medicationId,
        public readonly int $medicationBatchId,
        public readonly string $medicationName,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly float $discount = 0.0,
        public readonly ?string $dosage = null,
        public readonly ?string $instructions = null,
    ) {}

    /**
     * Criar DTO a partir de request validada
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            medicationId: $data['medication_id'],
            medicationBatchId: $data['medication_batch_id'],
            medicationName: $data['medication_name'],
            quantity: $data['quantity'],
            unitPrice: $data['unit_price'],
            discount: $data['discount'] ?? 0.0,
            dosage: $data['dosage'] ?? null,
            instructions: $data['instructions'] ?? null,
        );
    }

    /**
     * Calcular total do item
     */
    public function calculateTotal(): float
    {
        return ($this->quantity * $this->unitPrice) - $this->discount;
    }

    /**
     * Converter para array
     */
    public function toArray(): array
    {
        return [
            'medication_id' => $this->medicationId,
            'medication_batch_id' => $this->medicationBatchId,
            'medication_name' => $this->medicationName,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'discount' => $this->discount,
            'total' => $this->calculateTotal(),
            'dosage' => $this->dosage,
            'instructions' => $this->instructions,
        ];
    }
}
