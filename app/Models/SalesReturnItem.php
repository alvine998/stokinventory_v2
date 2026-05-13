<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    protected $table = 'sales_return_items';

    protected $fillable = [
        'return_id', 'product_id', 'product_name', 'quantity', 'unit_price',
    ];

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
