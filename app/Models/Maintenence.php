<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenence extends Model
{
    protected $table = 'maintenences';

    protected $fillable = [
        'assetId',
        'maintenanceDate',
        'type',
        'performedBy',
        'description',
        'cost',
        'result',
        'nextDueDate',
        'createbyUserId',
    ];

    protected function casts(): array
    {
        return [
            'maintenanceDate' => 'date',
            'cost' => 'decimal:2',
            'nextDueDate' => 'date',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'assetId', 'id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createbyUserId', 'id');
    }
}
