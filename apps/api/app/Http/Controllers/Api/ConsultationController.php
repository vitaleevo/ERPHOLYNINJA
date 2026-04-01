<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConsultationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $consultations = Consultation::where('clinic_id', $request->header('X-Clinic-Id'))
            ->with(['patient', 'doctor'])
            ->when($request->patient_id, fn($q, $id) => $q->where('patient_id', $id))
            ->when($request->doctor_id, fn($q, $id) => $q->where('doctor_id', $id))
            ->orderByDesc('started_at')
            ->paginate(20);

        return response()->json($consultations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'started_at' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'symptoms' => 'nullable|string',
        ]);

        $validated['clinic_id'] = $request->header('X-Clinic-Id');
        $validated['status'] = 'in_progress';

        $consultation = Consultation::create($validated);

        if ($validated['appointment_id']) {
            \App\Models\Appointment::where('id', $validated['appointment_id'])->update(['status' => 'in_progress']);
        }

        return response()->json($consultation->load(['patient', 'doctor']), 201);
    }

    public function show(Consultation $consultation): JsonResponse
    {
        return response()->json($consultation->load(['patient', 'doctor', 'medicalRecords', 'prescriptions']));
    }

    public function update(Request $request, Consultation $consultation): JsonResponse
    {
        $validated = $request->validate([
            'ended_at' => 'nullable|date',
            'chief_complaint' => 'nullable|string',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'observations' => 'nullable|string',
            'status' => 'nullable|in:in_progress,completed',
        ]);

        $consultation->update($validated);

        if (($validated['status'] ?? '') === 'completed' && $consultation->appointment) {
            $consultation->appointment->update(['status' => 'completed']);
        }

        return response()->json($consultation);
    }

    public function addMedicalRecord(Request $request, Consultation $consultation): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string',
            'attachments' => 'nullable|array',
        ]);

        $validated['consultation_id'] = $consultation->id;
        $validated['patient_id'] = $consultation->patient_id;
        $validated['doctor_id'] = $consultation->doctor_id;

        $record = MedicalRecord::create($validated);

        return response()->json($record, 201);
    }
}
