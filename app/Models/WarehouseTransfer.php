<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class WarehouseTransfer extends Model
{
    use LogsActivity;
    protected $fillable = [
        'business_id', 'product_id', 'from_warehouse_id', 'to_warehouse_id',
        'transferred_by', 'quantity', 'reference_no', 'notes', 'status', 'transferred_at',
    ];

    protected $casts = ['transferred_at' => 'datetime'];

    public function product()       { return $this->belongsTo(Product::class); }
    public function fromWarehouse() { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse()   { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }
    public function transferredBy() { return $this->belongsTo(User::class, 'transferred_by'); }
}
