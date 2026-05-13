<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $table = 'delivery_orders';

    protected $fillable = [
        'business_id', 'do_no', 'so_id', 'customer_id', 'warehouse_id', 'expedition_id',
        'tracking_no', 'shipping_address', 'status', 'notes',
        'shipped_at', 'estimated_delivery_at', 'delivered_at',
    ];

    protected $casts = [
        'shipped_at'            => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'delivered_at'          => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'so_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function expedition()
    {
        return $this->belongsTo(Expedition::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class, 'do_id');
    }

    public function trackings()
    {
        return $this->hasMany(ShipmentTracking::class, 'do_id')->orderByDesc('tracked_at');
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class, 'do_id');
    }

    public function totalQty(): float
    {
        return (float) $this->items->sum('quantity');
    }

    public function trackingUrl(): ?string
    {
        if (!$this->expedition || !$this->tracking_no) {
            return null;
        }
        return $this->expedition->trackingUrl($this->tracking_no);
    }
}
