<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

// User and Business are in the same namespace — no additional import needed.

class SupportRoom extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['business_id', 'assigned_user_id', 'support_type', 'subject', 'status'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }
}
