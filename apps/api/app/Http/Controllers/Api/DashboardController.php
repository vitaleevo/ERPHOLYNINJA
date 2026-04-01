<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Obter estatísticas do dashboard com cache
     */
    public function stats(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        
        // Cache por 5 minutos
        $stats = Cache::remember(
            "dashboard.stats.{$clinicId}",
            now()->addMinutes(5),
            function () use ($clinicId) {
                return [
                    'total_patients' => Patient::where('clinic_id', $clinicId)->count(),
                    'appointments_today' => Appointment::where('clinic_id', $clinicId)
                        ->whereDate('scheduled_at', today())
                        ->count(),
                    'appointments_pending' => Appointment::where('clinic_id', $clinicId)
                        ->where('status', 'pending')
                        ->count(),
                    'revenue_month' => \App\Models\AccountReceivable::where('clinic_id', $clinicId)
                        ->where('status', 'received')
                        ->whereMonth('payment_date', now()->month)
                        ->sum('amount'),
                ];
            }
        );

        return response()->json($stats);
    }

    /**
     * Limpar cache do dashboard
     */
    public function clearCache(): JsonResponse
    {
        $clinicId = request()->header('X-Clinic-Id');
        
        Cache::forget("dashboard.stats.{$clinicId}");
        
        return response()->json([
            'message' => 'Cache limpo com sucesso',
        ]);
    }
}
