<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetTransferResource extends JsonResource
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
            'asset_id'           => $this->assetId,
            'from_location_id'   => $this->fromLocationId,
            'to_location_id'     => $this->toLocationId,
            'from_employee_id'   => $this->fromEmployeeId,
            'to_employee_id'     => $this->toEmployeeId,
            'transfer_date'      => $this->TransferDate,
            'reason'             => $this->Reason,
            'status'             => $this->Status,
            'status_label'       => $this->Status ? ucwords(str_replace('_', ' ', $this->Status)) : null,
            'created_by_user_id' => $this->CreatedByUserID,
            'asset'              => $this->whenLoaded('asset', fn () => $this->asset ? [
                'id'        => $this->asset->id,
                'asset_tag' => $this->asset->asset_tag,
                'name'      => $this->asset->name,
            ] : null),
            'from_location'      => $this->whenLoaded('fromLocation', fn () => $this->fromLocation ? [
                'id'   => $this->fromLocation->id,
                'name' => $this->fromLocation->name,
                'code' => $this->fromLocation->code,
            ] : null),
            'to_location'        => $this->whenLoaded('toLocation', fn () => $this->toLocation ? [
                'id'   => $this->toLocation->id,
                'name' => $this->toLocation->name,
                'code' => $this->toLocation->code,
            ] : null),
            'from_employee'      => $this->whenLoaded('fromEmployee', fn () => $this->fromEmployee ? [
                'id'            => $this->fromEmployee->id,
                'employee_code' => $this->fromEmployee->EmployeeCode,
                'first_name'    => $this->fromEmployee->FirstName,
                'last_name'     => $this->fromEmployee->LastName,
                'full_name'     => trim($this->fromEmployee->FirstName . ' ' . $this->fromEmployee->LastName),
            ] : null),
            'to_employee'        => $this->whenLoaded('toEmployee', fn () => $this->toEmployee ? [
                'id'            => $this->toEmployee->id,
                'employee_code' => $this->toEmployee->EmployeeCode,
                'first_name'    => $this->toEmployee->FirstName,
                'last_name'     => $this->toEmployee->LastName,
                'full_name'     => trim($this->toEmployee->FirstName . ' ' . $this->toEmployee->LastName),
            ] : null),
            'created_by'         => $this->whenLoaded('createdBy', fn () => $this->createdBy ? [
                'id'    => $this->createdBy->id,
                'name'  => $this->createdBy->name,
                'email' => $this->createdBy->email,
            ] : null),
        ];
    }
}