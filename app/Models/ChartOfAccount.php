<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'business_id', 'code', 'name', 'type', 'parent_id',
        'is_system', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_system' => 'boolean', 'is_active' => 'boolean'];

    // Type labels
    public static array $types = ['asset', 'liability', 'equity', 'revenue', 'cogs', 'expense'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /** Seed default system accounts for a business */
    public static function seedDefaults(int $businessId): void
    {
        $defaults = [
            // Assets
            ['code' => '1100', 'name' => 'Kas & Setara Kas',       'type' => 'asset',    'sort_order' => 1],
            ['code' => '1200', 'name' => 'Piutang Usaha',           'type' => 'asset',    'sort_order' => 2],
            ['code' => '1300', 'name' => 'Persediaan Barang',       'type' => 'asset',    'sort_order' => 3],
            ['code' => '1400', 'name' => 'Aktiva Lain-lain',        'type' => 'asset',    'sort_order' => 4],
            // Liabilities
            ['code' => '2100', 'name' => 'Hutang Usaha',            'type' => 'liability','sort_order' => 10],
            ['code' => '2200', 'name' => 'Hutang Pajak (PPN)',       'type' => 'liability','sort_order' => 11],
            ['code' => '2300', 'name' => 'Hutang Lain-lain',        'type' => 'liability','sort_order' => 12],
            // Equity
            ['code' => '3100', 'name' => 'Modal Usaha',             'type' => 'equity',  'sort_order' => 20],
            ['code' => '3200', 'name' => 'Laba Ditahan',            'type' => 'equity',  'sort_order' => 21],
            // Revenue
            ['code' => '4100', 'name' => 'Pendapatan Penjualan',    'type' => 'revenue',  'sort_order' => 30],
            ['code' => '4200', 'name' => 'Pendapatan Lain-lain',    'type' => 'revenue',  'sort_order' => 31],
            // COGS
            ['code' => '5100', 'name' => 'Harga Pokok Penjualan',   'type' => 'cogs',     'sort_order' => 40],
            // Expenses
            ['code' => '6100', 'name' => 'Biaya Operasional',       'type' => 'expense',  'sort_order' => 50],
            ['code' => '6200', 'name' => 'Biaya Gaji',              'type' => 'expense',  'sort_order' => 51],
            ['code' => '6300', 'name' => 'Biaya Pengiriman',        'type' => 'expense',  'sort_order' => 52],
            ['code' => '6900', 'name' => 'Biaya Lain-lain',         'type' => 'expense',  'sort_order' => 53],
        ];

        foreach ($defaults as $account) {
            static::firstOrCreate(
                ['business_id' => $businessId, 'code' => $account['code']],
                array_merge($account, ['business_id' => $businessId, 'is_system' => true, 'is_active' => true])
            );
        }
    }
}
