<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id', 'product_name',
        'quantity', 'unit_price', 'received_qty',
    ];

    public function product()       { return $this->belongsTo(Product::class); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
}
