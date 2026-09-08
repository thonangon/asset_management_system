<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'organizations';

    protected $fillable = [
        'indentifier',
        'name',
        'slug',
        'address',
        'description',
        'logo',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class, 'organization_id', 'id');
    }
}
