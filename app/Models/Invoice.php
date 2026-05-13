<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'subscription_package_id',
        'billing_months',
        'bank_account_id',
        'discount_code_id',
        'discount_code',
        'customer_name',
        'customer_email',
        'invoice_no',
        'amount',
        'discount_amount',
        'total_amount',
        'status',
        'payment_method',
        'payment_notes',
        'payment_evidence',
        'paid_at',
        'issued_at',
        'due_at',
    ];

    protected $casts = ['issued_at' => 'date', 'due_at' => 'date', 'paid_at' => 'datetime'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function subscriptionPackage()
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class);
    }
}
