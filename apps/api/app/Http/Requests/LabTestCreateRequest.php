<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabTestCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:lab_test_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:lab_tests,code'],
            'description' => ['nullable', 'string'],
            'unit_of_measurement' => ['nullable', 'string', 'max:50'],
            'min_reference_value' => ['nullable', 'numeric'],
            'max_reference_value' => ['nullable', 'numeric', 'gte:min_reference_value'],
            'panic_min_value' => ['nullable', 'numeric'],
            'panic_max_value' => ['nullable', 'numeric', 'gte:panic_min_value'],
            'sample_type' => ['nullable', 'string', 'max:100'],
            'turnaround_time_hours' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'requires_equipment' => ['boolean'],
            'equipment_id' => ['nullable', 'exists:lab_equipment,id'],
            'preparation_instructions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }
}
