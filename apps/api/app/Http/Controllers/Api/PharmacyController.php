<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use App\Models\MedicationBatch;
use App\Models\PharmacyStock;
use App\Models\PharmacySale;
use App\Models\PharmacySaleItem;
use App\Models\PharmacyAlert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    /**
     * Listar medicamentos da clínica
     */
    public function medications(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        
        $medications = Medication::where('clinic_id', $clinicId)
            ->when($request->search, fn($q, $s) => 
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('generic_name', 'like', "%{$s}%")
            )
            ->when($request->requires_prescription !== null, fn($q) => 
                $q->where('requires_prescription', $request->requires_prescription)
            )
            ->withCount(['batches as total_stock' => fn($q) => 
                $q->select(DB::raw('SUM(current_quantity)'))
            ])
            ->orderBy('name')
            ->paginate(20);

        return response()->json($medications);
    }

    /**
     * Criar novo medicamento
     */
    public function storeMedication(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:100',
            'composition' => 'nullable|string',
            'manufacturer' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'requires_prescription' => 'boolean',
            'requires_special_control' => 'boolean',
            'indications' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'reference_price' => 'nullable|numeric|min:0',
        ]);

        $validated['clinic_id'] = $request->header('X-Clinic-Id');
        $validated['is_active'] = true;

        $medication = Medication::create($validated);

        return response()->json($medication, 201);
    }

    /**
     * Mostrar detalhes do medicamento com estoque
     */
    public function showMedication(Medication $medication): JsonResponse
    {
        return response()->json($medication->load([
            'batches' => fn($q) => $q->orderBy('expiry_date'),
            'stockMovements' => fn($q) => $q->latest()->limit(10),
        ]));
    }

    /**
     * Adicionar/atualizar lote de medicamento
     */
    public function storeBatch(Request $request, Medication $medication): JsonResponse
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|max:100',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'initial_quantity' => 'required|integer|min:1',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'storage_location' => 'nullable|string|max:255',
        ]);

        $clinicId = $request->header('X-Clinic-Id');
        
        DB::beginTransaction();
        try {
            // Criar lote
            $batch = MedicationBatch::create([
                'medication_id' => $medication->id,
                'clinic_id' => $clinicId,
                'batch_number' => $validated['batch_number'],
                'manufacturing_date' => $validated['manufacturing_date'] ?? null,
                'expiry_date' => $validated['expiry_date'],
                'initial_quantity' => $validated['initial_quantity'],
                'current_quantity' => $validated['initial_quantity'],
                'cost_price' => $validated['cost_price'] ?? null,
                'sale_price' => $validated['sale_price'],
                'storage_location' => $validated['storage_location'] ?? null,
                'status' => 'active',
            ]);

            // Registrar entrada no estoque
            PharmacyStock::create([
                'clinic_id' => $clinicId,
                'medication_id' => $medication->id,
                'batch_id' => $batch->id,
                'user_id' => $request->user()->id,
                'type' => 'entry',
                'quantity' => $validated['initial_quantity'],
                'balance_after' => $validated['initial_quantity'],
                'unit_cost' => $validated['cost_price'] ?? null,
                'reference_type' => 'Batch',
                'reference_id' => $batch->id,
                'reason' => 'Entrada de lote',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Lote adicionado com sucesso',
                'batch' => $batch,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erro ao adicionar lote',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Realizar venda/dispensação
     */
    public function storeSale(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'items' => 'required|array|min:1',
            'items.*.medication_id' => 'required|exists:medications,id',
            'items.*.batch_id' => 'required|exists:medication_batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.instructions' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,card,transfer,multicaixa,insurance,credit',
            'discount' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string',
        ]);

        $clinicId = $request->header('X-Clinic-Id');
        
        DB::beginTransaction();
        try {
            // Criar venda
            $sale = PharmacySale::create([
                'clinic_id' => $clinicId,
                'patient_id' => $validated['patient_id'] ?? null,
                'prescription_id' => $validated['prescription_id'] ?? null,
                'user_id' => $request->user()->id,
                'invoice_number' => PharmacySale::generateInvoiceNumber(),
                'subtotal' => 0,
                'discount' => $validated['discount'] ?? 0,
                'total' => 0,
                'payment_method' => $validated['payment_method'] ?? null,
                'status' => 'completed',
                'observations' => $validated['observations'] ?? null,
            ]);

            // Adicionar itens e baixar estoque
            foreach ($validated['items'] as $item) {
                $batch = MedicationBatch::findOrFail($item['batch_id']);
                
                // Verificar se há estoque suficiente
                if ($batch->current_quantity < $item['quantity']) {
                    throw new \Exception("Estoque insuficiente para {$batch->medication->name}");
                }

                // Baixar do lote
                $batch->updateQuantity(-$item['quantity']);

                // Criar item da venda
                $saleItem = PharmacySaleItem::create([
                    'pharmacy_sale_id' => $sale->id,
                    'medication_id' => $item['medication_id'],
                    'medication_batch_id' => $item['batch_id'],
                    'medication_name' => $batch->medication->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0),
                    'dosage' => $batch->medication->dosage,
                    'instructions' => $item['instructions'] ?? null,
                ]);

                // Registrar saída no estoque
                PharmacyStock::create([
                    'clinic_id' => $clinicId,
                    'medication_id' => $item['medication_id'],
                    'batch_id' => $item['batch_id'],
                    'user_id' => $request->user()->id,
                    'type' => 'exit',
                    'quantity' => -$item['quantity'],
                    'balance_after' => $batch->current_quantity,
                    'unit_cost' => $item['unit_price'],
                    'reference_type' => 'Sale',
                    'reference_id' => $sale->id,
                    'reason' => 'Venda',
                ]);
            }

            // Calcular totais
            $sale->calculateTotals();

            DB::commit();

            return response()->json([
                'message' => 'Venda realizada com sucesso',
                'sale' => $sale->load(['items', 'patient']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erro ao realizar venda',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obter alertas da farmácia
     */
    public function alerts(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        
        $alerts = [
            'low_stock' => [],
            'expiring_soon' => [],
            'expired' => [],
            'out_of_stock' => [],
        ];

        // Medicamentos com estoque baixo
        $medications = Medication::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->get();

        foreach ($medications as $medication) {
            $totalStock = $medication->getTotalStock();
            
            if ($totalStock === 0) {
                $alerts['out_of_stock'][] = $medication;
            } elseif ($totalStock < 10) {
                $alerts['low_stock'][] = $medication;
            }

            // Verificar lotes próximos do vencimento
            $expiringBatches = $medication->batches()
                ->where('status', 'active')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->get();

            foreach ($expiringBatches as $batch) {
                $alerts['expiring_soon'][] = [
                    'medication' => $medication,
                    'batch' => $batch,
                    'days_to_expire' => now()->diffInDays($batch->expiry_date),
                ];
            }

            // Verificar lotes vencidos
            $expiredBatches = $medication->batches()
                ->where('expiry_date', '<', now())
                ->get();

            foreach ($expiredBatches as $batch) {
                $alerts['expired'][] = [
                    'medication' => $medication,
                    'batch' => $batch,
                    'days_expired' => now()->diffInDays($batch->expiry_date),
                ];
            }
        }

        return response()->json($alerts);
    }

    /**
     * Resumo do estoque
     */
    public function stockSummary(Request $request): JsonResponse
    {
        $clinicId = $request->header('X-Clinic-Id');
        
        $totalMedications = Medication::where('clinic_id', $clinicId)->where('is_active', true)->count();
        $totalBatches = MedicationBatch::where('clinic_id', $clinicId)->where('status', 'active')->count();
        $totalSales = PharmacySale::where('clinic_id', $clinicId)
            ->whereDate('created_at', today())
            ->count();
        $salesRevenue = PharmacySale::where('clinic_id', $clinicId)
            ->whereDate('created_at', today())
            ->sum('total');

        $lowStockCount = Medication::where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->get()
            ->filter(fn($m) => $m->isLowStock())
            ->count();

        return response()->json([
            'total_medications' => $totalMedications,
            'total_batches' => $totalBatches,
            'sales_today' => $totalSales,
            'revenue_today' => number_format($salesRevenue, 2),
            'low_stock_count' => $lowStockCount,
        ]);
    }
}
