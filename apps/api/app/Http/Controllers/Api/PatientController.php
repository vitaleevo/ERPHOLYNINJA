<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patients = Patient::where('clinic_id', $request->header('X-Clinic-Id'))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20);

        return response()->json($patients);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'nif' => 'nullable|string',
            'bi_number' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'emergency_phone' => 'nullable|string',
            'insurance_id' => 'nullable|exists:insurances,id',
            'insurance_number' => 'nullable|string',
        ]);

        $validated['clinic_id'] = $request->header('X-Clinic-Id');
        $patient = Patient::create($validated);

        return response()->json($patient, 201);
    }

    public function show(Patient $patient): JsonResponse
    {
        return response()->json($patient->load(['insurance', 'appointments', 'consultations']));
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'sometimes|string',
            'nif' => 'nullable|string',
            'bi_number' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'emergency_phone' => 'nullable|string',
            'insurance_id' => 'nullable|exists:insurances,id',
            'insurance_number' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $patient->update($validated);

        return response()->json($patient);
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();
        return response()->json(['message' => 'Patient deleted']);
    }
}
