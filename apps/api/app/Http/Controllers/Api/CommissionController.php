<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        
        $commissions = Commission::where('clinic_id', $clinicId)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->doctor_id, fn($q, $d) => $q->where('doctor_id', $d))
            ->when($request->month, fn($q, $m) => 
                $q->whereYear('reference_month', '=', date('Y', strtotime($m)))
                  ->whereMonth('reference_month', '=', date('m', strtotime($m)))
            )
            ->with(['doctor', 'consultation'])
            ->orderBy('reference_month', 'desc')
            ->paginate(20);

        return response()->json($commissions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'amount' => 'required|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'reference_month' => 'required|date',
            'observations' => 'nullable|string',
        ]);

        $validated['clinic_id'] = $request->header('X-Clinic-Id');
        $validated['status'] = 'pending';

        $commission = Commission::create($validated);

        return response()->json($commission->load(['doctor', 'consultation']), 201);
    }

    public function show(Commission $commission): JsonResponse
    {
        return response()->json($commission->load(['doctor', 'consultation']));
    }

    public function update(Request $request, Commission $commission): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'percentage' => 'sometimes|numeric|min:0|max:100',
            'reference_month' => 'sometimes|date',
            'observations' => 'nullable|string',
            'status' => 'sometimes|in:pending,paid,cancelled',
        ]);

        $commission->update($validated);

        return response()->json($commission->load(['doctor', 'consultation']));
    }

    public function destroy(Commission $commission): JsonResponse
    {
        $commission->delete();
        return response()->json(['message' => 'Comissão removida com sucesso']);
    }

    /**
     * Marcar comissão como paga
     */
    public function markAsPaid(Request $request, Commission $commission): JsonResponse
    {
        $validated = $request->validate([
            'payment_date' => 'sometimes|date',
        ]);

        $commission->markAsPaid($validated['payment_date'] ?? null);

        return response()->json([
            'message' => 'Comissão marcada como paga',
            'commission' => $commission,
        ]);
    }

    /**
     * Cancelar comissão
     */
    public function cancel(Commission $commission): JsonResponse
    {
        $commission->cancel();

        return response()->json([
            'message' => 'Comissão cancelada',
            'commission' => $commission,
        ]);
    }

    /**
     * Resumo de comissões (dashboard)
     */
    public function summary(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        $month = $request->input('month', date('Y-m'));

        $totalPending = Commission::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->sum('amount');

        $totalPaid = Commission::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereYear('payment_date', '=', date('Y', strtotime($month)))
            ->whereMonth('payment_date', '=', date('m', strtotime($month)))
            ->sum('amount');

        // Agrupar por médico
        $byDoctor = Commission::where('clinic_id', $clinicId)
            ->selectRaw('doctor_id, SUM(amount) as total')
            ->groupBy('doctor_id')
            ->with('doctor')
            ->get();

        return response()->json([
            'month' => $month,
            'pending' => number_format($totalPending, 2),
            'paid' => number_format($totalPaid, 2),
            'total' => number_format($totalPending + $totalPaid, 2),
            'by_doctor' => $byDoctor,
        ]);
    }

    /**
     * Calcular comissão automaticamente de uma consulta
     */
    public function calculateFromConsultation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        $consultation = Consultation::findOrFail($validated['consultation_id']);
        $amount = Commission::calculateFromConsultation($consultation, $validated['percentage']);

        return response()->json([
            'consultation_id' => $consultation->id,
            'consultation_value' => number_format($consultation->value ?? 0, 2),
            'percentage' => $validated['percentage'],
            'commission_amount' => number_format($amount, 2),
        ]);
    }
}
