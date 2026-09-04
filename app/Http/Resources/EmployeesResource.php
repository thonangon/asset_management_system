<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'employee_code' => $this->EmployeeCode,
            'first_name' => $this->FirstName,
            'last_name'  => $this->LastName,
            'full_name'  => $this->FirstName . ' ' . $this->LastName,
            'email'      => $this->Email,
            'phone'      => $this->Phone,
            'status'     => $this->Status,
            'birthdate'  => $this->birthdate,
            'gender'     => $this->gender,
            'photo_path' => $this->photo_path,
            'department' => $this->department ? [
                'id'   => $this->department->id,
                'name' => $this->department->Name,
                'code' => $this->department->Code,
            ] : null,
        ];
    }
}
