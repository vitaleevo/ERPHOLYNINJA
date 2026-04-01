<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabTestProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $profiles = LabTestProfile::with(['labTests.category'])
            ->where('clinic_id', $request->user()->clinic_id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $profiles,
            'message' => 'Perfis de exames listados com sucesso',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:lab_test_profiles,code'],
            'description' => ['nullable', 'string'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'is_discountable' => ['boolean'],
            'discount_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'test_ids' => ['required', 'array', 'min:1'],
            'test_ids.*' => ['exists:lab_tests,id'],
        ]);

        DB::beginTransaction();
        try {
            $profile = LabTestProfile::create([
                'clinic_id' => $request->user()->clinic_id,
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'] ?? null,
                'total_price' => $validated['total_price'],
                'is_discountable' => $validated['is_discountable'] ?? true,
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
            ]);

            // Anexar exames ao perfil
            $profile->labTests()->attach($validated['test_ids']);

            DB::commit();

            $profile->load(['labTests.category']);

            return response()->json([
                'success' => true,
                'data' => $profile,
                'message' => 'Perfil de exame criado com sucesso',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar perfil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $profile = LabTestProfile::with(['labTests.category'])
            ->where('clinic_id', request()->user()->clinic_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $profile,
            'message' => 'Perfil recuperado com sucesso',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $profile = LabTestProfile::where('clinic_id', $request->user()->clinic_id)->findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:lab_test_profiles,code,' . $id],
            'description' => ['nullable', 'string'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'is_discountable' => ['boolean'],
            'discount_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'test_ids' => ['sometimes', 'array', 'min:1'],
            'test_ids.*' => ['exists:lab_tests,id'],
        ]);

        DB::beginTransaction();
        try {
            $profile->update($validated);

            if (isset($validated['test_ids'])) {
                $profile->labTests()->sync($validated['test_ids']);
            }

            DB::commit();

            $profile->load(['labTests.category']);

            return response()->json([
                'success' => true,
                'data' => $profile,
                'message' => 'Perfil atualizado com sucesso',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $profile = LabTestProfile::where('clinic_id', request()->user()->clinic_id)->findOrFail($id);
        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perfil eliminado com sucesso',
        ]);
    }
}
