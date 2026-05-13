<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'badge',
        'button_label',
        'button_url',
        'background',
        'image',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
