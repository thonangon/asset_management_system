<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    protected $table = 'asset_transfers';

    protected $fillable = [
        'assetId', 'fromLocationId', 'toLocationId', 'fromEmployeeId', 'toEmployeeId',
        'TransferDate', 'Reason', 'Status', 'CreatedByUserID',
    ];

    protected function casts(): array
    {
        return [
            'TransferDate' => 'date',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'assetId', 'id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'fromLocationId', 'id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'toLocationId', 'id');
    }

    public function fromEmployee()
    {
        return $this->belongsTo(Employee::class, 'fromEmployeeId', 'id');
    }

    public function toEmployee()
    {
        return $this->belongsTo(Employee::class, 'toEmployeeId', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedByUserID', 'id');
    }
}
