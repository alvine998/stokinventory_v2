<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'business_id', 'pr_no', 'supplier_id', 'warehouse_id',
        'requested_by', 'status', 'notes', 'requested_at',
    ];

    protected $casts = ['requested_at' => 'datetime'];

    public function supplier()    { return $this->belongsTo(Supplier::class); }
    public function warehouse()   { return $this->belongsTo(Warehouse::class); }
    public function requestedBy() { return $this->belongsTo(User::class, 'requested_by'); }
    public function items()       { return $this->hasMany(PurchaseRequestItem::class); }

    public function totalAmount(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}
