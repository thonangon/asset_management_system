<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = 'assets';

    protected $fillable = [
        'asset_tag',
        'serial_number',
        'name',
        'description',
        'purchase_cost',
        'purchase_date',
        'useful_life_years',
        'status',
        'asset_category_id',
        'current_location_id',
    ];

    protected function casts(): array
    {
        return [
            'purchase_cost' => 'decimal:2',
            'purchase_date' => 'date',
            'useful_life_years' => 'integer',
        ];
    }

    public function assetCategory()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'current_location_id', 'id');
    }

    public function warranty()
    {
        return $this->hasOne(Warranty::class, 'asset_id', 'id');
    }

    public function depreciations()
    {
        return $this->hasMany(Depreciation::class, 'asset_id', 'id');
    }
}