<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'business_id', 'return_no', 'grn_id', 'supplier_id', 'warehouse_id',
        'returned_by', 'reason', 'status', 'notes', 'returned_at',
    ];

    protected $casts = ['returned_at' => 'datetime'];

    public function supplier()  { return $this->belongsTo(Supplier::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
    public function grn()       { return $this->belongsTo(GoodsReceiveNote::class, 'grn_id'); }
    public function returnedBy(){ return $this->belongsTo(User::class, 'returned_by'); }
    public function items()     { return $this->hasMany(PurchaseReturnItem::class); }

    public function totalAmount(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}
