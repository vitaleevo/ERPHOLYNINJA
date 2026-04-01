<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::where('clinic_id', $request->header('X-Clinic-Id'))
            ->with(['patient', 'doctor', 'specialty']);

        if ($request->date) {
            $query->whereDate('scheduled_at', $request->date);
        }

        if ($request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('scheduled_at')->get();

        return response()->json($appointments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'specialty_id' => 'nullable|exists:specialties,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:5|max:120',
            'notes' => 'nullable|string',
            'room' => 'nullable|string',
        ]);

        $validated['clinic_id'] = $request->header('X-Clinic-Id');
        $validated['status'] = 'scheduled';

        $appointment = Appointment::create($validated);

        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'action' => 'created',
            'description' => 'Appointment scheduled',
            'user_id' => $request->user()->id,
        ]);

        return response()->json($appointment->load(['patient', 'doctor']), 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        return response()->json($appointment->load(['patient', 'doctor', 'specialty', 'logs']));
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'scheduled_at' => 'sometimes|date',
            'duration_minutes' => 'nullable|integer|min:5|max:120',
            'status' => 'sometimes|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'room' => 'nullable|string',
        ]);

        $oldStatus = $appointment->status;
        $appointment->update($validated);

        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            AppointmentLog::create([
                'appointment_id' => $appointment->id,
                'action' => 'status_changed',
                'description' => "Status changed from {$oldStatus} to {$validated['status']}",
                'user_id' => $request->user()->id,
            ]);
        }

        return response()->json($appointment);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted']);
    }
}
