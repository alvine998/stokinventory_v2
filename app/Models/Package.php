<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'name', 'code', 'price', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
