<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('platform_role')->default('customer')->after('is_super_admin');
        });

        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('section')->default('landing');
            $table->text('body')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('platform_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('audience')->default('all');
            $table->string('title');
            $table->text('message');
            $table->string('channel')->default('in_app');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('support_type')->default('technical');
            $table->string('subject');
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_no')->unique();
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('status')->default('unpaid');
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_rooms');
        Schema::dropIfExists('platform_notifications');
        Schema::dropIfExists('cms_pages');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('platform_role');
        });
    }
};
