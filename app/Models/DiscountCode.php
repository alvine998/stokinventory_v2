<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $fillable = ['subscription_package_id', 'code', 'type', 'value', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function appliesTo(SubscriptionPackage $package): bool
    {
        return $this->is_active
            && (! $this->subscription_package_id || $this->subscription_package_id === $package->id)
            && (! $this->starts_at || $this->starts_at->lte(now()))
            && (! $this->ends_at || $this->ends_at->gte(now()));
    }

    public function amountFor(float $subtotal): float
    {
        if ($this->type === 'fixed') {
            return min($subtotal, (float) $this->value);
        }

        return min($subtotal, $subtotal * ((float) $this->value / 100));
    }
}
