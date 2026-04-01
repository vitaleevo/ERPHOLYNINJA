<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabResultStoreRequest;
use App\Jobs\NotifyCriticalLabResult;
use App\Models\LabRequestItem;
use App\Models\LabResult;
use App\Models\LabTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LabResult::with(['test', 'requestItem.request.patient'])
            ->whereHas('requestItem.request', function ($q) use ($request) {
                $q->where('clinic_id', $request->user()->clinic_id);
            });

        // Filtros
        if ($request->has('patient_id')) {
            $query->whereHas('requestItem.request', function ($q) use ($request) {
                $q->where('patient_id', $request->patient_id);
            });
        }

        if ($request->has('test_id')) {
            $query->where('test_id', $request->test_id);
        }

        if ($request->has('is_abnormal')) {
            $query->where('is_abnormal', $request->boolean('is_abnormal'));
        }

        if ($request->has('is_critical')) {
            $query->where('is_critical', $request->boolean('is_critical'));
        }

        $results = $query->orderBy('result_datetime', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => 'Resultados de laboratório listados com sucesso',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LabResultStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // Obtém informações do teste para preencher valores padrão
            $test = LabTest::findOrFail($data['test_id']);
            
            $data['unit'] ??= $test->unit_of_measurement;
            $data['reference_min'] ??= $test->min_reference_value;
            $data['reference_max'] ??= $test->max_reference_value;
            $data['result_datetime'] ??= now();

            // Cria o resultado
            $result = LabResult::create($data);
            
            // Atualiza flags automaticamente
            if ($result->numeric_value !== null) {
                $result->updateFlags();
                
                // Dispara notificação se for valor crítico
                if ($result->is_critical) {
                    NotifyCriticalLabResult::dispatch($result);
                }
            }

            // Atualiza status do item para completo
            $requestItem = LabRequestItem::findOrFail($data['request_item_id']);
            $requestItem->update([
                'status' => 'completed',
                'technician_notes' => $request->technician_notes ?? null,
            ]);

            // Verifica se todos os itens estão completos para mudar status do pedido
            $labRequest = $requestItem->request;
            $allCompleted = $labRequest->items()->where('status', '!=', 'completed')->count() === 0;
            
            if ($allCompleted && $labRequest->status === 'in_progress') {
                $labRequest->update([
                    'status' => 'pending_validation',
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            $result->load(['test', 'requestItem.request.patient']);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Resultado registrado com sucesso',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar resultado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $result = LabResult::with([
            'test.category',
            'requestItem.request.patient',
            'requestItem.request.doctor'
        ])
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Resultado recuperado com sucesso',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LabResultStoreRequest $request, string $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $result = LabResult::findOrFail($id);
            
            $data = $request->validated();
            
            // Mantém unit e reference values se não foram fornecidos
            $data['unit'] ??= $result->unit;
            $data['reference_min'] ??= $result->reference_min;
            $data['reference_max'] ??= $result->reference_max;

            $result->update($data);
            
            // Atualiza flags automaticamente
            if ($result->numeric_value !== null) {
                $result->updateFlags();
            }

            DB::commit();

            $result->load(['test', 'requestItem.request.patient']);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Resultado atualizado com sucesso',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar resultado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk insert results for a request item
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'results' => ['required', 'array', 'min:1'],
            'results.*.request_item_id' => ['required', 'exists:lab_request_items,id'],
            'results.*.test_id' => ['required', 'exists:lab_tests,id'],
            'results.*.result_value' => ['nullable', 'string'],
            'results.*.numeric_value' => ['nullable', 'numeric'],
            'results.*.text_result' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $createdResults = [];
            
            foreach ($request->results as $resultData) {
                $test = LabTest::findOrFail($resultData['test_id']);
                
                $result = LabResult::create([
                    'request_item_id' => $resultData['request_item_id'],
                    'test_id' => $resultData['test_id'],
                    'result_value' => $resultData['result_value'] ?? null,
                    'numeric_value' => $resultData['numeric_value'] ?? null,
                    'text_result' => $resultData['text_result'] ?? null,
                    'unit' => $resultData['unit'] ?? $test->unit_of_measurement,
                    'reference_min' => $resultData['reference_min'] ?? $test->min_reference_value,
                    'reference_max' => $resultData['reference_max'] ?? $test->max_reference_value,
                    'interpretation' => $resultData['interpretation'] ?? null,
                    'comments' => $resultData['comments'] ?? null,
                    'result_datetime' => now(),
                ]);

                // Atualiza flags
                if ($result->numeric_value !== null) {
                    $result->updateFlags();
                }

                $createdResults[] = $result;
            }

            // Atualiza status dos itens
            $requestItemIds = collect($request->results)->pluck('request_item_id');
            LabRequestItem::whereIn('id', $requestItemIds)->update([
                'status' => 'completed',
            ]);

            // Verifica se pode validar o pedido
            $firstItem = LabRequestItem::find($requestItemIds->first());
            $labRequest = $firstItem->request;
            
            $allCompleted = $labRequest->items()->where('status', '!=', 'completed')->count() === 0;
            
            if ($allCompleted && $labRequest->status === 'in_progress') {
                $labRequest->update([
                    'status' => 'pending_validation',
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $createdResults,
                'message' => 'Resultados registrados com sucesso',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar resultados',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get patient results history
     */
    public function patientHistory(string $patientId): JsonResponse
    {
        $results = LabResult::with(['test.category', 'requestItem.request'])
            ->whereHas('requestItem.request', function ($q) use ($patientId) {
                $q->where('patient_id', $patientId)
                  ->where('status', 'validated');
            })
            ->orderBy('result_datetime', 'desc')
            ->get()
            ->groupBy('test_id');

        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => 'Histórico do paciente recuperado com sucesso',
        ]);
    }
}
