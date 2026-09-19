<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depreciation extends Model
{
    protected $table = 'depreciations';

    protected $fillable = [
        'asset_id',
        'period_start',
        'period_end',
        'opening_book_value',
        'depreciation_amount',
        'closing_book_value',
        'method',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_book_value' => 'decimal:2',
            'depreciation_amount' => 'decimal:2',
            'closing_book_value' => 'decimal:2',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}