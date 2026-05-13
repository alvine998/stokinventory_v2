<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expedition extends Model
{
    protected $fillable = [
        'business_id', 'name', 'code', 'tracking_url_template', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function trackingUrl(string $trackingNo): ?string
    {
        if (!$this->tracking_url_template) {
            return null;
        }
        return str_replace('{tracking_no}', $trackingNo, $this->tracking_url_template);
    }
}
