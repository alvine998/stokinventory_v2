<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrderItem extends Model
{
    protected $table = 'delivery_order_items';

    protected $fillable = [
        'do_id', 'product_id', 'product_name', 'quantity',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
