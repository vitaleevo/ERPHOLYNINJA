<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelemedicineSession;
use App\Models\TelemedicineChat;
use App\Models\TelemedicineFile;
use App\Models\Appointment;
use App\Models\User;
use App\Services\TelemedicineService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TelemedicineController extends Controller
{
    private TelemedicineService $telemedicineService;

    public function __construct(TelemedicineService $telemedicineService)
    {
        $this->telemedicineService = $telemedicineService;
    }

    /**
     * Listar sessões de telemedicina
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = TelemedicineSession::with(['doctor', 'patient', 'appointment'])
            ->where('clinic_id', $user->clinic_id);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $sessions = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($sessions);
    }

    /**
     * Criar sessão a partir de agendamento
     */
    public function createFromAppointment(int $appointmentId): JsonResponse
    {
        $user = Auth::user();
        
        $appointment = Appointment::where('id', $appointmentId)
            ->where('clinic_id', $user->clinic_id)
            ->firstOrFail();

        if (!$appointment->is_telemedicine) {
            return response()->json([
                'error' => 'Agendamento não é de telemedicina'
            ], 422);
        }

        try {
            $session = $this->telemedicineService->createSessionFromAppointment($appointment);
            
            return response()->json([
                'message' => 'Sessão criada com sucesso',
                'session' => $session->load(['doctor', 'patient', 'appointment'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter detalhes da sessão
     */
    public function show(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        $session = TelemedicineSession::with(['doctor', 'patient', 'appointment', 'chats.user', 'files.user'])
            ->where('clinic_id', $user->clinic_id)
            ->findOrFail($id);

        // Verificar se usuário tem permissão
        if (!in_array($user->id, [$session->doctor_id, $session->patient_id]) && !$user->isAdmin()) {
            return response()->json([
                'error' => 'Sem permissão para acessar esta sessão'
            ], 403);
        }

        return response()->json($session);
    }

    /**
     * Iniciar sessão
     */
    public function start(int $id): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $session = $this->telemedicineService->startSession($id, $user);
            
            return response()->json([
                'message' => 'Sessão iniciada com sucesso',
                'session' => $session,
                'meeting_url' => $session->meeting_url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Encerrar sessão
     */
    public function end(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $recordingUrl = $request->input('recording_url');
            $session = $this->telemedicineService->endSession($id, $user, $recordingUrl);
            
            return response()->json([
                'message' => 'Sessão encerrada com sucesso',
                'session' => $session
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar sessão
     */
    public function cancel(int $id): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $session = $this->telemedicineService->cancelSession($id, $user);
            
            return response()->json([
                'message' => 'Sessão cancelada com sucesso',
                'session' => $session
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar mensagem no chat
     */
    public function sendMessage(Request $request, int $sessionId): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'message' => 'required|string',
            'type' => 'sometimes|in:text,file,system'
        ]);

        try {
            $chat = $this->telemedicineService->sendMessage(
                $sessionId,
                $user,
                $request->message,
                $request->type ?? 'text'
            );
            
            return response()->json([
                'message' => 'Mensagem enviada com sucesso',
                'chat' => $chat->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter mensagens do chat
     */
    public function getMessages(int $sessionId): JsonResponse
    {
        $user = Auth::user();
        
        $session = TelemedicineSession::where('id', $sessionId)
            ->where('clinic_id', $user->clinic_id)
            ->firstOrFail();

        $messages = TelemedicineChat::with('user')
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        return response()->json($messages);
    }

    /**
     * Upload de arquivo
     */
    public function uploadFile(Request $request, int $sessionId): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'file' => 'required|file|max:10240', // Máx 10MB
            'category' => 'sometimes|in:document,exam,prescription,other'
        ]);

        try {
            $file = $this->telemedicineService->uploadFile(
                $sessionId,
                $request->file('file'),
                $user,
                $request->category ?? 'other'
            );
            
            return response()->json([
                'message' => 'Arquivo enviado com sucesso',
                'file' => $file
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter arquivos da sessão
     */
    public function getFiles(int $sessionId): JsonResponse
    {
        $user = Auth::user();
        
        $session = TelemedicineSession::where('id', $sessionId)
            ->where('clinic_id', $user->clinic_id)
            ->firstOrFail();

        $files = TelemedicineFile::with('user')
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($files);
    }

    /**
     * Obter configurações da sessão para cliente de vídeo
     */
    public function getSessionConfig(int $sessionId): JsonResponse
    {
        $user = Auth::user();
        
        try {
            $config = $this->telemedicineService->getSessionConfig($sessionId, $user);
            
            return response()->json($config);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Histórico de telemedicina do paciente
     */
    public function patientHistory(int $patientId): JsonResponse
    {
        $user = Auth::user();
        
        $patient = \App\Models\Patient::where('id', $patientId)
            ->where('clinic_id', $user->clinic_id)
            ->firstOrFail();

        $history = $this->telemedicineService->getPatientHistory($patient);

        return response()->json([
            'patient' => $patient,
            'history' => $history
        ]);
    }

    /**
     * Estatísticas de telemedicina
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $startDate = $request->has('start_date') 
            ? Carbon::parse($request->start_date) 
            : null;
        
        $endDate = $request->has('end_date') 
            ? Carbon::parse($request->end_date) 
            : null;

        $stats = $this->telemedicineService->getStatistics($user, $startDate, $endDate);

        return response()->json($stats);
    }

    /**
     * Join em sessão ativa
     */
    public function join(int $id): JsonResponse
    {
        $user = Auth::user();
        
        $session = TelemedicineSession::where('id', $id)
            ->where('clinic_id', $user->clinic_id)
            ->firstOrFail();

        // Verificar se usuário é participante
        if (!in_array($user->id, [$session->doctor_id, $session->patient_id])) {
            return response()->json([
                'error' => 'Usuário não é participante desta sessão'
            ], 403);
        }

        // Verificar se sessão está ativa
        if (!$session->isActive()) {
            return response()->json([
                'error' => 'Sessão não está ativa'
            ], 422);
        }

        return response()->json([
            'meeting_url' => $session->meeting_url,
            'session' => $session
        ]);
    }
}
