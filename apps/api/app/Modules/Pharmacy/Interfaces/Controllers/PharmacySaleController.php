<?php

namespace App\Modules\Pharmacy\Interfaces\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pharmacy\Application\Services\PharmacySaleService;
use App\Modules\Pharmacy\Application\DTOs\CreatePharmacySaleDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacySaleController extends Controller
{
    public function __construct(
        private PharmacySaleService $service
    ) {}

    /**
     * Listar todas as vendas de farmácia
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'clinic_id',
            'status',
            'patient_id',
            'start_date',
            'end_date'
        ]);

        $sales = $this->service->listSales($filters);

        return response()->json([
            'data' => $sales,
        ]);
    }

    /**
     * Buscar venda específica por ID
     */
    public function show(int $id): JsonResponse
    {
        $sale = $this->service->findSale($id);

        if (!$sale) {
            return response()->json([
                'message' => 'Venda não encontrada'
            ], 404);
        }

        return response()->json([
            'data' => $sale->toArray(),
        ]);
    }

    /**
     * Criar nova venda de farmácia
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validar request (em produção, usar Form Request)
            $validated = $request->validate([
                'clinic_id' => 'required|integer|exists:clinics,id',
                'patient_id' => 'required|integer|exists:patients,id',
                'prescription_id' => 'nullable|integer|exists:prescriptions,id',
                'items' => 'required|array|min:1',
                'items.*.medication_id' => 'required|integer|exists:medications,id',
                'items.*.medication_batch_id' => 'required|integer|exists:medication_batches,id',
                'items.*.medication_name' => 'required|string|max:255',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
                'payment_method' => 'required|in:cash,card,transfer,multicaixa,insurance',
                'discount' => 'nullable|numeric|min:0',
                'observations' => 'nullable|string',
            ]);

            // Criar DTO a partir da request
            $dto = CreatePharmacySaleDTO::fromRequest($validated);

            // Executar serviço
            $sale = $this->service->createSale($dto);

            return response()->json([
                'message' => 'Venda criada com sucesso',
                'data' => $sale->toArray(),
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar venda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar venda
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $this->service->cancelSale($id);

            return response()->json([
                'message' => 'Venda cancelada com sucesso',
            ]);

        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao cancelar venda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter resumo de vendas por período
     */
    public function summary(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        $summary = $this->service->getSalesSummary($startDate, $endDate);

        return response()->json([
            'data' => $summary,
        ]);
    }
}
