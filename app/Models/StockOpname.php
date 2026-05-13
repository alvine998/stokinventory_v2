<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'warehouse_id', 'reference_no', 'status', 'scheduled_at', 'completed_at', 'notes', 'evidence_image_path'];

    protected $casts = ['scheduled_at' => 'datetime', 'completed_at' => 'datetime'];
}
