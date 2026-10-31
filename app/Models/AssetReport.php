<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetReport extends Model
{
    public const REPORT_TYPES = [
        'asset_report',
        'assignment_report',
        'transfer_report',
        'return_report',
        'maintenance_report',
        'warranty_report',
        'depreciation_report', 
    ];

    protected $table = 'asset_reports';

    protected $fillable = [
        'requested_by_user_id',
        'report_type',
        'parameters',
        'status',
        'format',
        'output_uri',
        'requested_at',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'parameters' => 'array',
        'requested_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id', 'id');
    }
}
