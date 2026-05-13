<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppConfig extends Model
{
    protected $fillable = ['business_id', 'method', 'is_auto', 'notes'];

    protected $casts = ['is_auto' => 'boolean'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public static function forBusiness(int $businessId): self
    {
        return static::firstOrCreate(
            ['business_id' => $businessId],
            ['method' => 'weighted_average', 'is_auto' => true]
        );
    }
}
