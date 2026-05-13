<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('promo_banners', function (Blueprint $table) {
            $table->string('image')->nullable()->after('background');
        });
    }

    public function down()
    {
        Schema::table('promo_banners', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
