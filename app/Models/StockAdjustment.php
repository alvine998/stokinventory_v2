<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'business_id', 'product_id', 'warehouse_id', 'adjusted_by',
        'type', 'quantity', 'reason', 'reference_no', 'adjusted_at',
    ];

    protected $casts = ['adjusted_at' => 'datetime'];

    public function product()  { return $this->belongsTo(Product::class); }
    public function warehouse(){ return $this->belongsTo(Warehouse::class); }
    public function adjustedBy(){ return $this->belongsTo(User::class, 'adjusted_by'); }
}
