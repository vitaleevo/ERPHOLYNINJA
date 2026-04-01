<?php

namespace App\Services;

use App\Models\TelemedicineSession;
use App\Models\TelemedicineChat;
use App\Models\TelemedicineFile;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Patient;
use App\Events\MessageSent;
use App\Events\SessionStarted;
use App\Events\SessionEnded;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Exception;

class TelemedicineService
{
    private VideoConferenceService $videoService;

    public function __construct(VideoConferenceService $videoConferenceService)
    {
        $this->videoService = $videoConferenceService;
    }

    /**
     * Criar sessão de telemedicina a partir de agendamento
     */
    public function createSessionFromAppointment(Appointment $appointment): TelemedicineSession
    {
        if (!$appointment->is_telemedicine) {
            throw new Exception('Agendamento não é de telemedicina');
        }

        // Verificar se já existe sessão
        $existingSession = TelemedicineSession::where('appointment_id', $appointment->id)->first();
        if ($existingSession) {
            return $existingSession;
        }

        // Criar sala de vídeo
        $meetingData = $this->videoService->createMeetingRoom(
            (string) \Illuminate\Support\Str::uuid(),
            $appointment->doctor,
            $appointment->patient,
            $appointment->duration_minutes ?? 30
        );

        // Criar sessão
        $session = TelemedicineSession::create([
            'clinic_id' => $appointment->clinic_id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $appointment->patient_id,
            'session_id' => $meetingData['session_id'],
            'meeting_url' => $meetingData['meeting_url'],
            'moderator_password' => $meetingData['moderator_password'],
            'attendee_password' => $meetingData['attendee_password'],
            'status' => 'scheduled',
            'duration_minutes' => $meetingData['duration_minutes'],
            'settings' => [
                'provider' => $meetingData['provider'],
                'room_name' => $meetingData['room_name'],
            ],
        ]);

        // Atualizar agendamento com link
        $appointment->update([
            'video_call_link' => $session->meeting_url,
        ]);

        // Criar mensagem de sistema
        TelemedicineChat::createSystemMessage(
            $session->id,
            'Sessão de telemedicina criada. Aguarde o início da consulta.'
        );

        return $session;
    }

    /**
     * Iniciar sessão de telemedicina
     */
    public function startSession(int $sessionId, User $user): TelemedicineSession
    {
        $session = TelemedicineSession::findOrFail($sessionId);

        // Apenas médico pode iniciar a sessão
        if ($session->doctor_id !== $user->id) {
            throw new Exception('Apenas o médico pode iniciar a sessão');
        }

        $session->start();

        // Criar mensagem de sistema
        TelemedicineChat::createSystemMessage(
            $session->id,
            "Consulta iniciada às " . Carbon::now()->format('H:i')
        );

        // Broadcast do evento
        event(new SessionStarted($session));

        return $session->fresh();
    }

    /**
     * Encerrar sessão de telemedicina
     */
    public function endSession(int $sessionId, User $user, ?string $recordingUrl = null): TelemedicineSession
    {
        $session = TelemedicineSession::findOrFail($sessionId);

        // Apenas médico pode encerrar a sessão
        if ($session->doctor_id !== $user->id) {
            throw new Exception('Apenas o médico pode encerrar a sessão');
        }

        $session->end($recordingUrl);

        // Criar mensagem de sistema
        TelemedicineChat::createSystemMessage(
            $session->id,
            "Consulta encerrada às " . Carbon::now()->format('H:i')
        );

        // Encerrar reunião no provedor de vídeo
        $this->videoService->endMeeting($session);

        // Broadcast do evento
        event(new SessionEnded($session));

        return $session->fresh();
    }

    /**
     * Cancelar sessão de telemedicina
     */
    public function cancelSession(int $sessionId, User $user): TelemedicineSession
    {
        $session = TelemedicineSession::findOrFail($sessionId);

        // Médico ou admin podem cancelar
        if ($session->doctor_id !== $user->id && !$user->isAdmin()) {
            throw new Exception('Sem permissão para cancelar sessão');
        }

        $session->cancel();

        // Criar mensagem de sistema
        TelemedicineChat::createSystemMessage(
            $session->id,
            'Consulta cancelada.'
        );

        return $session->fresh();
    }

    /**
     * Enviar mensagem no chat
     */
    public function sendMessage(
        int $sessionId,
        User $user,
        string $message,
        string $type = 'text'
    ): TelemedicineChat {
        $session = TelemedicineSession::findOrFail($sessionId);

        // Verificar se usuário é participante da sessão
        if (!in_array($user->id, [$session->doctor_id, $session->patient_id])) {
            throw new Exception('Usuário não é participante desta sessão');
        }

        $chat = TelemedicineChat::create([
            'session_id' => $sessionId,
            'user_id' => $user->id,
            'message' => $message,
            'type' => $type,
        ]);

        // Broadcast da mensagem via WebSocket
        event(new MessageSent($chat));

        return $chat;
    }

    /**
     * Upload de arquivo na sessão
     */
    public function uploadFile(
        int $sessionId,
        UploadedFile $file,
        User $user,
        string $category = 'other'
    ): TelemedicineFile {
        $session = TelemedicineSession::findOrFail($sessionId);

        // Verificar se usuário é participante da sessão
        if (!in_array($user->id, [$session->doctor_id, $session->patient_id])) {
            throw new Exception('Usuário não é participante desta sessão');
        }

        // Salvar arquivo
        $path = $file->store('telemedicine/' . $sessionId, 'public');

        $telemedicineFile = TelemedicineFile::create([
            'session_id' => $sessionId,
            'user_id' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_category' => $category,
        ]);

        // Criar mensagem no chat sobre o arquivo
        $this->sendMessage(
            $sessionId,
            $user,
            "Arquivo enviado: {$file->getClientOriginalName()}",
            'system'
        );

        return $telemedicineFile;
    }

    /**
     * Obter histórico de sessões do paciente
     */
    public function getPatientHistory(Patient $patient, ?int $limit = 10): array
    {
        $sessions = TelemedicineSession::with(['doctor', 'appointment'])
            ->where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $sessions->map(function ($session) {
            return [
                'id' => $session->id,
                'date' => $session->created_at->format('d/m/Y H:i'),
                'doctor' => $session->doctor->name,
                'status' => $session->status,
                'duration' => $session->duration_minutes,
                'specialty' => $session->doctor->specialty?->name ?? 'Geral',
            ];
        })->toArray();
    }

    /**
     * Obter estatísticas de telemedicina
     */
    public function getStatistics(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = TelemedicineSession::where('clinic_id', $user->clinic_id);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $completed = (clone $query)->where('status', 'ended')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $scheduled = (clone $query)->where('status', 'scheduled')->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'scheduled' => $scheduled,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Obter configurações da sessão para o cliente
     */
    public function getSessionConfig(int $sessionId, User $user): array
    {
        $session = TelemedicineSession::findOrFail($sessionId);

        // Verificar se usuário é participante
        if (!in_array($user->id, [$session->doctor_id, $session->patient_id])) {
            throw new Exception('Usuário não é participante desta sessão');
        }

        return $this->videoService->getClientConfig($session, $user);
    }
}
