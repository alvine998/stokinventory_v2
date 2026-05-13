<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'logo', 'name', 'email', 'call_center', 'field', 'address', 'about', 'vision', 'mission', 'organization', 'why_us'];
}
