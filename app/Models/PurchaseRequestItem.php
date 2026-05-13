<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id', 'product_id', 'product_name',
        'quantity', 'unit_price', 'notes',
    ];

    public function product()         { return $this->belongsTo(Product::class); }
    public function purchaseRequest() { return $this->belongsTo(PurchaseRequest::class); }
}
