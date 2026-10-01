<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'asset_id'               => $this->assetId,
            'assigned_to_employee_id' => $this->assignedToEmployeeId,
            'from_location_id'       => $this->fromLocationId,
            'to_location_id'         => $this->toLocationId,
            'assignment_date'        => $this->assignmentDate,
            'expected_return_date'   => $this->expectedReturnDate,
            'return_date'            => $this->returnDate,
            'status'                 => $this->status,
            'status_label'           => $this->status ? ucwords(str_replace('_', ' ', $this->status)) : null,
            'notes'                  => $this->notes,
            'created_by_user_id'     => $this->createdByUserId,
            'asset'                  => $this->whenLoaded('asset', fn () => $this->asset ? [
                'id'        => $this->asset->id,
                'asset_tag' => $this->asset->asset_tag,
                'name'      => $this->asset->name,
            ] : null),
            'assigned_to'            => $this->whenLoaded('assignedTo', fn () => $this->assignedTo ? [
                'id'            => $this->assignedTo->id,
                'employee_code' => $this->assignedTo->EmployeeCode,
                'first_name'    => $this->assignedTo->FirstName,
                'last_name'     => $this->assignedTo->LastName,
                'full_name'     => trim($this->assignedTo->FirstName . ' ' . $this->assignedTo->LastName),
            ] : null),
            'from_location'          => $this->whenLoaded('fromLocation', fn () => $this->fromLocation ? [
                'id'   => $this->fromLocation->id,
                'name' => $this->fromLocation->name,
                'code' => $this->fromLocation->code,
            ] : null),
            'to_location'            => $this->whenLoaded('toLocation', fn () => $this->toLocation ? [
                'id'   => $this->toLocation->id,
                'name' => $this->toLocation->name,
                'code' => $this->toLocation->code,
            ] : null),
            'created_by'             => $this->whenLoaded('createdBy', fn () => $this->createdBy ? [
                'id'    => $this->createdBy->id,
                'name'  => $this->createdBy->name,
                'email' => $this->createdBy->email,
            ] : null),
        ];
    }
}