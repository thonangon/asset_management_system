<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    protected $table = 'asset_assignments';

    protected $fillable = [
        'assetId', 'assignedToEmployeeId', 'fromLocationId', 'toLocationId', 'assignmentDate',
        'expectedReturnDate', 'returnDate', 'status', 'notes', 'createdByUserId',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'assetId', 'id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assignedToEmployeeId', 'id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'fromLocationId', 'id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'toLocationId', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'createdByUserId', 'id');
    }
}
