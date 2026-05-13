<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierDebt extends Model
{
    protected $fillable = [
        'business_id', 'supplier_id', 'purchase_order_id',
        'invoice_no', 'amount', 'paid_amount', 'due_date', 'status', 'notes',
    ];

    protected $casts = ['due_date' => 'date'];

    public function supplier()      { return $this->belongsTo(Supplier::class); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }

    public function outstanding(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }
}
