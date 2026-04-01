<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountReceivable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AccountReceivableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        
        $accounts = AccountReceivable::where('clinic_id', $clinicId)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->payment_method, fn($q, $m) => $q->where('payment_method', $m))
            ->when($request->search, fn($q, $s) => 
                $q->whereHas('patient', fn($qp) => $qp->where('name', 'like', "%{$s}%"))
                  ->orWhere('description', 'like', "%{$s}%")
            )
            ->with(['patient'])
            ->orderBy('due_date', 'desc')
            ->paginate(20);

        return response()->json($accounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'payment_method' => 'nullable|in:cash,card,transfer,multicaixa,insurance',
            'reference' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
        ]);

        $validated['clinic_id'] = $request->header('X-Clinic-Id');
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'pending';

        $account = AccountReceivable::create($validated);

        return response()->json($account->load(['patient']), 201);
    }

    public function show(AccountReceivable $accountReceivable): JsonResponse
    {
        return response()->json($accountReceivable->load(['patient', 'creator']));
    }

    public function update(Request $request, AccountReceivable $accountReceivable): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'payment_method' => 'nullable|in:cash,card,transfer,multicaixa,insurance',
            'reference' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
            'status' => 'sometimes|in:pending,received,overdue,cancelled',
        ]);

        $accountReceivable->update($validated);

        return response()->json($accountReceivable->load(['patient']));
    }

    public function destroy(AccountReceivable $accountReceivable): JsonResponse
    {
        $accountReceivable->delete();
        return response()->json(['message' => 'Conta a receber removida com sucesso']);
    }

    /**
     * Marcar conta como recebida
     */
    public function markAsReceived(Request $request, AccountReceivable $accountReceivable): JsonResponse
    {
        $validated = $request->validate([
            'payment_date' => 'sometimes|date',
            'payment_method' => 'required|in:cash,card,transfer,multicaixa,insurance',
        ]);

        $accountReceivable->markAsReceived(
            $validated['payment_date'] ?? null,
            $validated['payment_method']
        );

        return response()->json([
            'message' => 'Conta marcada como recebida',
            'account' => $accountReceivable->load(['patient']),
        ]);
    }

    /**
     * Cancelar conta
     */
    public function cancel(AccountReceivable $accountReceivable): JsonResponse
    {
        $accountReceivable->cancel();

        return response()->json([
            'message' => 'Conta cancelada',
            'account' => $accountReceivable,
        ]);
    }

    /**
     * Resumo financeiro (dashboard)
     */
    public function summary(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        $month = $request->input('month', date('Y-m'));

        $totalPending = AccountReceivable::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->sum('amount');

        $totalReceived = AccountReceivable::where('clinic_id', $clinicId)
            ->where('status', 'received')
            ->whereYear('payment_date', '=', date('Y', strtotime($month)))
            ->whereMonth('payment_date', '=', date('m', strtotime($month)))
            ->sum('amount');

        $totalOverdue = AccountReceivable::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('amount');

        // Agrupar por método de pagamento
        $byPaymentMethod = AccountReceivable::where('clinic_id', $clinicId)
            ->where('status', 'received')
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'month' => $month,
            'pending' => number_format($totalPending, 2),
            'received' => number_format($totalReceived, 2),
            'overdue' => number_format($totalOverdue, 2),
            'total' => number_format($totalPending + $totalReceived + $totalOverdue, 2),
            'by_payment_method' => $byPaymentMethod,
        ]);
    }
}
