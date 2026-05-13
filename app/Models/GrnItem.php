<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrnItem extends Model
{
    protected $table = 'grn_items';

    protected $fillable = [
        'grn_id', 'product_id', 'product_name', 'quantity', 'unit_price',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function grn()     { return $this->belongsTo(GoodsReceiveNote::class, 'grn_id'); }
}
