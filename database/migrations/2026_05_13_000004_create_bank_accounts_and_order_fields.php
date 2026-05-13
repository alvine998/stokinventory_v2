<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('branch')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('subscription_package_id')->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable()->after('bank_account_id');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('payment_method')->default('bank_transfer')->after('status');
            $table->text('payment_notes')->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('payment_notes');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn(['bank_account_id', 'customer_name', 'customer_email', 'payment_method', 'payment_notes', 'paid_at']);
        });

        Schema::dropIfExists('bank_accounts');
    }
};
