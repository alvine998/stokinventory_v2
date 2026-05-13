<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'business_id', 'user_id', 'action',
        'subject_type', 'subject_id', 'description',
        'properties', 'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quick static helper to record an activity.
     */
    public static function record(
        string $action,
        string $description,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $properties = []
    ): void {
        $user = Auth::user();

        static::create([
            'business_id'  => $user?->business_id,
            'user_id'      => $user?->id,
            'action'       => $action,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'description'  => $description,
            'properties'   => $properties ?: null,
            'ip_address'   => Request::ip(),
        ]);
    }
}
