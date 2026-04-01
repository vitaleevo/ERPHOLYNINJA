<?php

namespace App\Services;

use App\Models\TelemedicineSession;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Patient;
use Exception;

class VideoConferenceService
{
    /**
     * Configurações do provedor de vídeo (ex: Jitsi, Zoom, Daily.co)
     */
    private string $provider;
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        // Configurar com variáveis de ambiente
        $this->provider = config('services.videoconference.provider', 'jitsi');
        $this->baseUrl = config('services.videoconference.base_url', 'https://meet.jit.si');
        $this->apiKey = config('services.videoconference.api_key');
        $this->apiSecret = config('services.videoconference.api_secret');
    }

    /**
     * Criar sala de reunião
     */
    public function createMeetingRoom(
        string $sessionId,
        User $doctor,
        Patient $patient,
        int $durationMinutes = 30
    ): array {
        try {
            // Gerar nomes únicos para a sala
            $roomName = $this->generateRoomName($sessionId);
            
            // Gerar senhas
            $moderatorPassword = TelemedicineSession::generatePassword();
            $attendeePassword = TelemedicineSession::generatePassword();

            // URL da reunião baseada no provedor
            $meetingUrl = "{$this->baseUrl}/{$roomName}";

            return [
                'session_id' => $sessionId,
                'room_name' => $roomName,
                'meeting_url' => $meetingUrl,
                'moderator_password' => $moderatorPassword,
                'attendee_password' => $attendeePassword,
                'duration_minutes' => $durationMinutes,
                'provider' => $this->provider,
            ];
        } catch (Exception $e) {
            throw new Exception('Erro ao criar sala de reunião: ' . $e->getMessage());
        }
    }

    /**
     * Gerar nome único para a sala
     */
    private function generateRoomName(string $sessionId): string
    {
        return 'MedAngola-' . substr(md5($sessionId), 0, 8) . '-' . time();
    }

    /**
     * Obter configurações do cliente para o provedor de vídeo
     */
    public function getClientConfig(
        TelemedicineSession $session,
        User $user
    ): array {
        $isModerator = $user->id === $session->doctor_id;
        
        return [
            'provider' => $this->provider,
            'roomName' => $this->generateRoomName($session->session_id),
            'userInfo' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'configOverwrite' => [
                'startWithAudioMuted' => false,
                'startWithVideoMuted' => false,
                'disableModerator' => !$isModerator,
            ],
            'interfaceConfigOverwrite' => [
                'SHOW_JITSI_WATERMARK' => false,
                'SHOW_BRAND_WATERMARK' => false,
            ],
            'password' => $isModerator 
                ? $session->moderator_password 
                : $session->attendee_password,
        ];
    }

    /**
     * Gravar sessão (se suportado pelo provedor)
     */
    public function startRecording(TelemedicineSession $session): bool
    {
        // Implementação específica por provedor
        // Retorna true se iniciado com sucesso
        return true;
    }

    /**
     * Parar gravação
     */
    public function stopRecording(TelemedicineSession $session): bool
    {
        // Implementação específica por provedor
        return true;
    }

    /**
     * Encerrar reunião
     */
    public function endMeeting(TelemedicineSession $session): void
    {
        // Notificar participantes que a reunião foi encerrada
        // Implementação via WebSocket
    }

    /**
     * Verificar status da sala
     */
    public function isRoomActive(string $roomId): bool
    {
        // Verificar se a sala está ativa no provedor
        return true;
    }
}
