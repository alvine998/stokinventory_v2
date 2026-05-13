<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentTracking extends Model
{
    protected $table = 'shipment_trackings';

    protected $fillable = [
        'do_id', 'status', 'location', 'description', 'tracked_at',
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_id');
    }
}
