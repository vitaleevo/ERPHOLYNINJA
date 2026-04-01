<?php

namespace App\Modules\Pharmacy\Domain\Entities;

use App\Modules\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

class PharmacySaleItem
{
    public function __construct(
        public readonly int $id,
        public readonly int $saleId,
        public readonly int $medicationId,
        public readonly int $medicationBatchId,
        public readonly string $medicationName,
        public readonly int $quantity,
        public readonly Money $unitPrice,
        public readonly Money $discount,
        public readonly Money $total,
        public readonly ?string $dosage,
        public readonly ?string $instructions,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt = null
    ) {}

    /**
     * Criar entity a partir do model Eloquent
     */
    public static function fromModel(object $model): self
    {
        return new self(
            id: $model->id,
            saleId: $model->pharmacy_sale_id,
            medicationId: $model->medication_id,
            medicationBatchId: $model->medication_batch_id,
            medicationName: $model->medication_name,
            quantity: $model->quantity,
            unitPrice: Money::fromFloat((float) $model->unit_price),
            discount: Money::fromFloat((float) ($model->discount ?? 0)),
            total: Money::fromFloat((float) $model->total),
            dosage: $model->dosage,
            instructions: $model->instructions,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at) : null
        );
    }

    /**
     * Converter para array de dados
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'pharmacy_sale_id' => $this->saleId,
            'medication_id' => $this->medicationId,
            'medication_batch_id' => $this->medicationBatchId,
            'medication_name' => $this->medicationName,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice->getAmount(),
            'discount' => $this->discount->getAmount(),
            'total' => $this->total->getAmount(),
            'dosage' => $this->dosage,
            'instructions' => $this->instructions,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Calcular preço unitário após desconto
     */
    public function getFinalUnitPrice(): Money
    {
        $discountPerItem = $this->discount->getAmount() / $this->quantity;
        return $this->unitPrice->subtract(Money::fromFloat($discountPerItem));
    }
}
