<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add inventory-ops columns to products
        Schema::table('products', function (Blueprint $table) {
            $table->integer('reorder_point')->default(0)->after('minimum_stock');
            $table->integer('safety_stock')->default(0)->after('reorder_point');
            $table->string('costing_method')->default('average')->after('safety_stock'); // fifo | fefo | average
        });

        // Stock adjustments (manual +/- with reason)
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['add', 'remove']);
            $table->integer('quantity');
            $table->string('reason')->nullable();
            $table->string('reference_no')->nullable();
            $table->timestamp('adjusted_at')->useCurrent();
            $table->timestamps();
        });

        // Warehouse transfers
        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('quantity');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('completed'); // pending | completed | cancelled
            $table->timestamp('transferred_at')->useCurrent();
            $table->timestamps();
        });

        // Serial number tracking
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('serial_no');
            $table->string('status')->default('in_stock'); // in_stock | sold | returned | damaged
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['business_id', 'serial_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
        Schema::dropIfExists('warehouse_transfers');
        Schema::dropIfExists('stock_adjustments');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['reorder_point', 'safety_stock', 'costing_method']);
        });
    }
};
