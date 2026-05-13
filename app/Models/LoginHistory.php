<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id', 'business_id', 'ip_address', 'user_agent',
        'is_successful', 'login_at', 'logout_at',
    ];

    protected $casts = [
        'login_at'  => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->logout_at) {
            return null;
        }
        $mins = $this->login_at->diffInMinutes($this->logout_at);
        if ($mins < 60) {
            return $mins . 'm';
        }
        $h = intdiv($mins, 60);
        $m = $mins % 60;
        return $h . 'h ' . $m . 'm';
    }
}
