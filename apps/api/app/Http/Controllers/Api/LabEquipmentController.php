<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabEquipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabEquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LabEquipment::withCount(['labTests', 'qualityControls'])
            ->where('clinic_id', $request->user()->clinic_id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('needs_calibration')) {
            $equipment = $query->get();
            if ($request->boolean('needs_calibration')) {
                $equipment = $equipment->filter(fn($e) => $e->needsCalibration());
            }
        } else {
            $equipment = $query->orderBy('name')->get();
        }

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipamentos de laboratório listados com sucesso',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'asset_tag' => ['nullable', 'string', 'unique:lab_equipment,asset_tag'],
            'status' => ['nullable', 'in:active,maintenance,inactive,broken'],
            'purchase_date' => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'calibration_date' => ['nullable', 'date'],
            'next_calibration_due' => ['nullable', 'date'],
            'specifications' => ['nullable', 'string'],
            'maintenance_notes' => ['nullable', 'string'],
            'requires_calibration' => ['boolean'],
            'calibration_interval_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['clinic_id'] = $request->user()->clinic_id;

        $equipment = LabEquipment::create($validated);

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipamento criado com sucesso',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $equipment = LabEquipment::with(['labTests', 'qualityControls.technician'])
            ->where('clinic_id', request()->user()->clinic_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipamento recuperado com sucesso',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $equipment = LabEquipment::where('clinic_id', $request->user()->clinic_id)->findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'asset_tag' => ['nullable', 'string', 'unique:lab_equipment,asset_tag,' . $id],
            'status' => ['nullable', 'in:active,maintenance,inactive,broken'],
            'purchase_date' => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'calibration_date' => ['nullable', 'date'],
            'next_calibration_due' => ['nullable', 'date'],
            'specifications' => ['nullable', 'string'],
            'maintenance_notes' => ['nullable', 'string'],
            'requires_calibration' => ['boolean'],
            'calibration_interval_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $equipment->update($validated);

        // Atualiza data de calibração se foi feita hoje
        if ($request->has('calibration_performed') && $request->boolean('calibration_performed')) {
            $equipment->update([
                'calibration_date' => now(),
                'next_calibration_due' => now()->addDays($equipment->calibration_interval_days),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $equipment->fresh(),
            'message' => 'Equipamento atualizado com sucesso',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $equipment = LabEquipment::where('clinic_id', request()->user()->clinic_id)->findOrFail($id);
        $equipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Equipamento eliminado com sucesso',
        ]);
    }

    /**
     * Get equipment needing calibration
     */
    public function needsCalibration(): JsonResponse
    {
        $equipment = LabEquipment::where('clinic_id', request()->user()->clinic_id)
            ->where('requires_calibration', true)
            ->get()
            ->filter(fn($e) => $e->needsCalibration());

        return response()->json([
            'success' => true,
            'data' => $equipment,
            'message' => 'Equipamentos precisando de calibração',
        ]);
    }
}
