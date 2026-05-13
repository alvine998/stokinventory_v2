<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchLot extends Model
{
    protected $fillable = [
        'business_id', 'product_id', 'batch_no', 'lot_no',
        'quantity', 'manufactured_at', 'expires_at', 'notes',
    ];

    protected $casts = [
        'manufactured_at' => 'date',
        'expires_at'      => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function daysUntilExpiry(): ?int
    {
        return $this->expires_at?->diffInDays(now(), false);
    }
}
