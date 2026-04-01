<?php

namespace App\Modules\Pharmacy\Domain\Listeners;

use App\Modules\Pharmacy\Domain\Events\SaleCreated;
use Psr\Log\LoggerInterface;

/**
 * Listener para evento de venda criada
 * 
 * Este listener é acionado quando uma nova venda é criada,
 * permitindo executar ações secundárias como:
 * - Enviar notificações
 * - Atualizar caches
 * - Disparar integrações externas
 */
class SaleCreatedListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * Handle the event
     */
    public function handle(SaleCreated $event): void
    {
        $payload = $event->payload();

        // Log da venda criada
        $this->logger->info('Nova venda de farmácia criada', [
            'sale_id' => $payload['sale_id'],
            'clinic_id' => $payload['clinic_id'],
            'patient_id' => $payload['patient_id'],
            'total' => $payload['total'],
        ]);

        // Aqui poderiam ser adicionadas outras lógicas:
        // - Enviar email de confirmação
        // - Notificar sistema de faturamento
        // - Atualizar dashboard em tempo real
        // - Integrar com sistema externo
    }
}
