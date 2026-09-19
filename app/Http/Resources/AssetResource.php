<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'asset_tag'          => $this->asset_tag,
            'serial_number'      => $this->serial_number,
            'name'               => $this->name,
            'description'        => $this->description,
            'purchase_cost'      => $this->purchase_cost,
            'purchase_date'      => $this->purchase_date,
            'useful_life_years'  => $this->useful_life_years,
            'status'             => $this->status,
            'status_label'       => $this->status ? ucwords(str_replace('_', ' ', $this->status)) : null,
            'category'           => $this->assetCategory ? [
                'id'   => $this->assetCategory->id,
                'name' => $this->assetCategory->name,
                'code' => $this->assetCategory->code,
            ] : null,
            'location'           => $this->location ? [
                'id'              => $this->location->id,
                'name'            => $this->location->name,
                'code'            => $this->location->code,
                'organization_id' => $this->location->organization_id,
            ] : null,
            'warranty'           => $this->whenLoaded('warranty', function () {
                if (!$this->warranty) {
                    return null;
                }

                return [
                    'id'               => $this->warranty->id,
                    'provider'         => $this->warranty->provider,
                    'start_date'       => $this->warranty->start_date,
                    'end_date'         => $this->warranty->end_date,
                    'coverage_details' => $this->warranty->coverage_details,
                    'policy_number'    => $this->warranty->policy_number,
                    'status'           => $this->warranty->status,
                ];
            }),
            'depreciations'      => $this->whenLoaded('depreciations', function () {
                return $this->depreciations->map(fn ($dep) => [
                    'id'                  => $dep->id,
                    'period_start'        => $dep->period_start,
                    'period_end'          => $dep->period_end,
                    'opening_book_value'  => $dep->opening_book_value,
                    'depreciation_amount' => $dep->depreciation_amount,
                    'closing_book_value'  => $dep->closing_book_value,
                    'method'              => $dep->method,
                ]);
            }),
        ];
    }
}