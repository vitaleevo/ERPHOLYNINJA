<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AccountPayableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        
        $accounts = AccountPayable::where('clinic_id', $clinicId)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->search, fn($q, $s) => 
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('supplier', 'like', "%{$s}%")
            )
            ->orderBy('due_date', 'desc')
            ->paginate(20);

        return response()->json($accounts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
        ]);

        $validated['clinic_id'] = $request->header('X-Clinic-Id');
        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'pending';

        $account = AccountPayable::create($validated);

        return response()->json($account, 201);
    }

    public function show(AccountPayable $accountPayable): JsonResponse
    {
        return response()->json($accountPayable->load(['creator']));
    }

    public function update(Request $request, AccountPayable $accountPayable): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'due_date' => 'sometimes|date',
            'category' => 'nullable|string|max:100',
            'supplier' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'status' => 'sometimes|in:pending,paid,overdue,cancelled',
        ]);

        $accountPayable->update($validated);

        return response()->json($accountPayable);
    }

    public function destroy(AccountPayable $accountPayable): JsonResponse
    {
        $accountPayable->delete();
        return response()->json(['message' => 'Conta a pagar removida com sucesso']);
    }

    /**
     * Marcar conta como paga
     */
    public function markAsPaid(Request $request, AccountPayable $accountPayable): JsonResponse
    {
        $validated = $request->validate([
            'payment_date' => 'sometimes|date',
        ]);

        $accountPayable->markAsPaid($validated['payment_date'] ?? null);

        return response()->json([
            'message' => 'Conta marcada como paga',
            'account' => $accountPayable,
        ]);
    }

    /**
     * Cancelar conta
     */
    public function cancel(AccountPayable $accountPayable): JsonResponse
    {
        $accountPayable->cancel();

        return response()->json([
            'message' => 'Conta cancelada',
            'account' => $accountPayable,
        ]);
    }

    /**
     * Resumo financeiro (dashboard)
     */
    public function summary(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        $month = $request->input('month', date('Y-m'));

        $totalPending = AccountPayable::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->sum('amount');

        $totalPaid = AccountPayable::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereYear('payment_date', '=', date('Y', strtotime($month)))
            ->whereMonth('payment_date', '=', date('m', strtotime($month)))
            ->sum('amount');

        $totalOverdue = AccountPayable::where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('amount');

        return response()->json([
            'month' => $month,
            'pending' => number_format($totalPending, 2),
            'paid' => number_format($totalPaid, 2),
            'overdue' => number_format($totalOverdue, 2),
            'total' => number_format($totalPending + $totalPaid + $totalOverdue, 2),
        ]);
    }
}
