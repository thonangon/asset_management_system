<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'indentifier'      => $this->indentifier,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'address'          => $this->address,
            'description'      => $this->description,
            'logo'             => $this->logo,
            'departments_count' => $this->whenCounted('departments'),
        ];
    }
}
