<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    protected $fillable = [
        'business_id', 'product_id', 'warehouse_id',
        'serial_no', 'status', 'notes',
    ];

    public function product()  { return $this->belongsTo(Product::class); }
    public function warehouse(){ return $this->belongsTo(Warehouse::class); }
}
