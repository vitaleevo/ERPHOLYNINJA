<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabRequestCreateRequest extends FormRequest
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
            'consultation_id' => ['nullable', 'exists:consultations,id'],
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'priority' => ['nullable', Rule::in(['routine', 'urgent', 'stat'])],
            'clinical_notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.test_id' => ['required_with:items', 'exists:lab_tests,id'],
            'items.*.profile_id' => ['nullable', 'exists:lab_test_profiles,id'],
            'print_label' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'É necessário adicionar pelo menos um exame ao pedido',
            'items.*.test_id.required_with' => 'Cada item deve ter um exame associado',
        ];
    }
}
