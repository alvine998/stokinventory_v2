<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('module', 50); // purchasing, sales, inventory, finance, general
            $table->string('trigger_event', 100)->nullable(); // e.g. purchase_order.submit
            $table->decimal('min_amount', 16, 2)->nullable(); // only apply if amount >= this
            $table->json('approver_ids'); // ordered array of user IDs
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_id')->nullable()->constrained('approval_workflows')->nullOnDelete();
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference_type', 100)->nullable(); // App\Models\PurchaseOrder
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->string('title');
            $table->decimal('amount', 16, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->tinyInteger('current_step')->unsigned()->default(1);
            $table->json('data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status']);
        });

        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->tinyInteger('step')->unsigned()->default(1);
            $table->enum('action', ['approved', 'rejected', 'commented', 'forwarded']);
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 30); // created, updated, deleted, restored
            $table->string('auditable_type', 150);
            $table->unsignedBigInteger('auditable_id');
            $table->string('auditable_label')->nullable(); // human-readable identifier
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });

        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_successful')->default(true);
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'login_at']);
            $table->index(['user_id', 'login_at']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50); // created, updated, deleted, viewed, login, etc.
            $table->string('subject_type', 150)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['business_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('login_histories');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_workflows');
    }
};
