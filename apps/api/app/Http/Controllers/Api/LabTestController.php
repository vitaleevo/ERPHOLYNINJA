<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LabTestCreateRequest;
use App\Models\LabTest;
use App\Models\LabTestCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LabTest::with('category', 'equipment')
            ->where('clinic_id', $request->user()->clinic_id);

        // Filtros
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        $tests = $query->orderBy('sort_order')->orderBy('name')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $tests,
            'message' => 'Exames laboratoriais listados com sucesso',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LabTestCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['clinic_id'] = $request->user()->clinic_id;

        $test = LabTest::create($data);

        return response()->json([
            'success' => true,
            'data' => $test->load('category', 'equipment'),
            'message' => 'Exame laboratorial criado com sucesso',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $test = LabTest::with(['category', 'equipment', 'profiles', 'qualityControls'])
            ->where('clinic_id', request()->user()->clinic_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $test,
            'message' => 'Exame laboratorial recuperado com sucesso',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LabTestCreateRequest $request, string $id): JsonResponse
    {
        $test = LabTest::where('clinic_id', $request->user()->clinic_id)->findOrFail($id);
        
        $data = $request->validated();
        $test->update($data);

        return response()->json([
            'success' => true,
            'data' => $test->fresh(['category', 'equipment']),
            'message' => 'Exame laboratorial atualizado com sucesso',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $test = LabTest::where('clinic_id', request()->user()->clinic_id)->findOrFail($id);
        $test->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exame laboratorial eliminado com sucesso',
        ]);
    }

    /**
     * List all active tests for dropdowns
     */
    public function activeTests(): JsonResponse
    {
        $tests = LabTest::with('category')
            ->where('clinic_id', request()->user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($test) => [
                'id' => $test->id,
                'name' => $test->name,
                'code' => $test->code,
                'unit' => $test->unit_of_measurement,
                'category' => $test->category->name,
                'price' => $test->price,
            ]);

        return response()->json([
            'success' => true,
            'data' => $tests,
        ]);
    }
}
