<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'product_id', 'warehouse_id', 'type', 'quantity', 'reference_no', 'notes', 'evidence_image_path', 'moved_at'];

    protected $casts = ['moved_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
