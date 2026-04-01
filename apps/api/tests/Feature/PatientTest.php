<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_consegue_listar_pacientes_da_clinica(): void
    {
        // Arrange: Criar clínica e pacientes
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        
        Patient::factory()->count(5)->create(['clinic_id' => $clinic->id]);

        Sanctum::actingAs($user);

        // Act: Listar pacientes
        $response = $this->withHeader('X-Clinic-Id', $clinic->id)
            ->getJson('/api/patients');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'clinic_id',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    public function test_consegue_criar_novo_paciente(): void
    {
        // Arrange: Criar clínica e usuário
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        
        $patientData = [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'phone' => '+244 923 000 000',
            'nif' => '001234567LA012',
            'bi_number' => '001234567LA012',
            'birth_date' => '1990-01-15',
            'gender' => 'male',
        ];

        Sanctum::actingAs($user);

        // Act: Criar paciente
        $response = $this->withHeader('X-Clinic-Id', $clinic->id)
            ->postJson('/api/patients', $patientData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'João Silva',
                'email' => 'joao@email.com',
            ]);

        $this->assertDatabaseHas('patients', [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
        ]);
    }

    public function test_nao_consegue_criar_paciente_sem_nome(): void
    {
        // Arrange
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        
        Sanctum::actingAs($user);

        // Act: Tentar criar sem nome
        $response = $this->withHeader('X-Clinic-Id', $clinic->id)
            ->postJson('/api/patients', [
                'phone' => '+244 923 000 000',
            ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }
}
