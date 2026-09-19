<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    protected $table = 'warranties';

    protected $fillable = [
        'asset_id',
        'provider',
        'start_date',
        'end_date',
        'coverage_details',
        'policy_number',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }
}