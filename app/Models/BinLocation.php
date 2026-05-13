<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinLocation extends Model
{
    protected $fillable = [
        'business_id', 'warehouse_id', 'code',
        'aisle', 'rack', 'level', 'bin', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
