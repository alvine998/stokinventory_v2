<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// User is in the same namespace — no additional import needed.

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = ['support_room_id', 'user_id', 'message', 'is_staff_reply'];

    protected $casts = ['is_staff_reply' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
