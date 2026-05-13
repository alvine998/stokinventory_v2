<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'sku', 'name', 'photo_path', 'category', 'unit', 'price', 'minimum_stock', 'current_stock'];
}
