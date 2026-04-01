<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas.
     */
    public int $tries = 3;

    /**
     * Tempo entre tentativas (em segundos).
     */
    public int $backoff = 60;

    public function __construct(
        public Appointment $appointment,
        public string $type = 'sms' // sms, whatsapp, email
    ) {}

    public function handle(): void
    {
        Log::info("Enviando lembrete de agendamento #{$this->appointment->id} via {$this->type}");

        try {
            match ($this->type) {
                'sms' => $this->sendSms(),
                'whatsapp' => $this->sendWhatsapp(),
                'email' => $this->sendEmail(),
                default => throw new \InvalidArgumentException("Tipo inválido: {$this->type}")
            };

            Log::info("Lembrete enviado com sucesso para agendamento #{$this->appointment->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao enviar lembrete: {$e->getMessage()}");
            throw $e;
        }
    }

    private function sendSms(): void
    {
        // Implementar integração com provedor de SMS (Twilio, etc.)
        $patient = $this->appointment->patient;
        $message = "Lembrete: Sua consulta está agendada para " . 
                   $this->appointment->scheduled_at->format('d/m/Y H:i');
        
        Log::info("SMS para {$patient->phone}: {$message}");
        
        // Exemplo: SmsProvider::send($patient->phone, $message);
    }

    private function sendWhatsapp(): void
    {
        // Implementar integração com WhatsApp API
        $patient = $this->appointment->patient;
        $message = "Olá {$patient->name}! 👋\n\n";
        $message .= "Lembrete da sua consulta:\n";
        $message .= "📅 Data: " . $this->appointment->scheduled_at->format('d/m/Y') . "\n";
        $message .= "⏰ Hora: " . $this->appointment->scheduled_at->format('H:i') . "\n";
        $message .= "👨‍⚕️ Médico: " . $this->appointment->doctor->name . "\n";
        
        Log::info("WhatsApp para {$patient->phone}: {$message}");
        
        // Exemplo: WhatsappProvider::send($patient->phone, $message);
    }

    private function sendEmail(): void
    {
        // Implementar envio de email
        $patient = $this->appointment->patient;
        
        Log::info("Email para {$patient->email}: Lembrete de consulta");
        
        // Exemplo: Mail::to($patient)->send(new AppointmentReminderMail($this->appointment));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Falha ao enviar lembrete após {$this->tries} tentativas: {$exception->getMessage()}");
        
        // Notificar admin sobre falha
        // Notification::send(User::where('role', 'admin')->get(), new JobFailed($this, $exception));
    }
}
