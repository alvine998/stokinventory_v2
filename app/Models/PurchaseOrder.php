<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'business_id', 'po_no', 'purchase_request_id', 'supplier_id',
        'warehouse_id', 'created_by', 'approved_by', 'status',
        'notes', 'ordered_at', 'expected_at',
    ];

    protected $casts = [
        'ordered_at'  => 'datetime',
        'expected_at' => 'date',
    ];

    public function supplier()        { return $this->belongsTo(Supplier::class); }
    public function warehouse()       { return $this->belongsTo(Warehouse::class); }
    public function purchaseRequest() { return $this->belongsTo(PurchaseRequest::class); }
    public function createdBy()       { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy()      { return $this->belongsTo(User::class, 'approved_by'); }
    public function items()           { return $this->hasMany(PurchaseOrderItem::class); }
    public function grns()            { return $this->hasMany(GoodsReceiveNote::class); }
    public function debts()           { return $this->hasMany(SupplierDebt::class); }

    public function totalAmount(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}
