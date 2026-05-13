<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'price',
        'discount_percent',
        'billing_periods',
        'trial_days',
        'features',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'billing_periods' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    /** Discount percent for a specific billing period (falls back to default). */
    public function discountForMonths(?int $months): int
    {
        if ($months && $this->billing_periods) {
            foreach ($this->billing_periods as $period) {
                if ((int) $period['months'] === $months) {
                    return (int) $period['discount_percent'];
                }
            }
        }

        return (int) $this->discount_percent;
    }

    /** Monthly price after period discount (or default discount). */
    public function priceForMonths(?int $months): float
    {
        return (float) $this->price * (100 - $this->discountForMonths($months)) / 100;
    }

    public function discountedPrice(): float
    {
        return (float) $this->price * (100 - $this->discount_percent) / 100;
    }
}
