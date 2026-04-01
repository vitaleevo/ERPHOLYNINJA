<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabResultStoreRequest extends FormRequest
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
            'request_item_id' => ['required', 'exists:lab_request_items,id'],
            'test_id' => ['required', 'exists:lab_tests,id'],
            'result_value' => ['nullable', 'string'],
            'numeric_value' => ['nullable', 'numeric'],
            'text_result' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'reference_min' => ['nullable', 'numeric'],
            'reference_max' => ['nullable', 'numeric', 'gte:reference_min'],
            'interpretation' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['string'],
            'result_datetime' => ['nullable', 'date'],
        ];
    }
}
