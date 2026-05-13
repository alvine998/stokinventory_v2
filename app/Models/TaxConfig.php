<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxConfig extends Model
{
    protected $fillable = [
        'business_id', 'name', 'code', 'rate_percent',
        'tax_type', 'is_inclusive', 'applies_to', 'is_active',
    ];

    protected $casts = ['is_inclusive' => 'boolean', 'is_active' => 'boolean'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /** Calculate tax amount on a base amount */
    public function calculate(float $amount): float
    {
        if ($this->is_inclusive) {
            // Tax is already included: tax = amount × rate / (100 + rate)
            return round($amount * $this->rate_percent / (100 + $this->rate_percent), 2);
        }
        return round($amount * $this->rate_percent / 100, 2);
    }
}
