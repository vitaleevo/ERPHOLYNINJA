<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemedicineTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctor;
    protected User $admin;
    protected Patient $patient;
    protected Clinic $clinic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::create([
            'name' => 'Clínica Teste',
            'nif' => '123456789',
            'email' => 'clinica@teste.com',
            'phone' => '+244 900 000 000',
        ]);

        $this->doctor = User::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Dr. João Silva',
            'email' => 'dr.joao@medangola.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Admin Sistema',
            'email' => 'admin@medangola.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Paciente Teste',
            'email' => 'paciente@teste.com',
            'phone' => '+244 900 111 222',
        ]);
    }

    public function test_criar_sessao_de_telemedicina(): void
    {
        // Criar agendamento de telemedicina
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 30,
            'status' => 'scheduled',
            'is_telemedicine' => true,
        ]);

        $response = $this->actingAs($this->doctor)
            ->postJson("/api/telemedicine/appointments/{$appointment->id}/create-session");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'session' => [
                    'id',
                    'session_id',
                    'meeting_url',
                    'moderator_password',
                    'attendee_password',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('telemedicine_sessions', [
            'appointment_id' => $appointment->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_iniciar_sessao_apenas_medico(): void
    {
        $session = \App\Models\TelemedicineSession::create([
            'clinic_id' => $this->clinic->id,
            'appointment_id' => Appointment::create([
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addDay(),
                'is_telemedicine' => true,
            ])->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'session_id' => 'test-session-uuid',
            'status' => 'scheduled',
        ]);

        // Médico inicia a sessão
        $response = $this->actingAs($this->doctor)
            ->postJson("/api/telemedicine/sessions/{$session->id}/start");

        $response->assertStatus(200);

        $this->assertDatabaseHas('telemedicine_sessions', [
            'id' => $session->id,
            'status' => 'started',
        ]);
    }

    public function test_paciente_nao_pode_iniciar_sessao(): void
    {
        $session = \App\Models\TelemedicineSession::create([
            'clinic_id' => $this->clinic->id,
            'appointment_id' => Appointment::create([
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addDay(),
                'is_telemedicine' => true,
            ])->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'session_id' => 'test-session-uuid',
            'status' => 'scheduled',
        ]);

        // Criar usuário paciente para teste
        $patientUser = User::create([
            'clinic_id' => $this->clinic->id,
            'name' => $this->patient->name,
            'email' => 'paciente.user@teste.com',
            'password' => bcrypt('password'),
            'role' => 'patient',
            'is_active' => true,
        ]);

        // Paciente tenta iniciar (deve falhar porque não é médico)
        $response = $this->actingAs($patientUser)
            ->postJson("/api/telemedicine/sessions/{$session->id}/start");

        $response->assertStatus(403); // Forbidden, não 500
    }

    public function test_enviar_mensagem_no_chat(): void
    {
        $session = \App\Models\TelemedicineSession::create([
            'clinic_id' => $this->clinic->id,
            'appointment_id' => Appointment::create([
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addDay(),
                'is_telemedicine' => true,
            ])->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'session_id' => 'test-session-uuid',
            'status' => 'started',
        ]);

        $response = $this->actingAs($this->doctor)
            ->postJson("/api/telemedicine/sessions/{$session->id}/messages", [
                'message' => 'Olá, como está se sentindo?',
                'type' => 'text'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'chat' => [
                    'id',
                    'session_id',
                    'message',
                    'type',
                ]
            ]);

        $this->assertDatabaseHas('telemedicine_chats', [
            'session_id' => $session->id,
            'message' => 'Olá, como está se sentindo?',
        ]);
    }

    public function test_listar_sessoes_com_filtros(): void
    {
        // Criar várias sessões
        \App\Models\TelemedicineSession::create([
            'clinic_id' => $this->clinic->id,
            'appointment_id' => Appointment::create([
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addDay(),
                'is_telemedicine' => true,
            ])->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'session_id' => 'session-1',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->doctor)
            ->getJson('/api/telemedicine/sessions?status=scheduled');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_encerrar_sessao_com_gravacao(): void
    {
        $session = \App\Models\TelemedicineSession::create([
            'clinic_id' => $this->clinic->id,
            'appointment_id' => Appointment::create([
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addDay(),
                'is_telemedicine' => true,
            ])->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'session_id' => 'test-session-uuid',
            'status' => 'started',
        ]);

        $response = $this->actingAs($this->doctor)
            ->postJson("/api/telemedicine/sessions/{$session->id}/end", [
                'recording_url' => 'https://storage.example.com/recording.mp4'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('telemedicine_sessions', [
            'id' => $session->id,
            'status' => 'ended',
            'recording_url' => 'https://storage.example.com/recording.mp4',
        ]);
    }

    public function test_obter_estatisticas_de_telemedicina(): void
    {
        // Criar sessões em diferentes status
        \App\Models\TelemedicineSession::create([
            'clinic_id' => $this->clinic->id,
            'appointment_id' => Appointment::create([
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addDay(),
                'is_telemedicine' => true,
            ])->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'session_id' => 'session-1',
            'status' => 'ended',
        ]);

        \App\Models\TelemedicineSession::create([
            'clinic_id' => $this->clinic->id,
            'appointment_id' => Appointment::create([
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addDay(),
                'is_telemedicine' => true,
            ])->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'session_id' => 'session-2',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->doctor)
            ->getJson('/api/telemedicine/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'total' => 2,
                'completed' => 1,
                'scheduled' => 1,
            ]);
    }

    public function test_historico_do_paciente(): void
    {
        $session = \App\Models\TelemedicineSession::create([
            'clinic_id' => $this->clinic->id,
            'appointment_id' => Appointment::create([
                'clinic_id' => $this->clinic->id,
                'patient_id' => $this->patient->id,
                'doctor_id' => $this->doctor->id,
                'scheduled_at' => now()->addDay(),
                'is_telemedicine' => true,
            ])->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'session_id' => 'test-session-uuid',
            'status' => 'ended',
        ]);

        $response = $this->actingAs($this->doctor)
            ->getJson("/api/telemedicine/patients/{$this->patient->id}/history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'patient' => ['id', 'name'],
                'history' => 'array'
            ]);
    }
}
