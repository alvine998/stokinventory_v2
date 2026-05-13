<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalAction extends Model
{
    protected $fillable = [
        'approval_request_id', 'actor_id', 'step', 'action', 'notes', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
