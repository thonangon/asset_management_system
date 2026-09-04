<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'EmployeeCode',
        'DepartmentID',
        'FirstName',
        'LastName',
        'Email',
        'Phone',
        'Status',
        'birthdate',
        'gender',
        'photo_path',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'EmployeeID', 'id');
    }

}
