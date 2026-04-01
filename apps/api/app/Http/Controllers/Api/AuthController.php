<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login do usuário e retorna token de acesso
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        $user = User::where('email', $request->email)
            ->where('clinic_id', $request->clinic_id)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Usuário está inativo. Contacte o administrador.'],
            ]);
        }

        // Revogar tokens antigos se necessário
        // $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'avatar' => $user->avatar,
                'is_active' => $user->is_active,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout do usuário (revoga token atual)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }

    /**
     * Registrar nova clínica e usuário admin
     */
    public function registerClinic(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Dados da clínica
            'clinic_name' => 'required|string|max:255',
            'clinic_email' => 'required|email|unique:clinics,email',
            'clinic_phone' => 'required|string',
            'clinic_nif' => 'required|string|unique:clinics,nif',
            'clinic_address' => 'nullable|string',
            
            // Dados do usuário admin
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|min:8|confirmed',
        ]);

        // Criar clínica
        $clinic = Clinic::create([
            'name' => $validated['clinic_name'],
            'slug' => $this->generateSlug($validated['clinic_name']),
            'email' => $validated['clinic_email'],
            'phone' => $validated['clinic_phone'],
            'nif' => $validated['clinic_nif'],
            'address' => $validated['clinic_address'] ?? null,
            'status' => 'active',
            'plan' => 'basic',
        ]);

        // Criar usuário admin
        $admin = User::create([
            'clinic_id' => $clinic->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Gerar token
        $token = $admin->createToken('auth-token')->plainTextToken;

        return response()->json([
            'clinic' => [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'email' => $clinic->email,
                'slug' => $clinic->slug,
            ],
            'user' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Registrar usuário em uma clínica existente (apenas admin)
     */
    public function registerUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,doctor,receptionist,pharmacist,accountant',
            'specialty_id' => 'nullable|exists:specialties,id',
            'phone' => 'nullable|string',
        ]);

        $clinicId = $request->header('X-Clinic-Id');
        
        $user = User::create([
            'clinic_id' => $clinicId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'specialty_id' => $request->specialty_id,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'message' => 'Usuário registrado com sucesso.',
        ], 201);
    }

    /**
     * Obter dados do usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['clinic', 'specialty']),
        ]);
    }

    /**
     * Obter estatísticas do dashboard
     */
    public function dashboardStats(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        $today = now()->format('Y-m-d');
        
        // Estatísticas básicas
        $appointmentsToday = \App\Models\Appointment::where('clinic_id', $clinicId)
            ->whereDate('date', $today)
            ->count();
            
        $totalPatients = \App\Models\Patient::where('clinic_id', $clinicId)->count();
        
        // Receita do mês (exemplo simplificado)
        $monthlyRevenue = \App\Models\Invoice::where('clinic_id', $clinicId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
            
        // Exames realizados na semana
        $examsCompleted = \App\Models\Consultation::where('clinic_id', $clinicId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotNull('exam_results')
            ->count();
        
        return response()->json([
            'appointments_today' => $appointmentsToday,
            'total_patients' => $totalPatients,
            'monthly_revenue' => $monthlyRevenue,
            'exams_completed' => $examsCompleted,
        ]);
    }

    /**
     * Gerar slug único para a clínica
     */
    private function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Clinic::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
