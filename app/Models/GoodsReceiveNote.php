<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiveNote extends Model
{
    protected $table = 'goods_receive_notes';

    protected $fillable = [
        'business_id', 'grn_no', 'purchase_order_id', 'supplier_id',
        'warehouse_id', 'received_by', 'status', 'notes', 'received_at',
    ];

    protected $casts = ['received_at' => 'datetime'];

    public function supplier()      { return $this->belongsTo(Supplier::class); }
    public function warehouse()     { return $this->belongsTo(Warehouse::class); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function receivedBy()    { return $this->belongsTo(User::class, 'received_by'); }
    public function items()         { return $this->hasMany(GrnItem::class, 'grn_id'); }
    public function returns()       { return $this->hasMany(PurchaseReturn::class, 'grn_id'); }

    public function totalAmount(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}
