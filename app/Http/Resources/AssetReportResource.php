<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requested_by_user_id' => $this->requested_by_user_id,
            'report_type' => $this->report_type,
            'parameters' => $this->parameters,
            'status' => $this->status,
            'format' => $this->format,
            'output_uri' => $this->output_uri,
            'requested_at' => $this->requested_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'error_message' => $this->error_message,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
