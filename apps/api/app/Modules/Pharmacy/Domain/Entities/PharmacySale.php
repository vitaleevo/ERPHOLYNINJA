<?php

namespace App\Modules\Pharmacy\Domain\Entities;

use App\Modules\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

class PharmacySale
{
    public function __construct(
        public readonly int $id,
        public readonly int $clinicId,
        public readonly int $patientId,
        public readonly ?int $prescriptionId,
        public readonly int $userId,
        public readonly string $invoiceNumber,
        public readonly Money $subtotal,
        public readonly Money $discount,
        public readonly Money $total,
        public readonly string $paymentMethod,
        public readonly string $status,
        public readonly ?string $observations,
        public readonly array $items,
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
            clinicId: $model->clinic_id,
            patientId: $model->patient_id,
            prescriptionId: $model->prescription_id,
            userId: $model->user_id,
            invoiceNumber: $model->invoice_number,
            subtotal: Money::fromFloat((float) $model->subtotal),
            discount: Money::fromFloat((float) ($model->discount ?? 0)),
            total: Money::fromFloat((float) $model->total),
            paymentMethod: $model->payment_method,
            status: $model->status,
            observations: $model->observations,
            items: $model->items?->map(fn($item) => PharmacySaleItem::fromModel($item))->toArray() ?? [],
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
            'clinic_id' => $this->clinicId,
            'patient_id' => $this->patientId,
            'prescription_id' => $this->prescriptionId,
            'user_id' => $this->userId,
            'invoice_number' => $this->invoiceNumber,
            'subtotal' => $this->subtotal->getAmount(),
            'discount' => $this->discount->getAmount(),
            'total' => $this->total->getAmount(),
            'payment_method' => $this->paymentMethod,
            'status' => $this->status,
            'observations' => $this->observations,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Verificar se venda pode ser cancelada
     */
    public function canBeCancelled(): bool
    {
        return $this->status !== 'cancelled';
    }

    /**
     * Cancelar venda (regra de domínio)
     */
    public function cancel(): void
    {
        if (!$this->canBeCancelled()) {
            throw new \DomainException('Venda já está cancelada');
        }
        
        // A lógica de estoque deve ser tratada pelo serviço de aplicação
    }

    /**
     * Verificar se venda está completa
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar se venda está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
