<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'business_id', 'so_no', 'customer_id', 'warehouse_id', 'price_level_id',
        'created_by', 'status', 'notes', 'ordered_at',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function priceLevel()
    {
        return $this->belongsTo(PriceLevel::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'so_id');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'so_id');
    }

    public function invoices()
    {
        return $this->hasMany(SalesInvoice::class, 'so_id');
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class, 'so_id');
    }

    public function totalAmount(): float
    {
        return (float) $this->items->sum('subtotal');
    }
}
