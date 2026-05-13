<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'section', 'body', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];
}
