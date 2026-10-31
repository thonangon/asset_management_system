<?php

namespace App\Http\Requests\Reports;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AssetReportRequest extends FormRequest
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'format' => ['nullable', 'string', 'in:xlsx,pdf'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'The report start date is required.',
            'start_date.date' => 'The report start date must be a valid date.',
            'end_date.required' => 'The report end date is required.',
            'end_date.date' => 'The report end date must be a valid date.',
            'end_date.after_or_equal' => 'The report end date must be on or after the start date.',
            'category_id.exists' => 'The selected asset category does not exist.',
            'asset_id.exists' => 'The selected asset does not exist.',
            'format.in' => 'The export format must be either xlsx or pdf.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedReportData(): array
    {
        return [
            'periodStart' => CarbonImmutable::parse($this->input('start_date'))->startOfDay(),
            'periodEnd' => CarbonImmutable::parse($this->input('end_date'))->startOfDay(),
            'categoryId' => $this->filled('category_id') ? (int) $this->input('category_id') : null,
            'assetId' => $this->filled('asset_id') ? (int) $this->input('asset_id') : null,
        ];
    }

    public function exportFormat(): string
    {
        return strtolower((string) $this->input('format', 'xlsx')) === 'pdf' ? 'pdf' : 'xlsx';
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
