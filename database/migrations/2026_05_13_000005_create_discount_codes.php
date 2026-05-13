<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('type')->default('percent');
            $table->decimal('value', 14, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('discount_code_id')->nullable()->after('bank_account_id')->constrained()->nullOnDelete();
            $table->string('discount_code')->nullable()->after('discount_code_id');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['discount_code_id']);
            $table->dropColumn(['discount_code_id', 'discount_code']);
        });

        Schema::dropIfExists('discount_codes');
    }
};
