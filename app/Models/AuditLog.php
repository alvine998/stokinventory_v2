<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    protected $fillable = [
        'business_id', 'user_id', 'event',
        'auditable_type', 'auditable_id', 'auditable_label',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quick static helper to record an audit entry.
     */
    public static function record(
        string $event,
        string $auditableType,
        int $auditableId,
        string $auditableLabel = '',
        array $oldValues = [],
        array $newValues = []
    ): void {
        $user = Auth::user();

        static::create([
            'business_id'     => $user?->business_id,
            'user_id'         => $user?->id,
            'event'           => $event,
            'auditable_type'  => $auditableType,
            'auditable_id'    => $auditableId,
            'auditable_label' => $auditableLabel,
            'old_values'      => $oldValues ?: null,
            'new_values'      => $newValues ?: null,
            'ip_address'      => Request::ip(),
            'user_agent'      => Request::userAgent(),
        ]);
    }
}
