<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'name',
        'code',
        'description',
        'organization_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'current_location_id', 'id');
    }
}