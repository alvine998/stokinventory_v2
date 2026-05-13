<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'business_id', 'name', 'code', 'contact_person',
        'phone', 'email', 'address', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
