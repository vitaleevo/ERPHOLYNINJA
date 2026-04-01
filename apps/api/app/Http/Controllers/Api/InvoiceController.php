<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        
        $invoices = Invoice::where('clinic_id', $clinicId)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => 
                $q->whereHas('patient', fn($qp) => $qp->where('name', 'like', "%{$s}%"))
                  ->orWhere('invoice_number', 'like', "%{$s}%")
            )
            ->with(['patient', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($invoices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'observations' => 'nullable|string',
            'due_date' => 'required|date',
        ]);

        $clinicId = $request->header('X-Clinic-Id');
        
        // Criar fatura
        $invoice = Invoice::create([
            'clinic_id' => $clinicId,
            'patient_id' => $validated['patient_id'],
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'total_amount' => 0,
            'status' => 'draft',
            'issue_date' => now(),
            'due_date' => $validated['due_date'],
            'observations' => $validated['observations'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        // Adicionar itens
        foreach ($validated['items'] as $item) {
            $invoiceItem = InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        // Calcular total
        $invoice->calculateTotal();

        return response()->json($invoice->load(['patient', 'items']), 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load(['patient', 'items', 'creator']));
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'sometimes|exists:patients,id',
            'observations' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'status' => 'sometimes|in:draft,issued,paid,cancelled',
        ]);

        $invoice->update($validated);

        return response()->json($invoice->load(['patient', 'items']));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'error' => 'Não é possível excluir uma fatura paga.',
            ], 422);
        }

        $invoice->items()->delete();
        $invoice->delete();

        return response()->json(['message' => 'Fatura removida com sucesso']);
    }

    /**
     * Emitir fatura
     */
    public function issue(Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'error' => 'Apenas faturas em rascunho podem ser emitidas.',
            ], 422);
        }

        $invoice->issue();

        return response()->json([
            'message' => 'Fatura emitida com sucesso',
            'invoice' => $invoice->load(['patient', 'items']),
        ]);
    }

    /**
     * Marcar fatura como paga
     */
    public function markAsPaid(Invoice $invoice): JsonResponse
    {
        $invoice->markAsPaid();

        return response()->json([
            'message' => 'Fatura marcada como paga',
            'invoice' => $invoice,
        ]);
    }

    /**
     * Cancelar fatura
     */
    public function cancel(Invoice $invoice): JsonResponse
    {
        $invoice->cancel();

        return response()->json([
            'message' => 'Fatura cancelada',
            'invoice' => $invoice,
        ]);
    }
}
