<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportRoom extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'assigned_user_id', 'support_type', 'subject', 'status'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class);
    }
}
