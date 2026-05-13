<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'name', 'slug', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];

    public static function allPermissions(): array
    {
        return array_merge(...array_values(static::permissionGroups()));
    }

    public static function defaultOwnerPermissions(): array
    {
        return static::allPermissions();
    }

    public static function permissionGroups(): array
    {
        return [
            'workspace'      => ['dashboard.view', 'reports.view', 'company.manage'],
            'people'         => ['users.manage', 'roles.manage'],
            'inventory'      => ['stores.manage', 'warehouses.manage', 'products.manage', 'stock.manage', 'packages.manage'],
            'master_data'    => ['master_data.manage'],
            'inventory_ops'  => ['inventory_ops.manage'],
            'purchasing'     => ['purchasing.manage'],
            'sales'          => ['sales.manage'],
            'finance'        => ['finance.manage'],
            'reporting'      => ['reporting.view'],
            'team_access'    => ['team.manage'],
        ];
    }
}
