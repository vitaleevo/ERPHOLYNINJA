<?php

namespace App\Notifications;

use App\Models\LabResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalLabResultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public LabResult $result,
        public string $message
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $test = $this->result->test;
        $patient = $this->result->requestItem->request->patient;

        return (new MailMessage)
            ->error()
            ->subject('RESULTADO CRÍTICO DE LABORATÓRIO - ' . $patient->name)
            ->greeting('ATENÇÃO MÉDICA IMEDIATA!')
            ->line($this->message)
            ->line('**Detalhes do Exame:**')
            ->line([
                'Exame' => $test->name,
                'Paciente' => $patient->name,
                'Valor' => $this->result->formatted_result,
                'Status' => $this->result->is_critical ? 'CRÍTICO' : 'ANORMAL',
            ])
            ->action('Ver Resultado Completo', url('/lab/results/' . $this->result->id))
            ->line('Este resultado requer atenção médica imediata.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'critical_lab_result',
            'result_id' => $this->result->id,
            'test_name' => $this->result->test->name,
            'patient_name' => $this->result->requestItem->request->patient->name,
            'value' => $this->result->formatted_result,
            'is_critical' => $this->result->is_critical,
            'abnormal_flag' => $this->result->abnormal_flag,
            'message' => $this->message,
            'url' => url('/lab/results/' . $this->result->id),
        ];
    }
}
