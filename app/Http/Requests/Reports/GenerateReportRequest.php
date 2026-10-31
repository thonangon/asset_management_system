<?php

namespace App\Http\Requests\Reports;

use App\Models\AssetReport;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'report_type' => ['required', 'string', Rule::in(AssetReport::REPORT_TYPES)],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'format' => ['sometimes', 'string', Rule::in(['json', 'csv', 'xlsx', 'pdf'])],
            'department' => ['sometimes', 'nullable', 'string', 'max:100'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'report_type.required' => 'The report type is required.',
            'report_type.in' => 'The selected report type is not supported.',
            'start_date.required' => 'The report start date is required.',
            'start_date.date_format' => 'The report start date must use the YYYY-MM-DD format.',
            'end_date.required' => 'The report end date is required.',
            'end_date.date_format' => 'The report end date must use the YYYY-MM-DD format.',
            'end_date.after_or_equal' => 'The report end date must be on or after the start date.',
            'format.in' => 'The report format must be one of: json, csv, xlsx, pdf.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors.',
            'data' => $validator->errors(),
        ], 422));
    }
}
