<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'business_id', 'workflow_id', 'requester_id',
        'reference_type', 'reference_id', 'reference_no',
        'title', 'amount', 'status', 'current_step', 'data', 'notes',
    ];

    protected $casts = [
        'data'   => 'array',
        'amount' => 'decimal:2',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function actions()
    {
        return $this->hasMany(ApprovalAction::class);
    }
}
