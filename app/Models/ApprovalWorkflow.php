<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'business_id', 'name', 'module', 'trigger_event',
        'min_amount', 'approver_ids', 'description', 'is_active',
    ];

    protected $casts = [
        'approver_ids' => 'array',
        'is_active'    => 'boolean',
        'min_amount'   => 'decimal:2',
    ];

    public function requests()
    {
        return $this->hasMany(ApprovalRequest::class, 'workflow_id');
    }

    public static function modules(): array
    {
        return ['purchasing', 'sales', 'inventory', 'finance', 'general'];
    }
}
