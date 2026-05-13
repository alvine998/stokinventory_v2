<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBarcode extends Model
{
    protected $fillable = ['business_id', 'product_id', 'barcode_type', 'value', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
