<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_consegue_fazer_login_com_credenciais_validas(): void
    {
        // Arrange: Criar clínica e usuário
        $clinic = Clinic::factory()->create([
            'status' => 'active',
        ]);
        
        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'teste@clinica.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        // Act: Tentar login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'teste@clinica.com',
            'password' => 'password123',
            'clinic_id' => $clinic->id,
        ]);

        // Assert: Verificar sucesso
        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'clinic_id',
                ],
                'token',
                'token_type',
            ]);
    }

    public function test_login_falha_com_credenciais_invalidas(): void
    {
        // Arrange: Criar clínica e usuário
        $clinic = Clinic::factory()->create();
        
        User::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'teste@clinica.com',
            'password' => bcrypt('password123'),
        ]);

        // Act: Tentar login com senha errada
        $response = $this->postJson('/api/auth/login', [
            'email' => 'teste@clinica.com',
            'password' => 'senha-errada',
            'clinic_id' => $clinic->id,
        ]);

        // Assert: Verificar erro
        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_registro_de_nova_clinica_funciona_corretamente(): void
    {
        // Arrange: Dados da clínica
        $data = [
            'clinic_name' => 'Nova Clínica Teste',
            'clinic_email' => 'contato@novaclinica.com',
            'clinic_phone' => '+244 923 000 000',
            'clinic_nif' => '500123456',
            'clinic_address' => 'Rua X, Luanda',
            'admin_name' => 'Dr. Admin',
            'admin_email' => 'admin@novaclinica.com',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ];

        // Act: Fazer registro
        $response = $this->postJson('/api/auth/register', $data);

        // Assert: Verificar sucesso
        $response->assertStatus(201)
            ->assertJsonStructure([
                'clinic' => [
                    'id',
                    'name',
                    'email',
                    'slug',
                ],
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
                'token',
            ]);
        
        // Verificar se clínica foi criada no banco
        $this->assertDatabaseHas('clinics', [
            'email' => 'contato@novaclinica.com',
            'nif' => '500123456',
        ]);
        
        // Verificar se admin foi criado
        $this->assertDatabaseHas('users', [
            'email' => 'admin@novaclinica.com',
            'role' => 'admin',
        ]);
    }

    public function test_clinica_inativa_nao_consegue_fazer_login(): void
    {
        // Arrange: Criar clínica inativa
        $clinic = Clinic::factory()->create([
            'status' => 'inactive',
        ]);
        
        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'teste@clinica.com',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        // Act & Assert: Tentar login deve falhar
        $response = $this->postJson('/api/auth/login', [
            'email' => 'teste@clinica.com',
            'password' => 'password123',
            'clinic_id' => $clinic->id,
        ]);

        $response->assertStatus(422);
    }
}
