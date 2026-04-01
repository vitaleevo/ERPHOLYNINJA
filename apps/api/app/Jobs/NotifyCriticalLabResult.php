<?php

namespace App\Jobs;

use App\Models\LabResult;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyCriticalLabResult implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public LabResult $result,
        public array $recipients = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->result->is_critical) {
            Log::info('Resultado não é crítico, ignorando notificação');
            return;
        }

        $test = $this->result->test;
        $requestItem = $this->result->requestItem;
        $request = $requestItem->request;
        $patient = $request->patient;

        // Mensagem de notificação
        $message = sprintf(
            "RESULTADO CRÍTICO: %s - Paciente: %s\nValor: %s %s\n%s",
            $test->name,
            $patient->name,
            number_format($this->result->numeric_value ?? 0, 2),
            $this->result->unit,
            $this->result->interpretation ?: 'Requer atenção imediata!'
        );

        // Determinar destinatários
        $recipients = $this->recipients;
        if (empty($recipients)) {
            // Médico que solicitou
            if ($request->doctor) {
                $recipients[] = $request->doctor;
            }
            
            // Técnico que realizou o exame
            if ($request->technician) {
                $recipients[] = $request->technician;
            }
        }

        // Enviar notificações
        foreach ($recipients as $recipient) {
            if ($recipient instanceof User) {
                // Notificação in-app (pode ser expandida para email, SMS, WhatsApp)
                $recipient->notify(new \App\Notifications\CriticalLabResultNotification(
                    $this->result,
                    $message
                ));

                Log::channel('critical')->alert(sprintf(
                    'Notificação crítica enviada para %s: %s',
                    $recipient->email,
                    $message
                ));
            }
        }

        // Registrar no log de críticos
        Log::channel('critical')->alert(sprintf(
            'Resultado crítico detectado: Test=%s, Patient=%s, Value=%s, Flag=%s',
            $test->name,
            $patient->name,
            $this->result->formatted_result,
            $this->result->abnormal_flag
        ));
    }
}
