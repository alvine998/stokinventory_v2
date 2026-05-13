<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });

        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('tracking_url_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });

        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('so_no');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('price_level_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->enum('status', ['draft', 'confirmed', 'processing', 'partially_delivered', 'delivered', 'cancelled'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamp('ordered_at')->useCurrent();
            $table->timestamps();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->foreign('price_level_id')->references('id')->on('price_levels')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('so_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
            $table->foreign('so_id')->references('id')->on('sales_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });

        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('do_no');
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('expedition_id')->nullable();
            $table->string('tracking_no')->nullable();
            $table->text('shipping_address')->nullable();
            $table->enum('status', ['draft', 'shipped', 'in_transit', 'delivered', 'failed', 'returned'])->default('shipped');
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('so_id')->references('id')->on('sales_orders')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->foreign('expedition_id')->references('id')->on('expeditions')->nullOnDelete();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('do_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->decimal('quantity', 12, 2);
            $table->timestamps();
            $table->foreign('do_id')->references('id')->on('delivery_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });

        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('invoice_no');
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('status', ['unpaid', 'partial', 'paid', 'cancelled', 'overdue'])->default('unpaid');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('due_at')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('so_id')->references('id')->on('sales_orders')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });

        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('return_no');
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('do_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('returned_by');
            $table->string('reason')->nullable();
            $table->enum('status', ['draft', 'completed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamp('returned_at')->useCurrent();
            $table->timestamps();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('so_id')->references('id')->on('sales_orders')->nullOnDelete();
            $table->foreign('do_id')->references('id')->on('delivery_orders')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->foreign('returned_by')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->timestamps();
            $table->foreign('return_id')->references('id')->on('sales_returns')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });

        Schema::create('shipment_trackings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('do_id');
            $table->string('status');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('tracked_at')->useCurrent();
            $table->timestamps();
            $table->foreign('do_id')->references('id')->on('delivery_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_trackings');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('expeditions');
        Schema::dropIfExists('price_levels');
    }
};
