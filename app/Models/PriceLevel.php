<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class PriceLevel extends Model
{
    use LogsActivity;
    protected $fillable = [
        'business_id', 'name', 'description', 'discount_percent', 'is_default', 'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }
}
