<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $table = 'asset_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_category_id', 'id');
    }
}