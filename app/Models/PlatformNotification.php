<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformNotification extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'audience', 'title', 'message', 'channel', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];
}
