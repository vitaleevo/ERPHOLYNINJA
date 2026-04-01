<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabTestCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = LabTestCategory::withCount(['labTests' => function ($query) {
            $query->where('is_active', true);
        }])
        ->where('clinic_id', $request->user()->clinic_id)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Categorias de exames listadas com sucesso',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:lab_test_categories,code'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['clinic_id'] = $request->user()->clinic_id;

        $category = LabTestCategory::create($validated);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Categoria criada com sucesso',
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = LabTestCategory::where('clinic_id', $request->user()->clinic_id)->findOrFail($id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:lab_test_categories,code,' . $id],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'data' => $category->fresh(),
            'message' => 'Categoria atualizada com sucesso',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $category = LabTestCategory::where('clinic_id', request()->user()->clinic_id)->findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoria eliminada com sucesso',
        ]);
    }
}
