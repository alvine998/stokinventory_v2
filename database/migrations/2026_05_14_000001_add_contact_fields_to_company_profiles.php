<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('call_center')->nullable()->after('email');
            $table->string('field')->nullable()->after('call_center');   // bidang usaha
            $table->text('address')->nullable()->after('field');
            $table->text('about')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['email', 'call_center', 'field', 'address', 'about']);
        });
    }
};
