<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabRequestCreateRequest;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabTest;
use App\Models\LabTestProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LabRequest::with(['patient', 'doctor', 'technician', 'validator', 'items.test'])
            ->where('clinic_id', $request->user()->clinic_id);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('accession_number')) {
            $query->where('accession_number', 'LIKE', "%{$request->accession_number}%");
        }

        if ($request->has('barcode')) {
            $query->where('barcode', $request->barcode);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $requests,
            'message' => 'Pedidos de laboratório listados com sucesso',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LabRequestCreateRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['clinic_id'] = $request->user()->clinic_id;
            $data['accession_number'] = LabRequest::generateAccessionNumber();
            $data['barcode'] = LabRequest::generateBarcode();
            
            // Define data prevista de entrega baseada no tempo de processamento
            $items = $data['items'];
            $maxTurnaround = 0;
            foreach ($items as $item) {
                $test = LabTest::find($item['test_id']);
                if ($test && $test->turnaround_time_hours > $maxTurnaround) {
                    $maxTurnaround = $test->turnaround_time_hours;
                }
            }
            $data['expected_delivery_at'] = now()->addHours($maxTurnaround);

            $labRequest = LabRequest::create($data);

            // Cria os itens do pedido
            foreach ($items as $item) {
                LabRequestItem::create([
                    'request_id' => $labRequest->id,
                    'test_id' => $item['test_id'],
                    'profile_id' => $item['profile_id'] ?? null,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            $labRequest->load(['patient', 'doctor', 'items.test.category']);

            return response()->json([
                'success' => true,
                'data' => $labRequest,
                'message' => 'Pedido de laboratório criado com sucesso',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pedido de laboratório',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $request = LabRequest::with([
            'patient', 
            'doctor', 
            'technician', 
            'validator',
            'consultation',
            'items.test.category',
            'items.profile',
            'items.results'
        ])
        ->where('clinic_id', request()->user()->clinic_id)
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $request,
            'message' => 'Pedido de laboratório recuperado com sucesso',
        ]);
    }

    /**
     * Update status of the request
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending_collection,collected,in_progress,pending_validation,validated,rejected,cancelled'],
            'rejection_reason' => ['nullable', 'string', 'required_if:status,rejected'],
        ]);

        $labRequest = LabRequest::where('clinic_id', $request->user()->clinic_id)->findOrFail($id);
        
        $labRequest->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason ?? null,
        ]);

        // Atualiza timestamps específicos conforme o status
        if ($request->status === 'collected') {
            $labRequest->update(['collection_datetime' => now()]);
        } elseif ($request->status === 'in_progress') {
            $labRequest->update(['started_at' => now()]);
        } elseif ($request->status === 'pending_validation') {
            $labRequest->update(['completed_at' => now()]);
        } elseif ($request->status === 'validated') {
            $labRequest->update(['validated_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'data' => $labRequest->fresh(['patient', 'doctor', 'items.test']),
            'message' => 'Status do pedido atualizado com sucesso',
        ]);
    }

    /**
     * Assign technician to request
     */
    public function assignTechnician(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'technician_id' => ['required', 'exists:users,id'],
        ]);

        $labRequest = LabRequest::where('clinic_id', $request->user()->clinic_id)->findOrFail($id);
        
        $labRequest->update([
            'technician_id' => $request->technician_id,
            'status' => 'in_progress',
            'received_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $labRequest->fresh(['technician']),
            'message' => 'Técnico atribuído com sucesso',
        ]);
    }

    /**
     * Validate request (Director Técnico)
     */
    public function validateRequest(string $id): JsonResponse
    {
        $labRequest = LabRequest::where('clinic_id', request()->user()->clinic_id)->findOrFail($id);

        if (!$labRequest->canBeValidated()) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido não está pronto para validação. Todos os itens devem estar completos.',
            ], 422);
        }

        $labRequest->update([
            'validator_id' => request()->user()->id,
            'status' => 'validated',
            'validated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $labRequest->fresh(['validator', 'patient', 'items.test']),
            'message' => 'Pedido validado com sucesso',
        ]);
    }

    /**
     * Reject request
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        $labRequest = LabRequest::where('clinic_id', $request->user()->clinic_id)->findOrFail($id);
        
        $labRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'data' => $labRequest,
            'message' => 'Pedido rejeitado com sucesso',
        ]);
    }
}
