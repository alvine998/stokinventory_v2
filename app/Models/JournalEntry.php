<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'business_id', 'entry_no', 'reference_no', 'reference_type',
        'description', 'entry_date', 'is_auto', 'created_by',
    ];

    protected $casts = ['entry_date' => 'date', 'is_auto' => 'boolean'];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /** Total debit (should equal total credit for balanced entries) */
    public function totalDebit(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function totalCredit(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->totalDebit() - $this->totalCredit()) < 0.01;
    }
}
