<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('name');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('evidence_image_path')->nullable()->after('notes');
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->string('evidence_image_path')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('evidence_image_path');
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn('evidence_image_path');
        });
    }
};
