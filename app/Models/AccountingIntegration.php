<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingIntegration extends Model
{
    protected $fillable = [
        'business_id', 'provider', 'api_key', 'endpoint', 'settings', 'is_active', 'last_sync_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'settings'     => 'array',
        'last_sync_at' => 'datetime',
    ];

    // Mask api_key when serialising to hide secrets from views
    public function getMaskedApiKeyAttribute(): string
    {
        if (!$this->api_key) {
            return '';
        }
        return str_repeat('*', max(0, strlen($this->api_key) - 4)) . substr($this->api_key, -4);
    }

    public static function providers(): array
    {
        return ['accurate', 'jurnal', 'zahir', 'beecloud', 'custom'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
