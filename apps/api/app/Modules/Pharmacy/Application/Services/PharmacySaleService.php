<?php

namespace App\Modules\Pharmacy\Application\Services;

use App\Modules\Pharmacy\Application\DTOs\CreatePharmacySaleDTO;
use App\Modules\Pharmacy\Domain\Entities\PharmacySale;
use App\Modules\Pharmacy\Domain\Entities\PharmacySaleItem;
use App\Modules\Pharmacy\Domain\Repositories\PharmacySaleRepositoryInterface;
use App\Modules\Core\Shared\ValueObjects\Money;
use DateTimeImmutable;

class PharmacySaleService
{
    public function __construct(
        private PharmacySaleRepositoryInterface $repository,
        private PharmacyStockService $stockService
    ) {}

    /**
     * Criar uma nova venda de farmácia
     */
    public function createSale(CreatePharmacySaleDTO $dto): PharmacySale
    {
        // 1. Validar disponibilidade de estoque
        foreach ($dto->items as $itemDTO) {
            $this->stockService->checkAvailability(
                $itemDTO->medicationBatchId,
                $itemDTO->quantity
            );
        }

        // 2. Calcular totais
        $subtotal = array_sum(array_map(
            fn($item) => $item->calculateTotal(),
            $dto->items
        ));
        
        $total = $subtotal - $dto->discount;

        // 3. Criar entity de venda
        $sale = new PharmacySale(
            id: 0, // Será definido pelo repositório
            clinicId: $dto->clinicId,
            patientId: $dto->patientId,
            prescriptionId: $dto->prescriptionId,
            userId: $dto->userId,
            invoiceNumber: '', // Será gerado pelo repositório
            subtotal: Money::fromFloat($subtotal),
            discount: Money::fromFloat($dto->discount),
            total: Money::fromFloat($total),
            paymentMethod: $dto->paymentMethod,
            status: 'pending',
            observations: $dto->observations,
            items: $this->createItemsFromDTO($dto->items),
            createdAt: new DateTimeImmutable()
        );

        // 4. Persistir venda
        $createdSale = $this->repository->create($sale);

        // 5. Baixar estoque
        foreach ($dto->items as $itemDTO) {
            $this->stockService->decreaseStock(
                $itemDTO->medicationBatchId,
                $itemDTO->quantity
            );
        }

        // 6. Atualizar status para completo
        // Em uma implementação real, isso poderia ser feito via Domain Event
        return $createdSale;
    }

    /**
     * Buscar venda por ID
     */
    public function findSale(int $id): ?PharmacySale
    {
        return $this->repository->find($id);
    }

    /**
     * Listar todas as vendas com filtros
     */
    public function listSales(array $filters = []): array
    {
        return $this->repository->findAll($filters)->toArray();
    }

    /**
     * Cancelar venda
     */
    public function cancelSale(int $id): void
    {
        $sale = $this->repository->find($id);
        
        if (!$sale) {
            throw new \DomainException('Venda não encontrada');
        }

        // Verificar se pode cancelar
        if (!$sale->canBeCancelled()) {
            throw new \DomainException('Venda já está cancelada');
        }

        // Cancelar venda (entidade)
        $sale->cancel();

        // Retornar itens ao estoque
        foreach ($sale->items as $item) {
            $this->stockService->increaseStock(
                $item->medicationBatchId,
                $item->quantity
            );
        }

        // Atualizar no repositório
        $cancelledSale = new PharmacySale(
            id: $sale->id,
            clinicId: $sale->clinicId,
            patientId: $sale->patientId,
            prescriptionId: $sale->prescriptionId,
            userId: $sale->userId,
            invoiceNumber: $sale->invoiceNumber,
            subtotal: $sale->subtotal,
            discount: $sale->discount,
            total: $sale->total,
            paymentMethod: $sale->paymentMethod,
            status: 'cancelled',
            observations: $sale->observations,
            items: $sale->items,
            createdAt: $sale->createdAt,
            updatedAt: new DateTimeImmutable()
        );

        $this->repository->update($cancelledSale);
    }

    /**
     * Obter resumo de vendas por período
     */
    public function getSalesSummary(string $startDate, string $endDate): array
    {
        $totalSales = $this->repository->getTotalSalesByPeriod($startDate, $endDate);
        $salesCount = count($this->repository->findByDateRange($startDate, $endDate));

        return [
            'total_sales' => $totalSales,
            'sales_count' => $salesCount,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ]
        ];
    }

    /**
     * Criar lista de itens da venda a partir dos DTOs
     * 
     * @param array<int, CreatePharmacySaleItemDTO> $itemsDTO
     * @return array<int, PharmacySaleItem>
     */
    private function createItemsFromDTO(array $itemsDTO): array
    {
        return array_map(function($itemDTO) {
            return new PharmacySaleItem(
                id: 0, // Será definido pelo repositório
                saleId: 0, // Será definido pelo repositório
                medicationId: $itemDTO->medicationId,
                medicationBatchId: $itemDTO->medicationBatchId,
                medicationName: $itemDTO->medicationName,
                quantity: $itemDTO->quantity,
                unitPrice: Money::fromFloat($itemDTO->unitPrice),
                discount: Money::fromFloat($itemDTO->discount),
                total: Money::fromFloat($itemDTO->calculateTotal()),
                dosage: $itemDTO->dosage,
                instructions: $itemDTO->instructions,
                createdAt: new DateTimeImmutable()
            );
        }, $itemsDTO);
    }
}
