<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $table = 'sales_returns';

    protected $fillable = [
        'business_id', 'return_no', 'so_id', 'do_id', 'customer_id', 'warehouse_id',
        'returned_by', 'reason', 'status', 'notes', 'returned_at',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'so_id');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class, 'return_id');
    }

    public function totalAmount(): float
    {
        return (float) $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}
