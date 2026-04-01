<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInvoicePayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $backoff = 120;

    public function __construct(
        public Invoice $invoice,
        public string $paymentMethod
    ) {}

    public function handle(): void
    {
        Log::info("Processando pagamento da fatura #{$this->invoice->id} via {$this->paymentMethod}");

        try {
            // Simular processamento de pagamento
            match ($this->paymentMethod) {
                'multicaixa' => $this->processMulticaixa(),
                'card' => $this->processCard(),
                'transfer' => $this->processTransfer(),
                default => throw new \InvalidArgumentException("Método não suportado")
            };

            // Marcar fatura como paga
            $this->invoice->markAsPaid();

            Log::info("Pagamento processado com sucesso para fatura #{$this->invoice->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar pagamento: {$e->getMessage()}");
            throw $e;
        }
    }

    private function processMulticaixa(): void
    {
        // Integrar com Multicaixa Express
        Log::info("Processando pagamento Multicaixa para fatura #{$this->invoice->id}");
        
        // Exemplo:
        // $response = MulticaixaProvider::pay([
        //     'amount' => $this->invoice->total_amount,
        //     'reference' => $this->generateReference(),
        // ]);
        
        sleep(1); // Simular delay
    }

    private function processCard(): void
    {
        // Integrar com gateway de cartão
        Log::info("Processando pagamento com cartão para fatura #{$this->invoice->id}");
        
        sleep(1); // Simular delay
    }

    private function processTransfer(): void
    {
        // Verificar transferência bancária
        Log::info("Verificando transferência para fatura #{$this->invoice->id}");
        
        sleep(1); // Simular delay
    }

    private function generateReference(): string
    {
        return 'MCX' . str_pad($this->invoice->id, 9, '0', STR_PAD_LEFT);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Falha no processamento do pagamento: {$exception->getMessage()}");
        
        // Manter fatura como pendente e notificar
        $this->invoice->update(['status' => 'issued']);
    }
}
