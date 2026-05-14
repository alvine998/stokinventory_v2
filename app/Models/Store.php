<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Store extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['business_id', 'name', 'code', 'address', 'phone', 'status'];
}
