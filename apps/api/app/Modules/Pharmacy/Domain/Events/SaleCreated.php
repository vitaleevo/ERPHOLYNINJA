<?php

namespace App\Modules\Pharmacy\Domain\Events;

use App\Modules\Pharmacy\Domain\Entities\PharmacySale;
use App\Modules\Core\Shared\DomainEvent\DomainEvent;
use DateTimeImmutable;

class SaleCreated implements DomainEvent
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly PharmacySale $sale
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function payload(): array
    {
        return [
            'sale_id' => $this->sale->id,
            'clinic_id' => $this->sale->clinicId,
            'patient_id' => $this->sale->patientId,
            'total' => $this->sale->total->getAmount(),
            'status' => $this->sale->status,
            'occurred_on' => $this->occurredOn->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Obter ID da venda
     */
    public function getSaleId(): int
    {
        return $this->sale->id;
    }

    /**
     * Obter ID da clínica
     */
    public function getClinicId(): int
    {
        return $this->sale->clinicId;
    }

    /**
     * Obter total da venda
     */
    public function getTotal(): float
    {
        return $this->sale->total->getAmount();
    }
}
