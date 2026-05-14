<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Brand extends Model
{
    use LogsActivity;
    protected $fillable = ['business_id', 'name', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
