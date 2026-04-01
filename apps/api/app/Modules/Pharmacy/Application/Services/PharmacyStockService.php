<?php

namespace App\Modules\Pharmacy\Application\Services;

/**
 * Serviço de controle de estoque (stub para desenvolvimento)
 * Em uma implementação completa, este serviço teria toda a lógica de estoque
 */
class PharmacyStockService
{
    /**
     * Verificar disponibilidade de medicamento no estoque
     */
    public function checkAvailability(int $batchId, int $quantity): void
    {
        // TODO: Implementar verificação real de estoque
        // Por enquanto, apenas um stub para permitir a compilação
    }

    /**
     * Baixar quantidade do estoque
     */
    public function decreaseStock(int $batchId, int $quantity): void
    {
        // TODO: Implementar baixa real de estoque
    }

    /**
     * Aumentar quantidade no estoque (devolução/cancelamento)
     */
    public function increaseStock(int $batchId, int $quantity): void
    {
        // TODO: Implementar aumento real de estoque
    }
}
