<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PrescriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $prescriptions = Prescription::where('clinic_id', $request->header('X-Clinic-Id'))
            ->with(['patient', 'doctor', 'items'])
            ->when($request->patient_id, fn($q, $id) => $q->where('patient_id', $id))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($prescriptions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'notes' => 'nullable|string',
            'is_digital_signature' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.medication' => 'required|string',
            'items.*.dosage' => 'required|string',
            'items.*.frequency' => 'required|string',
            'items.*.duration_days' => 'nullable|integer',
            'items.*.instructions' => 'nullable|string',
        ]);

        $validated['clinic_id'] = $request->header('X-Clinic-Id');
        $validated['doctor_id'] = $request->user()->id;

        $prescription = Prescription::create($validated);

        foreach ($validated['items'] as $item) {
            $prescription->items()->create($item);
        }

        return response()->json($prescription->load(['patient', 'doctor', 'items']), 201);
    }

    public function show(Prescription $prescription): JsonResponse
    {
        return response()->json($prescription->load(['patient', 'doctor', 'items']));
    }
}
