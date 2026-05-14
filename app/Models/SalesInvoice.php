<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class SalesInvoice extends Model
{
    use LogsActivity;
    protected $table = 'sales_invoices';

    protected $fillable = [
        'business_id', 'invoice_no', 'so_id', 'customer_id',
        'status', 'amount', 'paid_amount', 'due_at', 'issued_at', 'notes',
    ];

    protected $casts = [
        'due_at'    => 'date',
        'issued_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'so_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function outstanding(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid'
            && $this->due_at !== null
            && $this->due_at->isPast();
    }
}
