<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepreciationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'asset_id'            => $this->asset_id,
            'asset'               => $this->whenLoaded('asset', fn () => $this->asset ? [
                'id'        => $this->asset->id,
                'asset_tag' => $this->asset->asset_tag,
                'name'      => $this->asset->name,
            ] : null),
            'period_start'        => $this->period_start,
            'period_end'          => $this->period_end,
            'opening_book_value'  => $this->opening_book_value,
            'depreciation_amount' => $this->depreciation_amount,
            'closing_book_value'  => $this->closing_book_value,
            'method'              => $this->method,
            'user_id'             => $this->user_id,
        ];
    }
}