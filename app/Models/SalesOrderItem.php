<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';

    protected $fillable = [
        'so_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'discount_percent', 'subtotal',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'so_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
