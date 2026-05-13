<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTarget extends Model
{
    protected $table = 'kpi_targets';

    protected $fillable = ['business_id', 'metric', 'target_value', 'year', 'month'];

    public static function metrics(): array
    {
        return ['revenue', 'orders', 'gross_profit', 'inventory_value'];
    }
}
