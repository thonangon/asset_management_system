<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = ['Name', 'Code', 'description', 'organization_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'DepartmentID', 'id');
    }
}
