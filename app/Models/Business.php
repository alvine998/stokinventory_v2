<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'industry',
        'business_size',
        'inventory_goal',
        'has_multiple_locations',
        'trial_started_at',
        'trial_ends_at',
        'trial_expired_at',
        'onboarding_completed_at',
    ];

    protected $casts = [
        'has_multiple_locations' => 'boolean',
        'trial_started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'trial_expired_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
    ];

    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function trialDaysLeft(): int
    {
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function isTrialExpired(): bool
    {
        return $this->trial_ends_at && now()->greaterThanOrEqualTo($this->trial_ends_at);
    }

    public function daysUntilDataDeletion(): int
    {
        if (! $this->trial_expired_at) {
            return 7;
        }

        $deletionDate = $this->trial_expired_at->copy()->addDays(7);

        return max(0, now()->diffInDays($deletionDate, false));
    }
}
