<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            // Array of {months, discount_percent} objects, e.g. [{months:6,discount_percent:10},...]
            $table->json('billing_periods')->nullable()->after('discount_percent');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedTinyInteger('billing_months')->nullable()->after('subscription_package_id');
        });
    }

    public function down()
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->dropColumn('billing_periods');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('billing_months');
        });
    }
};
