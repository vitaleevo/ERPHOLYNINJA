<?php

namespace App\Modules\Pharmacy\Application\DTOs;

final class CreatePharmacySaleDTO
{
    /**
     * @param array<int, CreatePharmacySaleItemDTO> $items
     */
    public function __construct(
        public readonly int $clinicId,
        public readonly int $patientId,
        public readonly array $items,
        public readonly ?int $prescriptionId = null,
        public readonly int $userId = 1,
        public readonly string $paymentMethod = 'cash',
        public readonly float $discount = 0.0,
        public readonly ?string $observations = null,
    ) {}

    /**
     * Criar DTO a partir de request validada
     */
    public static function fromRequest(array $data): self
    {
        $items = array_map(
            fn($item) => CreatePharmacySaleItemDTO::fromRequest($item),
            $data['items']
        );

        return new self(
            clinicId: $data['clinic_id'],
            patientId: $data['patient_id'],
            items: $items,
            prescriptionId: $data['prescription_id'] ?? null,
            userId: $data['user_id'] ?? 1,
            paymentMethod: $data['payment_method'] ?? 'cash',
            discount: $data['discount'] ?? 0.0,
            observations: $data['observations'] ?? null,
        );
    }

    /**
     * Converter para array
     */
    public function toArray(): array
    {
        return [
            'clinic_id' => $this->clinicId,
            'patient_id' => $this->patientId,
            'prescription_id' => $this->prescriptionId,
            'user_id' => $this->userId,
            'payment_method' => $this->paymentMethod,
            'discount' => $this->discount,
            'observations' => $this->observations,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
        ];
    }
}
