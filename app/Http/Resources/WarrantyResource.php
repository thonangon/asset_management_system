<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarrantyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'asset_id'         => $this->asset_id,
            'asset'            => $this->whenLoaded('asset', fn () => $this->asset ? [
                'id'        => $this->asset->id,
                'asset_tag' => $this->asset->asset_tag,
                'name'      => $this->asset->name,
            ] : null),
            'provider'         => $this->provider,
            'start_date'       => $this->start_date,
            'end_date'         => $this->end_date,
            'coverage_details' => $this->coverage_details,
            'policy_number'    => $this->policy_number,
            'status'           => $this->status,
            'created_by_user_id' => $this->created_by_user_id,
        ];
    }
}